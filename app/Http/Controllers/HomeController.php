<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $isCompanyOperator = $user->hasCompanyOperationalContext();
        $isSelfEmployed = $user->isSelfEmployed();

        if (! $isCompanyOperator && ! $isSelfEmployed) {
            return Inertia::render('Home', [
                'stats' => [
                    'ongoing_projects' => 0,
                    'pending_requests' => 0,
                    'active_workers' => 0,
                    'total_projects' => 0,
                ],
            ]);
        }

        if ($isSelfEmployed) {
            return Inertia::render('Home', [
                'stats' => $this->selfEmployedStats($user),
            ]);
        }

        // ========================
        // PENDING REQUESTS
        // ========================
        $requestsSentCount = WorkerRequest::where('company_id', $companyId)
            ->where('type', 'invite')
            ->where('status', 'pending')
            ->count();

        $requestsReceivedCount = WorkerRequest::where(function ($q) use ($companyId, $user, $isSelfEmployed) {

                // Self-employed users
                if ($isSelfEmployed) {

                    $q->whereHas('worker', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    })
                    ->where('type', 'invite');

                } else {

                    // Requests for my company workers
                    $q->where(function ($sub) use ($companyId) {

                        $sub->whereHas('worker', function ($q2) use ($companyId) {
                            $q2->where('company_id', $companyId);
                        })
                        ->where('type', 'invite');

                    })

                    // Applications to my projects
                    ->orWhere(function ($sub) use ($companyId) {

                        $sub->whereHas('project', function ($q2) use ($companyId) {
                            $q2->where('hiring_company_id', $companyId);
                        })
                        ->where('type', 'apply');

                    });
                }
            })
            ->where('status', 'pending')
            ->count();

        $requestsToJoinCount = WorkerRequest::where('company_id', $companyId)
            ->where('type', 'apply')
            ->where('status', 'pending')
            ->count();

        $pendingTotal =
            $requestsSentCount +
            $requestsReceivedCount +
            $requestsToJoinCount;

        return Inertia::render('Home', [
            'stats' => [

                'ongoing_projects' => Project::where('hiring_company_id', $companyId)
                    ->where('status', 'in_progress')
                    ->count(),

                'pending_requests' => $pendingTotal,

                'active_workers' => WorkerProfile::query()
                    ->notArchived()
                    ->where('company_id', $companyId)
                    ->count(),

                'total_projects' => Project::where('hiring_company_id', $companyId)
                    ->count(),
            ]
        ]);
    }

    private function selfEmployedStats($user): array
    {
        $workerScope = function ($query) use ($user): void {
            $query
                ->notArchived()
                ->whereNull('company_id')
                ->where('user_id', $user->id);
        };

        return [
            'ongoing_projects' => WorkerRequest::query()
                ->where('status', 'ongoing')
                ->whereHas('worker', $workerScope)
                ->whereHas('project', fn ($query) => $query->where('status', 'in_progress'))
                ->count(),

            'pending_requests' => WorkerRequest::query()
                ->where('status', 'pending')
                ->whereHas('worker', $workerScope)
                ->where(function ($query) use ($user) {
                    $query
                        ->where('type', 'invite')
                        ->orWhere(function ($applicationQuery) use ($user) {
                            $applicationQuery
                                ->where('type', 'apply')
                                ->where('requested_by', $user->id);
                        });
                })
                ->count(),

            'completed_projects' => WorkerRequest::query()
                ->whereIn('status', ['completed', 'ended_early'])
                ->whereHas('worker', function ($query) use ($user): void {
                    $query
                        ->whereNull('company_id')
                        ->where('user_id', $user->id);
                })
                ->count(),

            'total_applications' => WorkerRequest::query()
                ->where('type', 'apply')
                ->where('requested_by', $user->id)
                ->count(),
        ];
    }
}
