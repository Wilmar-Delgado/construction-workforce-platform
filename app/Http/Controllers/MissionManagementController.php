<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\Rating;
use App\Models\User;
use App\Models\WorkerRequest;
use App\Support\MissionBusinessDateResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MissionManagementController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly MissionBusinessDateResolver $businessDates)
    {
    }

    public function index()
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        $isAdministrator = $user->role?->name === 'administrator';
        $isCompanyManager = $companyId !== null
            && in_array($user->role?->name, ['company_owner', 'planning_manager'], true);
        $isSelfEmployed = $companyId === null && $user->role?->name === 'self_employed';

        $this->authorize('viewAny', WorkerRequest::class);

        // ========================
        // REQUESTS (PENDING TAB)
        // ========================

        // Requests Sent
        $requestsSent = null;

        if (! $isSelfEmployed) {
            $requestsSentQuery = WorkerRequest::with([
                    'mission' => fn ($query) => $query->withCommittedWorkerCount()->with('hiringCompany.owner'),
                    'worker.user',
                    'worker.company.owner',
                    'company',
                    'requester.role',
                ])
                ->where('type', 'invite')
                ->where('status', 'pending');

            if (! $isAdministrator) {
                $isCompanyManager
                    ? $requestsSentQuery->where('company_id', $companyId)
                    : $requestsSentQuery->where('requested_by', $user->id);
            }

            $requestsSent = $requestsSentQuery
                ->paginate(10, ['*'], 'pending_sent_page')
                ->withQueryString();
        }

        // Requests Received
        $requestsReceivedQuery = WorkerRequest::with([
                'mission' => fn ($query) => $query->withCommittedWorkerCount()->with('hiringCompany.owner'),
                'worker.user',
                'worker.company.owner',
                'company',
                'requester.role',
            ])
            ->where('status', 'pending');

        if (! $isAdministrator) {
            if ($isCompanyManager) {
                $requestsReceivedQuery->where(function ($q) use ($companyId) {
                    // Invitations for my company workers.
                    $q->where(function ($sub) use ($companyId) {
                        $sub->whereHas('worker', function ($q2) use ($companyId) {
                            $q2->where('company_id', $companyId);
                        })
                        ->where('type', 'invite');
                    })

                    // Applications to missions owned by my company.
                    ->orWhere(function ($sub) use ($companyId) {
                        $sub->whereHas('mission', function ($q2) use ($companyId) {
                            $q2->where('hiring_company_id', $companyId);
                        })
                        ->where('type', 'apply');
                    });
                });
            } elseif ($isSelfEmployed) {
                $requestsReceivedQuery
                    ->where('type', 'invite')
                    ->whereHas('worker', function ($q) use ($user) {
                        $q->whereNull('company_id')
                            ->where('user_id', $user->id);
                    });
            } else {
                $requestsReceivedQuery->whereRaw('1 = 0');
            }
        }

        $requestsReceived = $requestsReceivedQuery
            ->paginate(10, ['*'], 'pending_received_page')
            ->withQueryString();

        // Requests To Join
        $requestsToJoinQuery = WorkerRequest::with([
                'mission' => fn ($query) => $query->withCommittedWorkerCount()->with('hiringCompany.owner'),
                'worker.user',
                'worker.company.owner',
                'company',
                'requester.role',
            ])
            ->where('type', 'apply')
            ->where('status', 'pending');

        if (! $isAdministrator) {
            $isCompanyManager
                ? $requestsToJoinQuery->where('company_id', $companyId)
                : $requestsToJoinQuery->where('requested_by', $user->id);
        }

        $requestsToJoin = $requestsToJoinQuery
            ->paginate(10, ['*'], 'pending_join_page')
            ->withQueryString();

        // ========================
        // ONGOING
        // ========================

        // Missions created by my company
        $ongoingCreated = null;

        if (! $isSelfEmployed) {
            $ongoingCreatedQuery = WorkerRequest::with([
                    'mission' => fn ($query) => $query->withCommittedWorkerCount()->with('hiringCompany.owner'),
                    'worker.user',
                    'worker.company.owner',
                    'company',
                    'requester.role',
                ])
                ->whereIn('status', ['accepted', 'ongoing'])
                ->whereHas('mission', fn ($query) => $query->whereIn('status', ['open', 'in_progress']));

            if (! $isAdministrator) {
                if ($isCompanyManager) {
                    $ongoingCreatedQuery->whereHas('mission', function ($q) use ($companyId) {
                        $q->where('hiring_company_id', $companyId);
                    });
                } else {
                    $ongoingCreatedQuery->whereRaw('1 = 0');
                }
            }

            $ongoingCreated = $ongoingCreatedQuery
                ->paginate(10, ['*'], 'ongoing_created_page')
                ->withQueryString();
        }

        // Missions my workers joined externally
        $ongoingJoinedQuery = WorkerRequest::with([
                'mission' => fn ($query) => $query->withCommittedWorkerCount()->with('hiringCompany.owner'),
                'worker.user',
                'worker.company.owner',
                'company',
                'requester.role',
            ])
            ->whereIn('status', ['accepted', 'ongoing'])
            ->whereHas('mission', fn ($query) => $query->whereIn('status', ['open', 'in_progress']));

        if (! $isAdministrator) {
            $ongoingJoinedQuery->whereHas('worker', function ($q) use ($companyId, $user, $isCompanyManager) {
                if ($isCompanyManager) {
                    $q->where('company_id', $companyId);
                } else {
                    $q->whereNull('company_id')
                        ->where('user_id', $user->id);
                }
            });

            if ($isCompanyManager) {
                $ongoingJoinedQuery->whereHas('mission', function ($q) use ($companyId) {
                    $q->where('hiring_company_id', '!=', $companyId);
                });
            }
        }

        $ongoingJoined = $ongoingJoinedQuery
            ->paginate(10, ['*'], 'ongoing_joined_page')
            ->withQueryString();

        // ========================
        // COMPLETED
        // ========================

        $completedCreated = null;

        if (! $isSelfEmployed) {
            $completedCreatedQuery = WorkerRequest::with([
                    'mission' => fn ($query) => $query->withCommittedWorkerCount()->with([
                        'hiringCompany.owner',
                        'ratings.reviewer.role',
                    ]),
                    'worker.user',
                    'worker.company.owner',
                    'company',
                    'requester.role',
                ])
                ->whereIn('status', ['completed', 'ended_early']);

            if (! $isAdministrator) {
                if ($isCompanyManager) {
                    $completedCreatedQuery->whereHas('mission', function ($q) use ($companyId) {
                        $q->where('hiring_company_id', $companyId);
                    });
                } else {
                    $completedCreatedQuery->whereRaw('1 = 0');
                }
            }

            $completedCreated = $completedCreatedQuery
                ->paginate(10, ['*'], 'completed_created_page')
                ->withQueryString();
        }

        $completedJoinedQuery = WorkerRequest::with([
                'mission' => fn ($query) => $query->withCommittedWorkerCount()->with([
                    'hiringCompany.owner',
                    'ratings.reviewer.role',
                ]),
                'worker.user',
                'worker.company.owner',
                'company',
                'requester.role',
            ])
            ->whereIn('status', ['completed', 'ended_early']);

        if (! $isAdministrator) {
            $completedJoinedQuery->whereHas('worker', function ($q) use ($companyId, $user, $isCompanyManager) {
                if ($isCompanyManager) {
                    $q->where('company_id', $companyId);
                } else {
                    $q->whereNull('company_id')
                        ->where('user_id', $user->id);
                }
            });

            if ($isCompanyManager) {
                $completedJoinedQuery->whereHas('mission', function ($q) use ($companyId) {
                    $q->where('hiring_company_id', '!=', $companyId);
                });
            }
        }

        $completedJoined = $completedJoinedQuery
            ->paginate(10, ['*'], 'completed_joined_page')
            ->withQueryString();

        if ($requestsSent !== null) {
            $this->prepareRequestsForDisplay($requestsSent, $companyId);
        }
        $this->prepareRequestsForDisplay($requestsReceived, $companyId);
        $this->prepareRequestsForDisplay($requestsToJoin, $companyId);
        if ($ongoingCreated !== null) {
            $this->prepareRequestsForDisplay($ongoingCreated, $companyId);
        }
        $this->prepareRequestsForDisplay($ongoingJoined, $companyId);
        if ($completedCreated !== null) {
            $this->prepareRequestsForDisplay($completedCreated, $companyId, true);
        }
        $this->prepareRequestsForDisplay($completedJoined, $companyId, true);

        return Inertia::render('MissionManagement', [
            'data' => [
                'pending' => [
                    'sent' => $requestsSent,
                    'received' => $requestsReceived,
                    'join' => $requestsToJoin,
                ],

                'ongoing' => [
                    'created' => $ongoingCreated,
                    'joined' => $ongoingJoined,
                ],

                'completed' => [
                    'created' => $completedCreated,
                    'joined' => $completedJoined,
                ],
            ],
            // Retain the request-root payload until the Vue redesign consumes this
            // mission-root contract in the next phase.
            'missionData' => $this->missionCentricData($user),
        ]);
    }

    /**
     * Mission-root payload for the mission-centric management redesign.
     *
     * Every paginator is rooted in missions, so a mission and its relevant
     * worker requests are always delivered together.
     */
    private function missionCentricData(User $user): array
    {
        $requests = $this->paginateMissionTab(
            $this->pendingRequestMissionsQuery($user),
            $user,
            ['pending', 'rejected', 'cancelled'],
            'mission_requests_page',
        );

        $staffing = $this->paginateMissionTab(
            $this->staffingMissionsQuery($user),
            $user,
            ['accepted'],
            'mission_staffing_page',
        );

        $inProgress = $this->paginateMissionTab(
            $this->inProgressMissionsQuery($user),
            $user,
            Mission::COMMITTED_REQUEST_STATUSES,
            'mission_in_progress_page',
            includeRatings: true,
        );

        $completed = $this->paginateMissionTab(
            $this->completedMissionsQuery($user),
            $user,
            ['completed', 'ended_early'],
            'mission_completed_page',
            includeRatings: true,
        );

        return [
            'tabs' => [
                'requests' => $requests,
                'staffing' => $staffing,
                'in_progress' => $inProgress,
                'completed' => $completed,
            ],
            'counts' => [
                'requests' => $requests->total(),
                'staffing' => $staffing->total(),
                'in_progress' => $inProgress->total(),
                'completed' => $completed->total(),
            ],
        ];
    }

    private function pendingRequestMissionsQuery(User $user): Builder
    {
        return Mission::query()
            ->whereHas('requests', function (Builder $query) use ($user) {
                $query->whereIn('status', ['pending', 'rejected', 'cancelled']);
                $this->scopeRequestsVisibleTo($query, $user);
            });
    }

    private function staffingMissionsQuery(User $user): Builder
    {
        $isAdministrator = $this->isAdministrator($user);
        $isCompanyManager = $this->isCompanyManager($user);
        $companyId = $user->company_id;

        return Mission::query()
            ->notArchived()
            ->where('status', 'open')
            ->where(function (Builder $query) use ($user, $isAdministrator, $isCompanyManager, $companyId) {
                if ($isAdministrator) {
                    $query->whereNotNull('recruiting_closed_at')
                        ->orWhereHas('requests', fn (Builder $requests) => $requests->where('status', 'accepted'));

                    return;
                }

                if ($isCompanyManager) {
                    $query->where(function (Builder $ownMissions) use ($companyId) {
                        $ownMissions->where('hiring_company_id', $companyId)
                            ->where(function (Builder $staffingState) {
                                $staffingState->whereNotNull('recruiting_closed_at')
                                    ->orWhereHas('requests', fn (Builder $requests) => $requests->where('status', 'accepted'));
                            });
                    })->orWhere(function (Builder $externalMissions) use ($user, $companyId) {
                        $externalMissions->where('hiring_company_id', '!=', $companyId)
                            ->whereHas('requests', function (Builder $requests) use ($user) {
                                $requests->where('status', 'accepted');
                                $this->scopeExternalRequestsVisibleTo($requests, $user);
                            });
                    });

                    return;
                }

                $query->whereHas('requests', function (Builder $requests) use ($user) {
                    $requests->where('status', 'accepted');
                    $this->scopeExternalRequestsVisibleTo($requests, $user);
                });
            });
    }

    private function inProgressMissionsQuery(User $user): Builder
    {
        return Mission::query()
            ->notArchived()
            ->where('status', 'in_progress')
            ->whereHas('requests', function (Builder $query) use ($user) {
                $query->whereIn('status', Mission::COMMITTED_REQUEST_STATUSES);
                $this->scopeRequestsVisibleTo($query, $user);
            });
    }

    private function completedMissionsQuery(User $user): Builder
    {
        return Mission::query()
            ->where('status', 'completed')
            ->whereHas('requests', function (Builder $query) use ($user) {
                $query->whereIn('status', ['completed', 'ended_early']);
                $this->scopeRequestsVisibleTo($query, $user);
            });
    }

    private function paginateMissionTab(Builder $query, User $user, array $requestStatuses, string $pageName, bool $includeRatings = false): LengthAwarePaginator
    {
        $missions = $query
            ->withCommittedWorkerCount()
            ->with([
                'hiringCompany.owner',
                'requests' => function (Builder|Relation $requests) use ($user, $requestStatuses) {
                    $requests->whereIn('status', $requestStatuses)
                        ->with([
                            'worker.user',
                            'worker.company.owner',
                            'company',
                            'requester.role',
                        ])
                        ->latest('created_at');

                    $this->scopeRequestsVisibleTo($requests, $user);
                },
            ])
            ->latest('updated_at')
            ->paginate(10, ['*'], $pageName)
            ->withQueryString();

        $this->prepareMissionsForDisplay($missions, $user, $includeRatings);

        return $missions;
    }

    /**
     * Limits nested request rows to the viewer's own mission, company workers,
     * or independent worker. The root mission query uses the same scope.
     */
    private function scopeRequestsVisibleTo(Builder|Relation $query, User $user): void
    {
        if ($this->isAdministrator($user)) {
            return;
        }

        if ($this->isCompanyManager($user)) {
            $companyId = $user->company_id;

            $query->where(function (Builder $visibleRequests) use ($companyId) {
                $visibleRequests->whereHas('mission', fn (Builder $mission) => $mission->where('hiring_company_id', $companyId))
                    ->orWhere('company_id', $companyId)
                    ->orWhereHas('worker', fn (Builder $worker) => $worker->where('company_id', $companyId));
            });

            return;
        }

        if ($this->isSelfEmployed($user)) {
            $query->whereHas('worker', function (Builder $worker) use ($user) {
                $worker->whereNull('company_id')
                    ->where('user_id', $user->id);
            });

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function scopeExternalRequestsVisibleTo(Builder|Relation $query, User $user): void
    {
        if ($this->isAdministrator($user)) {
            return;
        }

        if ($this->isCompanyManager($user)) {
            $companyId = $user->company_id;

            $query->where(function (Builder $visibleRequests) use ($companyId) {
                $visibleRequests->where('company_id', $companyId)
                    ->orWhereHas('worker', fn (Builder $worker) => $worker->where('company_id', $companyId));
            });

            return;
        }

        if ($this->isSelfEmployed($user)) {
            $query->whereHas('worker', function (Builder $worker) use ($user) {
                $worker->whereNull('company_id')
                    ->where('user_id', $user->id);
            });

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function prepareMissionsForDisplay(LengthAwarePaginator $missions, User $user, bool $includeRatings): void
    {
        if ($includeRatings) {
            $this->attachRatingsToRelevantRequests($missions);
        }

        $missions->getCollection()->each(function (Mission $mission) use ($user) {
            $isOwnMission = $mission->hiring_company_id === $user->company_id && $user->company_id !== null;

            $mission->management_context = [
                'is_own_mission' => $isOwnMission,
                'relationship' => $isOwnMission ? 'own_mission' : 'external_assignment',
                'recruiting_state' => $mission->recruiting_closed_at === null ? 'recruiting' : 'staffing_closed',
                'can_stop_recruiting' => $this->canStopRecruiting($user, $mission),
                'can_start_mission' => $this->canStartMission($user, $mission),
                'can_view_mission' => $mission->archived_at === null,
            ];

            $requests = $mission->requests;

            $requests->each(function (WorkerRequest $request) use ($mission, $user) {
                $canRespond = $this->canRespondToRequest($user, $mission, $request);

                $request->management_context = [
                    'direction' => $this->requestDirection($user, $mission, $request),
                    'can_respond' => $canRespond,
                    'waiting_for_response' => $request->status === 'pending' && ! $canRespond,
                    'can_complete' => $this->canCompleteAssignment($user, $mission, $request),
                    'can_end_early' => $this->canEndAssignmentEarly($user, $mission, $request),
                ];
            });

            // Keep the legacy relationship name out of the new contract so its
            // purpose is explicit to the Phase 2 Vue implementation.
            $mission->unsetRelation('requests');
            $mission->setRelation('management_requests', $requests);
        });
    }

    private function attachRatingsToRelevantRequests(LengthAwarePaginator $missions): void
    {
        $requests = $missions->getCollection()
            ->flatMap(fn (Mission $mission) => $mission->requests);

        if ($requests->isEmpty()) {
            return;
        }

        $ratings = Rating::query()
            ->with('reviewer.role')
            ->whereIn('mission_id', $requests->pluck('mission_id')->unique())
            ->whereIn('worker_profile_id', $requests->pluck('worker_profile_id')->filter()->unique())
            ->get()
            ->keyBy(fn (Rating $rating) => $rating->mission_id.':'.$rating->worker_profile_id);

        $requests->each(function (WorkerRequest $request) use ($ratings) {
            $request->rating = $ratings->get($request->mission_id.':'.$request->worker_profile_id);
        });
    }

    private function requestDirection(User $user, Mission $mission, WorkerRequest $request): string
    {
        if ($this->isAdministrator($user)) {
            return 'administrative';
        }

        return $this->isRequestRecipient($user, $mission, $request) ? 'incoming' : 'outgoing';
    }

    private function canRespondToRequest(User $user, Mission $mission, WorkerRequest $request): bool
    {
        return $request->status === 'pending'
            && $this->isRequestRecipient($user, $mission, $request);
    }

    private function isRequestRecipient(User $user, Mission $mission, WorkerRequest $request): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        if ($request->type === 'apply') {
            return $this->isCompanyManager($user)
                && $mission->hiring_company_id === $user->company_id;
        }

        if ($request->type !== 'invite' || $request->worker === null) {
            return false;
        }

        if ($this->isCompanyManager($user)) {
            return $request->worker->company_id === $user->company_id;
        }

        return $this->isSelfEmployed($user)
            && $request->worker->company_id === null
            && $request->worker->user_id === $user->id;
    }

    private function canStopRecruiting(User $user, Mission $mission): bool
    {
        return $this->canManageMission($user, $mission)
            && $mission->status === 'open'
            && $mission->recruiting_closed_at === null
            && $mission->committed_worker_count > 0
            && $mission->remaining_capacity > 0;
    }

    private function canStartMission(User $user, Mission $mission): bool
    {
        return $this->canManageMission($user, $mission)
            && $mission->status === 'open'
            && $mission->recruiting_closed_at !== null
            && $mission->committed_worker_count > 0
            && $mission->start_date !== null
            && $this->businessDates->hasReachedStartDate($mission);
    }

    private function canCompleteAssignment(User $user, Mission $mission, WorkerRequest $request): bool
    {
        return $this->canManageMission($user, $mission)
            && $mission->status === 'in_progress'
            && $request->status === 'ongoing'
            && $mission->end_date !== null
            && $this->businessDates->hasReachedEndDate($mission);
    }

    private function canEndAssignmentEarly(User $user, Mission $mission, WorkerRequest $request): bool
    {
        return $this->canManageMission($user, $mission)
            && $mission->status === 'in_progress'
            && in_array($request->status, ['accepted', 'ongoing'], true);
    }

    private function canManageMission(User $user, Mission $mission): bool
    {
        return $this->isAdministrator($user)
            || ($this->isCompanyManager($user) && $mission->hiring_company_id === $user->company_id);
    }

    private function isAdministrator(User $user): bool
    {
        return $user->role?->name === 'administrator';
    }

    private function isCompanyManager(User $user): bool
    {
        return $user->company_id !== null
            && in_array($user->role?->name, ['company_owner', 'planning_manager'], true);
    }

    private function isSelfEmployed(User $user): bool
    {
        return $user->company_id === null && $user->role?->name === 'self_employed';
    }

    private function prepareRequestsForDisplay($requests, ?int $companyId, bool $includeRating = false): void
    {
        $requests->getCollection()->transform(function (WorkerRequest $request) use ($companyId, $includeRating) {
            $request->mission_context = [
                'is_own_mission' => $companyId !== null
                    && $request->mission->hiring_company_id === $companyId,
            ];

            if ($includeRating) {
                $request->rating = $request->mission->ratings
                    ->firstWhere('worker_profile_id', $request->worker_profile_id);
            }

            return $request;
        });
    }

    public function respond(Request $request, WorkerRequest $workerRequest)
    {
        $validated = $request->validate([
            'action' => 'required|in:accept,reject',
            'message' => 'nullable|string|max:1000',
            'reason' => 'nullable|string|max:1000',
        ]);

        $this->authorize('respond', [$workerRequest, $validated['action']]);

        if ($validated['action'] === 'accept') {
            DB::transaction(function () use ($workerRequest): void {
                $mission = Mission::query()
                    ->lockForUpdate()
                    ->findOrFail($workerRequest->mission_id);

                $lockedRequest = WorkerRequest::query()
                    ->lockForUpdate()
                    ->findOrFail($workerRequest->id);

                if ($lockedRequest->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'action' => __('app.mission_management_page.validation.request_no_longer_pending'),
                    ]);
                }

                if ($mission->recruiting_closed_at !== null) {
                    throw ValidationException::withMessages([
                        'action' => __('app.mission_management_page.validation.recruiting_closed'),
                    ]);
                }

                if (! $mission->isActionableForStaffing() || $mission->remaining_capacity <= 0) {
                    throw ValidationException::withMessages([
                        'action' => __('app.mission_management_page.validation.mission_not_eligible_for_staffing'),
                    ]);
                }

                $workerAlreadyCommitted = WorkerRequest::query()
                    ->where('mission_id', $mission->id)
                    ->where('worker_profile_id', $lockedRequest->worker_profile_id)
                    ->whereKeyNot($lockedRequest->id)
                    ->whereIn('status', Mission::COMMITTED_REQUEST_STATUSES)
                    ->exists();

                if ($workerAlreadyCommitted) {
                    throw ValidationException::withMessages([
                        'action' => __('app.mission_management_page.validation.worker_already_committed'),
                    ]);
                }

                $lockedRequest->update([
                    'status' => 'accepted',
                    'responded_by' => auth()->id(),
                    'responded_at' => now(),
                ]);

                if ($mission->committedRequests()->count() >= $mission->workers) {
                    $mission->closeRecruitingAndCancelPending();
                }
            });
            // Send acceptance email
        } elseif ($validated['action'] === 'reject') {
            $workerRequest->update([
                'status' => 'rejected',
                'rejection_message' => $validated['message'],
                'responded_by' => auth()->id(),
                'responded_at' => now(),
            ]);
            // Send rejection email
        }
            
        return back()->with('success', 'Request updated successfully.');
    }

    public function closeRecruiting(Mission $mission)
    {
        $this->authorize('closeRecruiting', $mission);

        DB::transaction(function () use ($mission): void {
            $lockedMission = Mission::query()
                ->lockForUpdate()
                ->findOrFail($mission->id);

            if ($lockedMission->status !== 'open' || $lockedMission->recruiting_closed_at !== null) {
                throw ValidationException::withMessages([
                    'mission' => __('app.mission_management_page.validation.recruiting_already_closed'),
                ]);
            }

            if ($lockedMission->committedRequests()->count() < 1) {
                throw ValidationException::withMessages([
                    'mission' => __('app.mission_management_page.validation.recruiting_requires_committed_worker'),
                ]);
            }

            $lockedMission->closeRecruitingAndCancelPending();
        });

        return back()->with('success', __('app.mission_management_page.success.recruiting_closed'));
    }

    public function start(Mission $mission)
    {
        $this->authorize('start', $mission);

        DB::transaction(function () use ($mission): void {
            $lockedMission = Mission::query()
                ->lockForUpdate()
                ->findOrFail($mission->id);

            if ($lockedMission->status !== 'open' || $lockedMission->recruiting_closed_at === null) {
                throw ValidationException::withMessages([
                    'mission' => __('app.mission_management_page.validation.mission_not_ready_to_start'),
                ]);
            }

            if (! $this->businessDates->hasReachedStartDate($lockedMission)) {
                throw ValidationException::withMessages([
                    'mission' => __('app.mission_management_page.validation.mission_start_date_not_reached'),
                ]);
            }

            if ($lockedMission->committedRequests()->count() < 1) {
                throw ValidationException::withMessages([
                    'mission' => __('app.mission_management_page.validation.mission_start_requires_committed_worker'),
                ]);
            }

            $lockedMission->closeRecruitingAndCancelPending();
            $lockedMission->startExecution();
        });

        return back()->with('success', __('app.mission_management_page.success.mission_started'));
    }

    public function complete(Request $request, WorkerRequest $workerRequest)
    {
        $this->authorize('complete', $workerRequest);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($workerRequest, $validated): void {
            $mission = Mission::query()
                ->lockForUpdate()
                ->findOrFail($workerRequest->mission_id);

            $lockedRequest = WorkerRequest::query()
                ->lockForUpdate()
                ->findOrFail($workerRequest->id);

            if ($mission->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'assignment' => __('app.mission_management_page.validation.mission_not_in_progress'),
                ]);
            }

            if ($lockedRequest->status !== 'ongoing') {
                throw ValidationException::withMessages([
                    'assignment' => __('app.mission_management_page.validation.assignment_not_ready_to_complete'),
                ]);
            }

            if (! $this->businessDates->hasReachedEndDate($mission)) {
                throw ValidationException::withMessages([
                    'assignment' => __('app.mission_management_page.validation.mission_end_date_not_reached'),
                ]);
            }

            if (Rating::query()
                ->where('mission_id', $mission->id)
                ->where('worker_profile_id', $lockedRequest->worker_profile_id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'rating' => __('app.mission_management_page.validation.rating_already_exists'),
                ]);
            }

            Rating::create([
                'mission_id' => $mission->id,
                'reviewed_by_user_id' => auth()->id(),
                'worker_profile_id' => $lockedRequest->worker_profile_id,
                'score' => $validated['rating'],
                'feedback' => $validated['comment'] ?? null,
            ]);

            $lockedRequest->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $mission->completeIfReady();
        });

        return back()->with('success', __('app.mission_management_page.success.assignment_completed'));
    }

    public function endEarly(Request $request, WorkerRequest $workerRequest)
    {
        $this->authorize('endEarly', $workerRequest);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($workerRequest, $validated): void {
            $mission = Mission::query()
                ->lockForUpdate()
                ->findOrFail($workerRequest->mission_id);

            $lockedRequest = WorkerRequest::query()
                ->lockForUpdate()
                ->findOrFail($workerRequest->id);

            if ($mission->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'assignment' => __('app.mission_management_page.validation.mission_not_in_progress'),
                ]);
            }

            if (! in_array($lockedRequest->status, ['accepted', 'ongoing'], true)) {
                throw ValidationException::withMessages([
                    'assignment' => __('app.mission_management_page.validation.assignment_not_ready_to_end_early'),
                ]);
            }

            if (Rating::query()
                ->where('mission_id', $mission->id)
                ->where('worker_profile_id', $lockedRequest->worker_profile_id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'rating' => __('app.mission_management_page.validation.rating_already_exists'),
                ]);
            }

            Rating::create([
                'mission_id' => $mission->id,
                'reviewed_by_user_id' => auth()->id(),
                'worker_profile_id' => $lockedRequest->worker_profile_id,
                'score' => $validated['rating'],
                'feedback' => $validated['comment'] ?? null,
            ]);

            $lockedRequest->update([
                'status' => 'ended_early',
                'ended_at' => now(),
            ]);

            // An early-ended assignment is resolved, but the mission cannot finish
            // before its own end date or while other active assignments remain.
            $mission->completeIfReady();
        });

        return back()->with('success', __('app.mission_management_page.success.assignment_ended_early'));
    }
}
