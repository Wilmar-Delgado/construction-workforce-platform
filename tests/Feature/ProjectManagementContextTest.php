<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\Rating;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectManagementContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_management_exposes_ownership_context_and_the_matching_rating_reviewer(): void
    {
        [$viewer, $viewerCompany] = $this->companyUser('Viewer Company', 'company_owner');
        [$externalOwner, $externalCompany] = $this->companyUser('External Company', 'company_owner');
        $planningManager = $this->companyMember($externalCompany, 'planning_manager', 'External Planning Manager');

        $ownProject = $this->project($viewer, 'Own project');
        $externalProject = $this->project($externalOwner, 'External project');

        $viewerWorkerA = $this->worker($viewerCompany, 'Viewer Worker A');
        $viewerWorkerB = $this->worker($viewerCompany, 'Viewer Worker B');
        $viewerWorkerC = $this->worker($viewerCompany, 'Viewer Worker C');
        $externalWorker = $this->worker($externalCompany, 'External Worker');

        WorkerRequest::create([
            'project_id' => $ownProject->id,
            'requested_by' => $viewer->id,
            'company_id' => $viewerCompany->id,
            'worker_profile_id' => $externalWorker->id,
            'type' => 'invite',
            'status' => 'pending',
        ]);

        WorkerRequest::create([
            'project_id' => $externalProject->id,
            'requested_by' => $viewer->id,
            'company_id' => $viewerCompany->id,
            'worker_profile_id' => $viewerWorkerC->id,
            'type' => 'apply',
            'status' => 'pending',
        ]);

        WorkerRequest::create([
            'project_id' => $ownProject->id,
            'requested_by' => $viewer->id,
            'company_id' => $viewerCompany->id,
            'worker_profile_id' => $viewerWorkerA->id,
            'type' => 'apply',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $externalRequestA = WorkerRequest::create([
            'project_id' => $externalProject->id,
            'requested_by' => $viewer->id,
            'company_id' => $viewerCompany->id,
            'worker_profile_id' => $viewerWorkerA->id,
            'type' => 'apply',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $externalRequestB = WorkerRequest::create([
            'project_id' => $externalProject->id,
            'requested_by' => $viewer->id,
            'company_id' => $viewerCompany->id,
            'worker_profile_id' => $viewerWorkerB->id,
            'type' => 'apply',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Rating::create([
            'project_id' => $externalProject->id,
            'reviewed_by_user_id' => $planningManager->id,
            'worker_profile_id' => $viewerWorkerA->id,
            'score' => 5,
            'feedback' => 'Excellent work.',
        ]);

        Rating::create([
            'project_id' => $externalProject->id,
            'reviewed_by_user_id' => $externalOwner->id,
            'worker_profile_id' => $viewerWorkerB->id,
            'score' => 3,
            'feedback' => 'Solid work.',
        ]);

        $this->actingAs($viewer)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('ProjectManagement')
                ->has('data.pending.sent')
                ->has('data.ongoing.created')
                ->has('data.completed.created')
                ->where('data.pending.sent.data.0.project_context.is_own_project', true)
                ->where('data.pending.join.data.0.project_context.is_own_project', false)
                ->where('data.completed.created.data.0.project_context.is_own_project', true)
                ->has('data.completed.joined.data', 2)
                ->where('data.completed.joined.data.0.id', $externalRequestA->id)
                ->where('data.completed.joined.data.0.project_context.is_own_project', false)
                ->where('data.completed.joined.data.0.project.hiring_company.name', $externalCompany->name)
                ->where('data.completed.joined.data.0.rating.worker_profile_id', $viewerWorkerA->id)
                ->where('data.completed.joined.data.0.rating.reviewer.id', $planningManager->id)
                ->where('data.completed.joined.data.0.rating.reviewer.role.name', 'planning_manager')
                ->where('data.completed.joined.data.1.id', $externalRequestB->id)
                ->where('data.completed.joined.data.1.rating.worker_profile_id', $viewerWorkerB->id)
                ->where('data.completed.joined.data.1.rating.reviewer.id', $externalOwner->id)
                ->where('data.completed.joined.data.1.rating.reviewer.role.name', 'company_owner')
            );
    }

    public function test_self_employed_project_management_omits_impossible_sections_and_keeps_assignment_history(): void
    {
        [$hiringManager, $hiringCompany] = $this->companyUser('Hiring Company', 'company_owner');
        [$externalManager] = $this->companyUser('External Company', 'company_owner');
        $selfEmployedUser = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'self_employed'])->id,
            'company_id' => null,
        ]);
        $worker = WorkerProfile::create([
            'user_id' => $selfEmployedUser->id,
            'company_id' => null,
            'name' => 'Independent worker',
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);

        $invitationProject = $this->project($hiringManager, 'Invitation project');
        $applicationProject = $this->project($externalManager, 'Application project');
        $completedProject = $this->project($externalManager, 'Completed assignment project');
        $activeProject = Project::create([
            'hiring_company_id' => $externalManager->company_id,
            'created_by' => $externalManager->id,
            'title' => 'Active assignment project',
            'city' => 'Calgary',
            'province' => 'Alberta',
            'job_type' => 'Electrician',
            'workers' => 1,
            'start_date' => today(),
            'end_date' => today()->addWeek(),
            'status' => 'open',
        ]);

        WorkerRequest::create([
            'project_id' => $invitationProject->id,
            'requested_by' => $hiringManager->id,
            'company_id' => $hiringCompany->id,
            'worker_profile_id' => $worker->id,
            'type' => 'invite',
            'status' => 'pending',
        ]);

        WorkerRequest::create([
            'project_id' => $applicationProject->id,
            'requested_by' => $selfEmployedUser->id,
            'company_id' => null,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => 'pending',
        ]);

        WorkerRequest::create([
            'project_id' => $activeProject->id,
            'requested_by' => $selfEmployedUser->id,
            'company_id' => null,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => 'accepted',
        ]);

        $endedEarlyRequest = WorkerRequest::create([
            'project_id' => $completedProject->id,
            'requested_by' => $selfEmployedUser->id,
            'company_id' => null,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => 'ended_early',
            'ended_at' => now(),
        ]);

        $this->actingAs($selfEmployedUser)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('ProjectManagement')
                ->where('data.pending.sent', null)
                ->where('data.pending.received.total', 1)
                ->where('data.pending.join.total', 1)
                ->where('data.ongoing.created', null)
                ->where('data.ongoing.joined.total', 1)
                ->where('data.completed.created', null)
                ->where('data.completed.joined.total', 1)
                ->where('data.completed.joined.data.0.id', $endedEarlyRequest->id)
                ->where('data.completed.joined.data.0.status', 'ended_early')
            );
    }

    public function test_pending_received_requests_expose_the_actual_proposer_and_role(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company', 'company_owner');
        [$externalOwner, $externalCompany] = $this->companyUser('External Company', 'company_owner');
        $planningManager = $this->companyMember($externalCompany, 'planning_manager', 'External Planning Manager');
        $selfEmployedUser = User::factory()->create([
            'name' => 'Independent Proposer',
            'role_id' => Role::firstOrCreate(['name' => 'self_employed'])->id,
            'company_id' => null,
        ]);

        $project = $this->project($hiringManager, 'Hiring project');
        $ownerWorker = $this->worker($externalCompany, 'Owner proposed worker');
        $planningWorker = $this->worker($externalCompany, 'Planning proposed worker');
        $independentWorker = WorkerProfile::create([
            'user_id' => $selfEmployedUser->id,
            'company_id' => null,
            'name' => 'Independent proposed worker',
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);

        WorkerRequest::create([
            'project_id' => $project->id,
            'requested_by' => $externalOwner->id,
            'company_id' => $externalCompany->id,
            'worker_profile_id' => $ownerWorker->id,
            'type' => 'apply',
            'status' => 'pending',
        ]);

        WorkerRequest::create([
            'project_id' => $project->id,
            'requested_by' => $planningManager->id,
            'company_id' => $externalCompany->id,
            'worker_profile_id' => $planningWorker->id,
            'type' => 'apply',
            'status' => 'pending',
        ]);

        WorkerRequest::create([
            'project_id' => $project->id,
            'requested_by' => $selfEmployedUser->id,
            'company_id' => null,
            'worker_profile_id' => $independentWorker->id,
            'type' => 'apply',
            'status' => 'pending',
        ]);

        $this->actingAs($hiringManager)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('ProjectManagement')
                ->has('data.pending.received.data', 3)
                ->where('data.pending.received.data.0.requester.id', $externalOwner->id)
                ->where('data.pending.received.data.0.requester.role.name', 'company_owner')
                ->where('data.pending.received.data.0.company.name', $externalCompany->name)
                ->where('data.pending.received.data.1.requester.id', $planningManager->id)
                ->where('data.pending.received.data.1.requester.role.name', 'planning_manager')
                ->where('data.pending.received.data.1.company.name', $externalCompany->name)
                ->where('data.pending.received.data.2.requester.id', $selfEmployedUser->id)
                ->where('data.pending.received.data.2.requester.name', $selfEmployedUser->name)
                ->where('data.pending.received.data.2.requester.role.name', 'self_employed')
                ->where('data.pending.received.data.2.worker.id', $independentWorker->id)
                ->where('data.pending.received.data.2.worker.name', $independentWorker->name)
                ->where('data.pending.received.data.2.company', null)
            );
    }

    private function companyUser(string $companyName, string $roleName): array
    {
        $user = User::factory()->create([
            'name' => $companyName.' Owner',
            'role_id' => Role::firstOrCreate(['name' => $roleName])->id,
        ]);
        $company = Company::create(['name' => $companyName, 'owner_id' => $user->id]);
        $user->update(['company_id' => $company->id]);

        return [$user->fresh(), $company];
    }

    private function companyMember(Company $company, string $roleName, string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'company_id' => $company->id,
            'role_id' => Role::firstOrCreate(['name' => $roleName])->id,
        ]);
    }

    private function project(User $owner, string $title): Project
    {
        return Project::create([
            'hiring_company_id' => $owner->company_id,
            'created_by' => $owner->id,
            'title' => $title,
            'city' => 'Calgary',
            'province' => 'Alberta',
            'job_type' => 'Electrician',
            'workers' => 3,
            'start_date' => today()->subWeek(),
            'end_date' => today(),
            'status' => 'completed',
        ]);
    }

    private function worker(Company $company, string $name): WorkerProfile
    {
        return WorkerProfile::create([
            'company_id' => $company->id,
            'name' => $name,
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);
    }
}
