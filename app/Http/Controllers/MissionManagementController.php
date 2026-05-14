<?php

namespace App\Http\Controllers;

use App\Models\WorkerRequest;
use Inertia\Inertia;

class MissionManagementController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        $isSelfEmployed = !$companyId;

        // ========================
        // REQUESTS (PENDING TAB)
        // ========================

        // Requests Sent
        $requestsSent = WorkerRequest::with([
                'mission',
                'worker.user',
                'worker.company.owner',
                'company'
            ])
            ->where('company_id', $companyId)
            ->where('type', 'invite')
            ->where('status', 'pending')
            ->get();

        // Requests Received
        $requestsReceived = WorkerRequest::with([
                'mission',
                'worker.user',
                'worker.company.owner',
                'company'
            ])
            ->where(function ($q) use ($companyId, $user, $isSelfEmployed) {
                // Self-employed
                if ($isSelfEmployed) {
                    $q->whereHas('worker', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    })
                    ->where('type', 'invite');
                } else {
                    // Requests for my workers
                    $q->where(function ($sub) use ($companyId) {
                        $sub->whereHas('worker', function ($q2) use ($companyId) {
                            $q2->where('company_id', $companyId);
                        })
                        ->where('type', 'invite');
                    })

                    // Applications to my missions
                    ->orWhere(function ($sub) use ($companyId) {
                        $sub->whereHas('mission', function ($q2) use ($companyId) {
                            $q2->where('hiring_company_id', $companyId);
                        })
                        ->where('type', 'apply');
                    });
                }
            })
            ->where('status', 'pending')
            ->get();

        // Requests To Join
        $requestsToJoin = WorkerRequest::with([
                'mission',
                'worker.user',
                'worker.company.owner',
                'company'
            ])
            ->where('company_id', $companyId)
            ->where('type', 'apply')
            ->where('status', 'pending')
            ->get();

        // ========================
        // ONGOING
        // ========================

        // Missions created by my company
        $ongoingCreated = WorkerRequest::with([
                'mission',
                'worker.user',
                'worker.company.owner',
                'company'
            ])
            ->whereHas('mission', function ($q) use ($companyId) {
                $q->where('hiring_company_id', $companyId)
                    ->where('status', 'in_progress');
            })
            ->whereIn('status', ['accepted', 'ongoing'])
            ->get();

        // Missions my workers joined externally
        $ongoingJoined = WorkerRequest::with([
                'mission.hiringCompany.owner',
                'worker.user',
                'worker.company.owner',
                'company'
            ])
            ->whereHas('worker', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->where('type', 'invite')
            ->whereIn('status', ['accepted', 'ongoing'])
            ->get();

        // ========================
        // COMPLETED
        // ========================

        $completedCreated = WorkerRequest::with([
                'mission',
                'worker.user',
                'worker.company.owner',
                'company'
            ])
            ->whereHas('mission', function ($q) use ($companyId) {
                $q->where('hiring_company_id', $companyId)
                    ->where('status', 'completed');
            })
            ->where('status', 'completed')
            ->get();

        $completedJoined = WorkerRequest::with([
                'mission.hiringCompany.owner',
                'worker.user',
                'worker.company.owner',
                'company'
            ])
            ->whereHas('worker', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->where('type', 'invite')
            ->where('status', 'completed')
            ->get();

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
}