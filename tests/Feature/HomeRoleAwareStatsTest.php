<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomeRoleAwareStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_owner_receives_company_oriented_home_metrics(): void
    {
        [$owner, $company] = $this->companyUser('company_owner', 'Owner Company');
        $this->project($owner, $company, 'Active company project', 'in_progress');
        $this->worker($owner, $company, 'Company Electrician');

        $this->actingAs($owner)
            ->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->where('stats.ongoing_projects', 1)
                ->where('stats.pending_requests', 0)
                ->where('stats.active_workers', 1)
                ->where('stats.total_projects', 1)
                ->missing('stats.completed_projects')
                ->missing('stats.total_applications')
            );
    }

    public function test_planning_manager_with_company_context_receives_company_oriented_home_metrics(): void
    {
        [$owner, $company] = $this->companyUser('company_owner', 'Managed Company');
        $manager = $this->user('planning_manager', ['company_id' => $company->id]);
        $this->project($owner, $company, 'Planning project', 'in_progress');
        $this->worker($owner, $company, 'Company Welder');

        $this->actingAs($manager)
            ->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->where('stats.ongoing_projects', 1)
                ->where('stats.active_workers', 1)
                ->where('stats.total_projects', 1)
                ->missing('stats.completed_projects')
            );
    }

    public function test_self_employed_metrics_are_scoped_to_their_own_assignment_and_request_history(): void
    {
        $selfEmployed = $this->user('self_employed');
        $selfWorker = $this->worker($selfEmployed, null, 'Independent Electrician');
        $otherSelfEmployed = $this->user('self_employed');
        $otherWorker = $this->worker($otherSelfEmployed, null, 'Other Independent Electrician');
        [$hiringOwner, $hiringCompany] = $this->companyUser('company_owner', 'Hiring Company');

        $ongoingProject = $this->project($hiringOwner, $hiringCompany, 'Ongoing assignment', 'in_progress');
        $this->request($ongoingProject, $selfEmployed, null, $selfWorker, 'apply', 'ongoing');

        $acceptedProject = $this->project($hiringOwner, $hiringCompany, 'Awaiting start', 'open');
        $this->request($acceptedProject, $selfEmployed, null, $selfWorker, 'apply', 'accepted');

        $completedProject = $this->project($hiringOwner, $hiringCompany, 'Completed assignment', 'completed');
        $this->request($completedProject, $selfEmployed, null, $selfWorker, 'apply', 'completed');

        $earlyEndProject = $this->project($hiringOwner, $hiringCompany, 'Ended early assignment', 'in_progress');
        $this->request($earlyEndProject, $selfEmployed, null, $selfWorker, 'apply', 'ended_early');

        $pendingInviteProject = $this->project($hiringOwner, $hiringCompany, 'Pending invitation', 'open');
        $this->request($pendingInviteProject, $hiringOwner, $hiringCompany, $selfWorker, 'invite', 'pending');

        $pendingApplicationProject = $this->project($hiringOwner, $hiringCompany, 'Pending application', 'open');
        $this->request($pendingApplicationProject, $selfEmployed, null, $selfWorker, 'apply', 'pending');

        $rejectedApplicationProject = $this->project($hiringOwner, $hiringCompany, 'Rejected application', 'open');
        $this->request($rejectedApplicationProject, $selfEmployed, null, $selfWorker, 'apply', 'rejected');

        $otherProject = $this->project($hiringOwner, $hiringCompany, 'Other worker assignment', 'in_progress');
        $this->request($otherProject, $otherSelfEmployed, null, $otherWorker, 'apply', 'ongoing');

        $this->actingAs($selfEmployed)
            ->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->where('stats.ongoing_projects', 1)
                ->where('stats.pending_requests', 2)
                ->where('stats.completed_projects', 2)
                ->where('stats.total_applications', 6)
                ->missing('stats.active_workers')
                ->missing('stats.total_projects')
            );

        $this->actingAs($selfEmployed)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('data.completed.joined.total', 2)
            );
    }

    public function test_archived_self_employed_worker_history_remains_in_completed_and_application_metrics(): void
    {
        $selfEmployed = $this->user('self_employed');
        $archivedWorker = $this->worker($selfEmployed, null, 'Archived independent worker', [
            'archived_at' => now(),
        ]);
        [$hiringOwner, $hiringCompany] = $this->companyUser('company_owner', 'Historical Hiring Company');

        $completedProject = $this->project($hiringOwner, $hiringCompany, 'Historical completion', 'completed');
        $this->request($completedProject, $selfEmployed, null, $archivedWorker, 'apply', 'completed');

        $pendingProject = $this->project($hiringOwner, $hiringCompany, 'Historical pending application', 'open');
        $this->request($pendingProject, $selfEmployed, null, $archivedWorker, 'apply', 'pending');

        $this->actingAs($selfEmployed)
            ->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('stats.pending_requests', 0)
                ->where('stats.completed_projects', 1)
                ->where('stats.total_applications', 2)
            );
    }

    public function test_administrator_without_company_context_keeps_neutral_home_metrics(): void
    {
        $administrator = $this->user('administrator');

        $this->actingAs($administrator)
            ->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->where('stats.ongoing_projects', 0)
                ->where('stats.pending_requests', 0)
                ->where('stats.active_workers', 0)
                ->where('stats.total_projects', 0)
                ->missing('stats.completed_projects')
                ->missing('stats.total_applications')
            );
    }

    private function companyUser(string $roleName, string $companyName): array
    {
        $owner = $this->user('company_owner');
        $company = Company::create([
            'name' => $companyName,
            'phone' => '403-555-0100',
            'address' => '100 Test Street',
            'owner_id' => $owner->id,
        ]);

        $owner->update(['company_id' => $company->id]);

        return [$owner->fresh(), $company];
    }

    private function user(string $roleName, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role_id' => Role::firstOrCreate(['name' => $roleName])->id,
            'company_id' => null,
            'email_verified_at' => now(),
        ], $attributes));
    }

    private function worker(User $user, ?Company $company, string $name, array $attributes = []): WorkerProfile
    {
        return WorkerProfile::create(array_merge([
            'user_id' => $company === null ? $user->id : null,
            'company_id' => $company?->id,
            'name' => $name,
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ], $attributes));
    }

    private function project(User $owner, Company $company, string $title, string $status): Project
    {
        return Project::create([
            'hiring_company_id' => $company->id,
            'created_by' => $owner->id,
            'title' => $title,
            'city' => 'Calgary',
            'province' => 'Alberta',
            'country' => 'Canada',
            'job_type' => 'Electrician',
            'workers' => 1,
            'start_date' => today()->subWeek(),
            'end_date' => today(),
            'status' => $status,
        ]);
    }

    private function request(
        Project $project,
        User $requestedBy,
        ?Company $company,
        WorkerProfile $worker,
        string $type,
        string $status,
    ): WorkerRequest {
        return WorkerRequest::create([
            'project_id' => $project->id,
            'requested_by' => $requestedBy->id,
            'company_id' => $company?->id,
            'worker_profile_id' => $worker->id,
            'type' => $type,
            'status' => $status,
            'completed_at' => $status === 'completed' ? now() : null,
            'ended_at' => $status === 'ended_early' ? now() : null,
        ]);
    }
}
