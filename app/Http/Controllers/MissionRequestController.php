<?php

namespace App\Http\Controllers;

use App\Mail\MissionRequestCreated;
use App\Models\Mission;
use App\Models\MissionRequest;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MissionRequestController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Mission $mission)
    {
        $validated = $request->validate([
            'worker_profile_id' => 'required|exists:worker_profiles,id',
            'message' => 'nullable|string|max:1000',
        ]);

        // Load worker profile
        $worker = WorkerProfile::findOrFail(
            $validated['worker_profile_id']
        );

        $this->authorize('createApplication', [WorkerRequest::class, $mission, $worker]);

        $user = auth()->user();

        // Prevent duplicate requests
        $alreadyExists = MissionRequest::where([
            'mission_id' => $mission->id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => 'pending',
        ])->exists();

        if ($alreadyExists) {
            return back()->withErrors([
                'worker_profile_id' => 'This worker was already offered to this mission.'
            ]);
        }

        $lendingCompanyId = null;
        // Company user
        if ($user->company_id) {
            $lendingCompanyId = $user->company_id;
        }

        // Create request
        $requestModel = MissionRequest::create([
            'mission_id' => $mission->id,
            'requested_by' => $user->id,
            'company_id' => $lendingCompanyId, // NULL for self-employed
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
            'responded_by' => null,
            'responded_at' => null,
        ]);

        // Load relationships (important for email view)
        $requestModel->load([
            'mission.hiringCompany',
            'worker.company',
            'company',
            'requester',
        ]);

        // Send email to company owner of the hiring company
        $companyOwner = User::where(
            'company_id',
            $mission->hiring_company_id
        )
        ->whereHas('role', function ($q) {
            $q->where('name', 'company_owner');
        })
        ->first();

        if ($companyOwner) {
            Mail::to('gabhenriquezmor@gmail.com')
                ->send(new MissionRequestCreated($requestModel));
        }

        return back()->with('success', 'Request to join mission sent successfully.');
    }
}
