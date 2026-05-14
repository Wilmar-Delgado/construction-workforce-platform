<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\WorkerRequestCreated;

class WorkerRequestController extends Controller
{
    public function store(Request $request, WorkerProfile $worker)
    {
        $validated = $request->validate(
            [
                'mission_id' => 'required|exists:missions,id',
                'message' => 'nullable|string|max:1000',
            ],
            [
                'mission_id.required' => 'Please select a mission.',
                'mission_id.exists' => 'The selected mission is invalid.',
                'message.max' => 'Message cannot exceed 1000 characters.',
            ]
        );

        $companyId = auth()->user()->company_id;

        // Prevent duplicate requests
        $alreadyExists = WorkerRequest::where([
            'mission_id'        => $validated['mission_id'],
            'worker_profile_id' => $worker->id,
            'company_id'        => $companyId,
            'type'              => 'invite',
            'status'            => 'pending',
        ])->exists();

        if ($alreadyExists) {
            return back()->withErrors([
                'mission_id' => 'You already requested this worker for this mission.'
            ]);
        }

        // Create request
        $requestModel = WorkerRequest::create([
            'mission_id'        => $validated['mission_id'],
            'requested_by'      => auth()->id(),
            'company_id'        => $companyId,
            'worker_profile_id' => $worker->id,
            'type'              => 'invite',
            'message'           => $validated['message'] ?? null,
            'status'            => 'pending',
            'responded_by'      => null,
            'responded_at'      => null,
        ]);

        // Load relationships (important for email view)
        $requestModel->load([
            'mission',
            'worker.company',
            'company',
            'requester'
        ]);

        // Determine recipient
        if ($worker->company_id) {
            // Worker belongs to a company → send to company owner

            $companyOwner = User::where('company_id', $worker->company_id)
            ->whereHas('role', function ($q) {
                $q->where('name', 'company_owner');
            })
            ->first();

            if ($companyOwner) {
                Mail::to($companyOwner->email)->send(new WorkerRequestCreated($requestModel));
            }

        } else {
            // Self-employed → send directly to worker

            $workerUser = User::where('id', $worker->user_id)->first();

            if ($workerUser) {
                Mail::to($workerUser->email)->send(new WorkerRequestCreated($requestModel));
            }
        }

        return back()->with('success', 'Request sent successfully.');
    }
}
