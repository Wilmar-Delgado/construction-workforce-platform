<?php

namespace App\Http\Controllers;

use App\Mail\ProjectRequestCreated;
use App\Models\Project;
use App\Models\ProjectRequest;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ProjectRequestController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'worker_profile_id' => 'required|exists:worker_profiles,id',
            'message' => 'nullable|string|max:1000',
        ]);

        // Load worker profile
        $worker = WorkerProfile::findOrFail(
            $validated['worker_profile_id']
        );

        if (! $worker->isOperationallyAvailable()) {
            throw ValidationException::withMessages([
                'worker_profile_id' => __('app.find_projects_page.validation.archived_worker_cannot_request'),
            ]);
        }

        if ($request->user()->role?->name === 'administrator'
            && $request->user()->company_id === null) {
            throw ValidationException::withMessages([
                'company' => [__('app.common.validation.company_context_required')],
            ]);
        }

        $this->authorize('createApplication', [WorkerRequest::class, $project, $worker]);

        $user = auth()->user();

        // A worker has one durable request record per project, regardless of request type or status.
        $alreadyExists = ProjectRequest::where([
            'project_id' => $project->id,
            'worker_profile_id' => $worker->id,
        ])->exists();

        if ($alreadyExists) {
            return back()->withErrors([
                'worker_profile_id' => __('app.find_projects_page.validation.worker_already_requested'),
            ]);
        }

        $lendingCompanyId = null;
        // Company user
        if ($user->company_id) {
            $lendingCompanyId = $user->company_id;
        }

        // Create request
        $requestModel = ProjectRequest::create([
            'project_id' => $project->id,
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
            'project.hiringCompany',
            'worker.company',
            'company',
            'requester',
        ]);

        // Send email to the owner recorded for the project's hiring company.
        $companyOwner = $project->hiringCompany?->owner;

        if ($companyOwner) {
            Mail::to($companyOwner->email)
                ->locale($companyOwner->language)
                ->send(new ProjectRequestCreated($requestModel));
        }

        return back()->with('success', __('app.find_projects_page.success.request_sent'));
    }
}
