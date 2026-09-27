<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Rating;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use App\Support\ProjectBusinessDateResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProjectManagementController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ProjectBusinessDateResolver $businessDates)
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
                    'project' => fn ($query) => $query->withCommittedWorkerCount()->with('hiringCompany.owner'),
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
                'project' => fn ($query) => $query->withCommittedWorkerCount()->with('hiringCompany.owner'),
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

                    // Applications to projects owned by my company.
                    ->orWhere(function ($sub) use ($companyId) {
                        $sub->whereHas('project', function ($q2) use ($companyId) {
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
                'project' => fn ($query) => $query->withCommittedWorkerCount()->with('hiringCompany.owner'),
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

        // Projects created by my company
        $ongoingCreated = null;

        if (! $isSelfEmployed) {
            $ongoingCreatedQuery = WorkerRequest::with([
                    'project' => fn ($query) => $query->withCommittedWorkerCount()->with('hiringCompany.owner'),
                    'worker.user',
                    'worker.company.owner',
                    'company',
                    'requester.role',
                ])
                ->whereIn('status', ['accepted', 'ongoing'])
                ->whereHas('project', fn ($query) => $query->whereIn('status', ['open', 'in_progress']));

            if (! $isAdministrator) {
                if ($isCompanyManager) {
                    $ongoingCreatedQuery->whereHas('project', function ($q) use ($companyId) {
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

        // Projects my workers joined externally
        $ongoingJoinedQuery = WorkerRequest::with([
                'project' => fn ($query) => $query->withCommittedWorkerCount()->with('hiringCompany.owner'),
                'worker.user',
                'worker.company.owner',
                'company',
                'requester.role',
            ])
            ->whereIn('status', ['accepted', 'ongoing'])
            ->whereHas('project', fn ($query) => $query->whereIn('status', ['open', 'in_progress']));

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
                $ongoingJoinedQuery->whereHas('project', function ($q) use ($companyId) {
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
                    'project' => fn ($query) => $query->withCommittedWorkerCount()->with([
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
                    $completedCreatedQuery->whereHas('project', function ($q) use ($companyId) {
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
                'project' => fn ($query) => $query->withCommittedWorkerCount()->with([
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
                $completedJoinedQuery->whereHas('project', function ($q) use ($companyId) {
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

        return Inertia::render('ProjectManagement', [
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
            // project-root contract in the next phase.
            'projectData' => $this->projectCentricData($user),
        ]);
    }

    /**
     * Returns the read-only details for a project already visible to the
     * current Project Management user. External projects require the visible
     * request that establishes the viewer's relationship to the project.
     */
    public function details(Request $request, Project $project): JsonResponse
    {
        abort_if(
            $project->archived_at !== null || $project->status === 'completed',
            404,
        );

        $user = $request->user();
        $isOwnProject = $user->company_id !== null
            && $project->hiring_company_id === $user->company_id;
        $workerRequest = null;

        if ($isOwnProject) {
            $this->authorize('view', $project);
        } else {
            $request->validate([
                'request' => ['required', 'integer'],
            ]);

            $workerRequest = WorkerRequest::findOrFail($request->integer('request'));

            abort_unless(
                $workerRequest->project_id === $project->id
                    && in_array(
                        $workerRequest->status,
                        [
                            'pending',
                            'accepted',
                            'ongoing',
                            'completed',
                            'rejected',
                            'cancelled',
                            'ended_early',
                        ],
                        true,
                    ),
                404,
            );

            $this->authorize('view', $workerRequest);
        }

        $project->load([
            'hiringCompany:id,name,owner_id',
            'hiringCompany.owner:id,name,phone',
            'requirements:id,project_id,name',
        ])->loadCount([
            'requests as committed_worker_count' => fn (Builder $requests) => $requests
                ->whereIn('status', Project::COMMITTED_REQUEST_STATUSES),
        ]);

        return response()->json([
            'project' => $this->projectDetailsPayload(
                $project,
                $workerRequest,
                $isOwnProject,
            ),
        ]);
    }

    /**
     * Returns a worker profile only through an authorized Project Management
     * request relationship. This deliberately does not apply notArchived(),
     * because archived profiles remain available in authorized history.
     */
    public function workerDetails(
        Request $request,
        WorkerProfile $workerProfile,
    ): JsonResponse {
        $request->validate([
            'request' => ['required', 'integer'],
        ]);

        $workerRequest = WorkerRequest::findOrFail(
            $request->integer('request'),
        );

        abort_unless(
            $workerRequest->worker_profile_id === $workerProfile->id
                && in_array(
                    $workerRequest->status,
                    [
                        'pending',
                        'accepted',
                        'ongoing',
                        'completed',
                        'rejected',
                        'cancelled',
                        'ended_early',
                    ],
                    true,
                ),
            404,
        );

        $this->authorize('view', $workerRequest);

        $workerProfile
            ->load([
                'skills:id,name',
                'certifications:id,name',
                'company:id,name',
            ])
            ->loadAvg('ratings', 'score')
            ->loadCount('ratings');

        return response()->json([
            'worker' => $this->workerDetailsPayload($workerProfile),
        ]);
    }

    private function projectDetailsPayload(
        Project $project,
        ?WorkerRequest $workerRequest,
        bool $isOwnProject,
    ): array {
        $payload = [
            'id' => $project->id,
            'title' => $project->title,
            'description' => $project->description,
            'city' => $project->city,
            'province' => $project->province,
            'country' => $project->country,
            'job_type' => $project->job_type,
            'workers' => $project->workers,
            'start_date' => $project->start_date,
            'end_date' => $project->end_date,
            'hourly_rate' => $project->hourly_rate,
            'committed_worker_count' => $project->committed_worker_count,
            'remaining_capacity' => $project->remaining_capacity,
            'hiring_company' => [
                'name' => $project->hiringCompany?->name,
            ],
            'requirements' => $project->requirements
                ->map(fn ($requirement) => [
                    'id' => $requirement->id,
                    'name' => $requirement->name,
                ])
                ->values(),
        ];

        $canViewOperationalDetails = $isOwnProject
            || in_array(
                $workerRequest?->status,
                ['accepted', 'ongoing', 'completed'],
                true,
            );

        if ($canViewOperationalDetails) {
            $payload['operational_details'] = [
                'site_name' => $project->site_name,
                'address_line_1' => $project->address_line_1,
                'address_line_2' => $project->address_line_2,
                'postal_code' => $project->postal_code,
                'directions' => $project->directions,
                'contact_name' => $project->hiringCompany?->owner?->name,
                'contact_phone' => $project->hiringCompany?->owner?->phone,
            ];
        }

        return $payload;
    }

    private function workerDetailsPayload(WorkerProfile $workerProfile): array
    {
        return [
            'id' => $workerProfile->id,
            'name' => $workerProfile->name,
            'job' => $workerProfile->job,
            'years_experience' => $workerProfile->years_experience,
            'hourly_rate' => $workerProfile->hourly_rate,
            'rating' => $workerProfile->rating,
            'ratings_count' => $workerProfile->ratings_count,
            'company' => $workerProfile->company ? [
                'id' => $workerProfile->company->id,
                'name' => $workerProfile->company->name,
            ] : null,
            'skills' => $workerProfile->skills
                ->map(fn ($skill) => [
                    'id' => $skill->id,
                    'name' => $skill->name,
                ])
                ->values(),
            'certifications' => $workerProfile->certifications
                ->map(fn ($certification) => [
                    'id' => $certification->id,
                    'name' => $certification->name,
                ])
                ->values(),
        ];
    }

    /**
     * Project-root payload for the project-centric management redesign.
     *
     * Every paginator is rooted in projects, so a project and its relevant
     * worker requests are always delivered together.
     */
    private function projectCentricData(User $user): array
    {
        $requests = $this->paginateProjectTab(
            $this->pendingRequestProjectsQuery($user),
            $user,
            ['pending', 'rejected', 'cancelled'],
            'project_requests_page',
        );

        $staffing = $this->paginateProjectTab(
            $this->staffingProjectsQuery($user),
            $user,
            ['accepted'],
            'project_staffing_page',
        );

        $inProgress = $this->paginateProjectTab(
            $this->inProgressProjectsQuery($user),
            $user,
            Project::COMMITTED_REQUEST_STATUSES,
            'project_in_progress_page',
            includeRatings: true,
        );

        $completed = $this->paginateProjectTab(
            $this->completedProjectsQuery($user),
            $user,
            ['completed', 'ended_early'],
            'project_completed_page',
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

    private function pendingRequestProjectsQuery(User $user): Builder
    {
        return Project::query()
            ->whereHas('requests', function (Builder $query) use ($user) {
                $query->whereIn('status', ['pending', 'rejected', 'cancelled']);
                $this->scopeRequestsVisibleTo($query, $user);
            });
    }

    private function staffingProjectsQuery(User $user): Builder
    {
        $isAdministrator = $this->isAdministrator($user);
        $isCompanyManager = $this->isCompanyManager($user);
        $companyId = $user->company_id;

        return Project::query()
            ->notArchived()
            ->where('status', 'open')
            ->where(function (Builder $query) use ($user, $isAdministrator, $isCompanyManager, $companyId) {
                if ($isAdministrator) {
                    $query->whereNotNull('recruiting_closed_at')
                        ->orWhereHas('requests', fn (Builder $requests) => $requests->where('status', 'accepted'));

                    return;
                }

                if ($isCompanyManager) {
                    $query->where(function (Builder $ownProjects) use ($companyId) {
                        $ownProjects->where('hiring_company_id', $companyId)
                            ->where(function (Builder $staffingState) {
                                $staffingState->whereNotNull('recruiting_closed_at')
                                    ->orWhereHas('requests', fn (Builder $requests) => $requests->where('status', 'accepted'));
                            });
                    })->orWhere(function (Builder $externalProjects) use ($user, $companyId) {
                        $externalProjects->where('hiring_company_id', '!=', $companyId)
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

    private function inProgressProjectsQuery(User $user): Builder
    {
        return Project::query()
            ->notArchived()
            ->where('status', 'in_progress')
            ->whereHas('requests', function (Builder $query) use ($user) {
                $query->whereIn('status', Project::COMMITTED_REQUEST_STATUSES);
                $this->scopeRequestsVisibleTo($query, $user);
            });
    }

    private function completedProjectsQuery(User $user): Builder
    {
        return Project::query()
            ->where('status', 'completed')
            ->whereHas('requests', function (Builder $query) use ($user) {
                $query->whereIn('status', ['completed', 'ended_early']);
                $this->scopeRequestsVisibleTo($query, $user);
            });
    }

    private function paginateProjectTab(Builder $query, User $user, array $requestStatuses, string $pageName, bool $includeRatings = false): LengthAwarePaginator
    {
        $projects = $query
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

        $this->prepareProjectsForDisplay($projects, $user, $includeRatings);

        return $projects;
    }

    /**
     * Limits nested request rows to the viewer's own project, company workers,
     * or independent worker. The root project query uses the same scope.
     */
    private function scopeRequestsVisibleTo(Builder|Relation $query, User $user): void
    {
        if ($this->isAdministrator($user)) {
            return;
        }

        if ($this->isCompanyManager($user)) {
            $companyId = $user->company_id;

            $query->where(function (Builder $visibleRequests) use ($companyId) {
                $visibleRequests->whereHas('project', fn (Builder $project) => $project->where('hiring_company_id', $companyId))
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

    private function prepareProjectsForDisplay(LengthAwarePaginator $projects, User $user, bool $includeRatings): void
    {
        if ($includeRatings) {
            $this->attachRatingsToRelevantRequests($projects);
        }

        $projects->getCollection()->each(function (Project $project) use ($user) {
            $isOwnProject = $project->hiring_company_id === $user->company_id && $user->company_id !== null;

            $project->management_context = [
                'is_own_project' => $isOwnProject,
                'relationship' => $isOwnProject ? 'own_project' : 'external_assignment',
                'recruiting_state' => $project->recruiting_closed_at === null ? 'recruiting' : 'staffing_closed',
                'can_stop_recruiting' => $this->canStopRecruiting($user, $project),
                'can_start_project' => $this->canStartProject($user, $project),
                'can_view_project' => $project->archived_at === null
                    && $project->status !== 'completed',
            ];

            $requests = $project->requests;

            $requests->each(function (WorkerRequest $request) use ($project, $user) {
                $canRespond = $this->canRespondToRequest($user, $project, $request);

                $request->management_context = [
                    'direction' => $this->requestDirection($user, $project, $request),
                    'can_respond' => $canRespond,
                    'waiting_for_response' => $request->status === 'pending' && ! $canRespond,
                    'can_complete' => $this->canCompleteAssignment($user, $project, $request),
                    'can_end_early' => $this->canEndAssignmentEarly($user, $project, $request),
                ];
            });

            // Keep the legacy relationship name out of the new contract so its
            // purpose is explicit to the Phase 2 Vue implementation.
            $project->unsetRelation('requests');
            $project->setRelation('management_requests', $requests);
        });
    }

    private function attachRatingsToRelevantRequests(LengthAwarePaginator $projects): void
    {
        $requests = $projects->getCollection()
            ->flatMap(fn (Project $project) => $project->requests);

        if ($requests->isEmpty()) {
            return;
        }

        $ratings = Rating::query()
            ->with('reviewer.role')
            ->whereIn('project_id', $requests->pluck('project_id')->unique())
            ->whereIn('worker_profile_id', $requests->pluck('worker_profile_id')->filter()->unique())
            ->get()
            ->keyBy(fn (Rating $rating) => $rating->project_id.':'.$rating->worker_profile_id);

        $requests->each(function (WorkerRequest $request) use ($ratings) {
            $request->rating = $ratings->get($request->project_id.':'.$request->worker_profile_id);
        });
    }

    private function requestDirection(User $user, Project $project, WorkerRequest $request): string
    {
        if ($this->isAdministrator($user)) {
            return 'administrative';
        }

        return $this->isRequestRecipient($user, $project, $request) ? 'incoming' : 'outgoing';
    }

    private function canRespondToRequest(User $user, Project $project, WorkerRequest $request): bool
    {
        return $request->status === 'pending'
            && $this->isRequestRecipient($user, $project, $request);
    }

    private function isRequestRecipient(User $user, Project $project, WorkerRequest $request): bool
    {
        if ($this->isAdministrator($user)) {
            return true;
        }

        if ($request->type === 'apply') {
            return $this->isCompanyManager($user)
                && $project->hiring_company_id === $user->company_id;
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

    private function canStopRecruiting(User $user, Project $project): bool
    {
        return $this->canManageProject($user, $project)
            && $project->status === 'open'
            && $project->recruiting_closed_at === null
            && $project->committed_worker_count > 0
            && $project->remaining_capacity > 0;
    }

    private function canStartProject(User $user, Project $project): bool
    {
        return $this->canManageProject($user, $project)
            && $project->status === 'open'
            && $project->recruiting_closed_at !== null
            && $project->committed_worker_count > 0
            && $project->start_date !== null
            && $this->businessDates->hasReachedStartDate($project);
    }

    private function canCompleteAssignment(User $user, Project $project, WorkerRequest $request): bool
    {
        return $this->canManageProject($user, $project)
            && $project->status === 'in_progress'
            && $request->status === 'ongoing'
            && $project->end_date !== null
            && $this->businessDates->hasReachedEndDate($project);
    }

    private function canEndAssignmentEarly(User $user, Project $project, WorkerRequest $request): bool
    {
        return $this->canManageProject($user, $project)
            && $project->status === 'in_progress'
            && in_array($request->status, ['accepted', 'ongoing'], true);
    }

    private function canManageProject(User $user, Project $project): bool
    {
        return $this->isAdministrator($user)
            || ($this->isCompanyManager($user) && $project->hiring_company_id === $user->company_id);
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
            $request->project_context = [
                'is_own_project' => $companyId !== null
                    && $request->project->hiring_company_id === $companyId,
            ];

            if ($includeRating) {
                $request->rating = $request->project->ratings
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
                $project = Project::query()
                    ->lockForUpdate()
                    ->findOrFail($workerRequest->project_id);

                $lockedRequest = WorkerRequest::query()
                    ->lockForUpdate()
                    ->findOrFail($workerRequest->id);

                if ($lockedRequest->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'action' => __('app.project_management_page.validation.request_no_longer_pending'),
                    ]);
                }

                if ($project->recruiting_closed_at !== null) {
                    throw ValidationException::withMessages([
                        'action' => __('app.project_management_page.validation.recruiting_closed'),
                    ]);
                }

                if (! $project->isActionableForStaffing() || $project->remaining_capacity <= 0) {
                    throw ValidationException::withMessages([
                        'action' => __('app.project_management_page.validation.project_not_eligible_for_staffing'),
                    ]);
                }

                $workerAlreadyCommitted = WorkerRequest::query()
                    ->where('project_id', $project->id)
                    ->where('worker_profile_id', $lockedRequest->worker_profile_id)
                    ->whereKeyNot($lockedRequest->id)
                    ->whereIn('status', Project::COMMITTED_REQUEST_STATUSES)
                    ->exists();

                if ($workerAlreadyCommitted) {
                    throw ValidationException::withMessages([
                        'action' => __('app.project_management_page.validation.worker_already_committed'),
                    ]);
                }

                $lockedRequest->update([
                    'status' => 'accepted',
                    'responded_by' => auth()->id(),
                    'responded_at' => now(),
                ]);

                if ($project->committedRequests()->count() >= $project->workers) {
                    $project->closeRecruitingAndCancelPending();
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
            
        return back()->with('success', __('app.project_management_page.success.request_updated'));
    }

    public function closeRecruiting(Project $project)
    {
        $this->authorize('closeRecruiting', $project);

        DB::transaction(function () use ($project): void {
            $lockedProject = Project::query()
                ->lockForUpdate()
                ->findOrFail($project->id);

            if ($lockedProject->status !== 'open' || $lockedProject->recruiting_closed_at !== null) {
                throw ValidationException::withMessages([
                    'project' => __('app.project_management_page.validation.recruiting_already_closed'),
                ]);
            }

            if ($lockedProject->committedRequests()->count() < 1) {
                throw ValidationException::withMessages([
                    'project' => __('app.project_management_page.validation.recruiting_requires_committed_worker'),
                ]);
            }

            $lockedProject->closeRecruitingAndCancelPending();
        });

        return back()->with('success', __('app.project_management_page.success.recruiting_closed'));
    }

    public function start(Project $project)
    {
        $this->authorize('start', $project);

        DB::transaction(function () use ($project): void {
            $lockedProject = Project::query()
                ->lockForUpdate()
                ->findOrFail($project->id);

            if ($lockedProject->status !== 'open' || $lockedProject->recruiting_closed_at === null) {
                throw ValidationException::withMessages([
                    'project' => __('app.project_management_page.validation.project_not_ready_to_start'),
                ]);
            }

            if (! $this->businessDates->hasReachedStartDate($lockedProject)) {
                throw ValidationException::withMessages([
                    'project' => __('app.project_management_page.validation.project_start_date_not_reached'),
                ]);
            }

            if ($lockedProject->committedRequests()->count() < 1) {
                throw ValidationException::withMessages([
                    'project' => __('app.project_management_page.validation.project_start_requires_committed_worker'),
                ]);
            }

            $lockedProject->closeRecruitingAndCancelPending();
            $lockedProject->startExecution();
        });

        return back()->with('success', __('app.project_management_page.success.project_started'));
    }

    public function complete(Request $request, WorkerRequest $workerRequest)
    {
        $this->authorize('complete', $workerRequest);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($workerRequest, $validated): void {
            $project = Project::query()
                ->lockForUpdate()
                ->findOrFail($workerRequest->project_id);

            $lockedRequest = WorkerRequest::query()
                ->lockForUpdate()
                ->findOrFail($workerRequest->id);

            if ($project->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'assignment' => __('app.project_management_page.validation.project_not_in_progress'),
                ]);
            }

            if ($lockedRequest->status !== 'ongoing') {
                throw ValidationException::withMessages([
                    'assignment' => __('app.project_management_page.validation.assignment_not_ready_to_complete'),
                ]);
            }

            if (! $this->businessDates->hasReachedEndDate($project)) {
                throw ValidationException::withMessages([
                    'assignment' => __('app.project_management_page.validation.project_end_date_not_reached'),
                ]);
            }

            if (Rating::query()
                ->where('project_id', $project->id)
                ->where('worker_profile_id', $lockedRequest->worker_profile_id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'rating' => __('app.project_management_page.validation.rating_already_exists'),
                ]);
            }

            Rating::create([
                'project_id' => $project->id,
                'reviewed_by_user_id' => auth()->id(),
                'worker_profile_id' => $lockedRequest->worker_profile_id,
                'score' => $validated['rating'],
                'feedback' => $validated['comment'] ?? null,
            ]);

            $lockedRequest->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $project->completeIfReady();
        });

        return back()->with('success', __('app.project_management_page.success.assignment_completed'));
    }

    public function endEarly(Request $request, WorkerRequest $workerRequest)
    {
        $this->authorize('endEarly', $workerRequest);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($workerRequest, $validated): void {
            $project = Project::query()
                ->lockForUpdate()
                ->findOrFail($workerRequest->project_id);

            $lockedRequest = WorkerRequest::query()
                ->lockForUpdate()
                ->findOrFail($workerRequest->id);

            if ($project->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'assignment' => __('app.project_management_page.validation.project_not_in_progress'),
                ]);
            }

            if (! in_array($lockedRequest->status, ['accepted', 'ongoing'], true)) {
                throw ValidationException::withMessages([
                    'assignment' => __('app.project_management_page.validation.assignment_not_ready_to_end_early'),
                ]);
            }

            if (Rating::query()
                ->where('project_id', $project->id)
                ->where('worker_profile_id', $lockedRequest->worker_profile_id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'rating' => __('app.project_management_page.validation.rating_already_exists'),
                ]);
            }

            Rating::create([
                'project_id' => $project->id,
                'reviewed_by_user_id' => auth()->id(),
                'worker_profile_id' => $lockedRequest->worker_profile_id,
                'score' => $validated['rating'],
                'feedback' => $validated['comment'] ?? null,
            ]);

            $lockedRequest->update([
                'status' => 'ended_early',
                'ended_at' => now(),
            ]);

            // An early-ended assignment is resolved, but the project cannot finish
            // before its own end date or while other active assignments remain.
            $project->completeIfReady();
        });

        return back()->with('success', __('app.project_management_page.success.assignment_ended_early'));
    }
}
