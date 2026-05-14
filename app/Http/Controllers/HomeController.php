<?php

namespace App\Http\Controllers;

use App\Models\Mission;
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

        $isSelfEmployed = !$companyId;

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

                'ongoing_missions' => Mission::where('hiring_company_id', $companyId)
                    ->where('status', 'in_progress')
                    ->count(),

                'pending_requests' => $pendingTotal,

                'active_workers' => WorkerProfile::where('company_id', $companyId)
                    ->count(),

                'total_missions' => Mission::where('hiring_company_id', $companyId)
                    ->count(),
            ]
        ]);
    }
}