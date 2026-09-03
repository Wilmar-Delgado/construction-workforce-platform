<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MissionManagementController extends Controller
{
    use AuthorizesRequests;

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
        $requestsSentQuery = WorkerRequest::with([
                'mission',
                'worker.user',
                'worker.company.owner',
                'company'
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

        // Requests Received
        $requestsReceivedQuery = WorkerRequest::with([
                'mission',
                'worker.user',
                'worker.company.owner',
                'company'
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
                'mission.hiringCompany.owner',
                'worker.user',
                'worker.company.owner',
                'company'
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
        $ongoingCreatedQuery = WorkerRequest::with([
                'mission',
                'worker.user',
                'worker.company.owner',
                'company'
            ])
            ->whereIn('status', ['accepted', 'ongoing']);

        if (! $isAdministrator) {
            if ($isCompanyManager) {
                $ongoingCreatedQuery->whereHas('mission', function ($q) use ($companyId) {
                    $q->where('hiring_company_id', $companyId)
                        ->where('status', 'in_progress');
                });
            } else {
                $ongoingCreatedQuery->whereRaw('1 = 0');
            }
        }

        $ongoingCreated = $ongoingCreatedQuery
            ->paginate(10, ['*'], 'ongoing_created_page')
            ->withQueryString();

        // Missions my workers joined externally
        $ongoingJoinedQuery = WorkerRequest::with([
                'mission.hiringCompany.owner',
                'worker.user',
                'worker.company.owner',
                'company'
            ])
            ->whereIn('status', ['accepted', 'ongoing']);

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

        $completedCreatedQuery = WorkerRequest::with([
                'mission.ratings',
                'worker.user',
                'worker.company.owner',
                'company'
            ])
            ->where('status', 'completed');

        if (! $isAdministrator) {
            if ($isCompanyManager) {
                $completedCreatedQuery->whereHas('mission', function ($q) use ($companyId) {
                    $q->where('hiring_company_id', $companyId)
                        ->where('status', 'completed');
                });
            } else {
                $completedCreatedQuery->whereRaw('1 = 0');
            }
        }

        $completedCreated = $completedCreatedQuery
            ->paginate(10, ['*'], 'completed_created_page')
            ->withQueryString();

        $completedCreated->getCollection()->transform(function ($request) {
            $request->rating = $request->mission->ratings
                ->firstWhere('worker_profile_id', $request->worker_profile_id);
                
            return $request;
        });

        $completedJoinedQuery = WorkerRequest::with([
                'mission.ratings',
                'worker.user',
                'worker.company.owner',
                'company'
            ])
            ->where('status', 'completed');

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

        $completedJoined->getCollection()->transform(function ($request) {
            $request->rating = $request->mission->ratings
                ->firstWhere('worker_profile_id', $request->worker_profile_id);

            return $request;
        });

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
            ]
        ]);
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
            $workerRequest->update([
                'status' => 'accepted',
                'responded_by' => auth()->id(),
                'responded_at' => now(),
            ]);

            if ($workerRequest->mission?->status === 'open') {
                $workerRequest->mission->update([
                    'status' => 'in_progress',
                ]);
            }
            // Send acceptance email
        } elseif ($validated['action'] === 'reject') {
            $workerRequest->update([
                'status' => 'rejected',
                'responded_by' => auth()->id(),
                'responded_at' => now(),
            ]);
            // Send rejection email
        }
            
        return back()->with('success', 'Request updated successfully.');
    }

    public function complete(Request $request, WorkerRequest $workerRequest)
    {
        $this->authorize('complete', $workerRequest);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Rating::create([
            'mission_id'    => $workerRequest->mission_id,
            'reviewed_by_user_id' => auth()->id(),
            'worker_profile_id' => $workerRequest->worker->id,
            'score'         => $validated['rating'],
            'feedback'      => $validated['comment'],
        ]);

        $workerRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $remaining = WorkerRequest::where('mission_id', $workerRequest->mission_id)
            ->whereIn('status', ['accepted', 'ongoing'])
            ->exists();

        if (!$remaining && $workerRequest->mission?->status === 'in_progress') {
            $workerRequest->mission->update([
                'status' => 'completed',
            ]);
        }

        return back()->with(
            'success',
            'Mission completed successfully.'
        );
    }
}
