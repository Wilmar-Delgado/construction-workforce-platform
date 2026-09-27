<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Mail\WorkerRequestCreated;

class WorkerRequestController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, WorkerProfile $worker)
    {
        $validated = $request->validate(
            [
                'project_id' => 'required|exists:projects,id',
                'message' => 'nullable|string|max:1000',
            ],
            [
                'project_id.required' => __('app.find_workers_page.validation.project_required'),
                'project_id.exists' => __('app.find_workers_page.validation.project_invalid'),
                'message.max' => __('app.find_workers_page.validation.message_max'),
            ]
        );

        $project = Project::findOrFail($validated['project_id']);

        if (! $worker->isOperationallyAvailable()) {
            throw ValidationException::withMessages([
                'worker' => __('app.find_workers_page.request_modal.archived_worker_cannot_receive_request'),
            ]);
        }

        if (! $request->user()->hasCompanyOperationalContext()) {
            throw ValidationException::withMessages([
                'company' => [__('app.common.validation.company_context_required')],
            ]);
        }

        $this->authorize('createInvite', [WorkerRequest::class, $project, $worker]);

        $companyId = auth()->user()->company_id;

        // A worker has one durable request record per project, regardless of request type or status.
        $alreadyExists = WorkerRequest::where([
            'project_id'        => $validated['project_id'],
            'worker_profile_id' => $worker->id,
        ])->exists();

        if ($alreadyExists) {
            return back()->withErrors([
                'project_id' => __('app.find_workers_page.validation.worker_already_requested'),
            ]);
        }

        // Create request
        $requestModel = WorkerRequest::create([
            'project_id'        => $validated['project_id'],
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
            'project',
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
                Mail::to($companyOwner->email)
                    ->locale($companyOwner->language)
                    ->send(new WorkerRequestCreated($requestModel));
            }

        } else {
            // Self-employed → send directly to worker

            $workerUser = User::where('id', $worker->user_id)->first();

            if ($workerUser) {
                Mail::to($workerUser->email)
                    ->locale($workerUser->language)
                    ->send(new WorkerRequestCreated($requestModel));
            }
        }

        return back()->with('success', __('app.find_workers_page.success.request_sent'));
    }
}
