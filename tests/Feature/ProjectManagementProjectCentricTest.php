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
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectManagementProjectCentricTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_requests_are_grouped_under_one_project_with_mixed_directions(): void
    {
        [$hiringManager, $hiringCompany] = $this->companyUser('Hiring Company');
        [$externalManager, $externalCompany] = $this->companyUser('External Company');
        $project = $this->project($hiringManager, 'Framing crew', 'open');

        $applicantA = $this->worker($externalCompany, 'Applicant A');
        $applicantB = $this->worker($externalCompany, 'Applicant B');
        $invitedWorker = $this->worker($externalCompany, 'Invited worker');

        $applicationA = $this->request($project, $externalManager, $externalCompany, $applicantA, 'apply', 'pending');
        $applicationB = $this->request($project, $externalManager, $externalCompany, $applicantB, 'apply', 'pending');
        $invitation = $this->request($project, $hiringManager, $hiringCompany, $invitedWorker, 'invite', 'pending');
        DB::table('requests')->where('id', $applicationA->id)->update(['created_at' => now()->subMinutes(3)]);
        DB::table('requests')->where('id', $applicationB->id)->update(['created_at' => now()->subMinutes(2)]);
        DB::table('requests')->where('id', $invitation->id)->update(['created_at' => now()->subMinute()]);

        $this->actingAs($hiringManager)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('ProjectManagement')
                ->where('projectData.counts.requests', 1)
                ->has('projectData.tabs.requests.data', 1)
                ->where('projectData.tabs.requests.data.0.id', $project->id)
                ->has('projectData.tabs.requests.data.0.management_requests', 3)
                ->where('projectData.tabs.requests.data.0.management_context.is_own_project', true)
                ->where('projectData.tabs.requests.data.0.management_requests.0.id', $invitation->id)
                ->where('projectData.tabs.requests.data.0.management_requests.0.management_context.direction', 'outgoing')
                ->where('projectData.tabs.requests.data.0.management_requests.0.management_context.waiting_for_response', true)
                ->where('projectData.tabs.requests.data.0.management_requests.2.id', $applicationA->id)
                ->where('projectData.tabs.requests.data.0.management_requests.2.management_context.direction', 'incoming')
                ->where('projectData.tabs.requests.data.0.management_requests.2.management_context.can_respond', true)
            );
    }

    public function test_rejected_requests_remain_visible_to_the_hiring_and_lending_sides_only(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$lendingManager, $lendingCompany] = $this->companyUser('Lending Company');
        [$unrelatedManager] = $this->companyUser('Unrelated Company');
        $project = $this->project($hiringManager, 'Rejected application project', 'open');
        $worker = $this->worker($lendingCompany, 'Declined worker');
        $request = $this->request($project, $lendingManager, $lendingCompany, $worker, 'apply', 'rejected');
        $request->update(['responded_at' => now()]);

        $this->actingAs($hiringManager)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('projectData.counts.requests', 1)
                ->has('projectData.tabs.requests.data', 1)
                ->where('projectData.tabs.requests.data.0.management_requests.0.id', $request->id)
                ->where('projectData.tabs.requests.data.0.management_requests.0.status', 'rejected')
                ->where('projectData.tabs.requests.data.0.management_requests.0.responded_at', $request->responded_at->toJSON())
                ->where('projectData.tabs.requests.data.0.management_requests.0.management_context.direction', 'incoming')
                ->where('projectData.tabs.requests.data.0.management_requests.0.management_context.can_respond', false)
            );

        $this->actingAs($lendingManager)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('projectData.counts.requests', 1)
                ->has('projectData.tabs.requests.data', 1)
                ->where('projectData.tabs.requests.data.0.management_requests.0.id', $request->id)
                ->where('projectData.tabs.requests.data.0.management_requests.0.status', 'rejected')
                ->where('projectData.tabs.requests.data.0.management_requests.0.management_context.direction', 'outgoing')
                ->where('projectData.tabs.requests.data.0.management_requests.0.management_context.can_respond', false)
            );

        $this->actingAs($unrelatedManager)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('projectData.counts.requests', 0)
                ->has('projectData.tabs.requests.data', 0)
            );
    }

    public function test_rejection_messages_are_preserved_without_overwriting_original_request_history(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$lendingManager, $lendingCompany] = $this->companyUser('Lending Company');
        $project = $this->project($hiringManager, 'Rejection message project', 'open');
        $worker = $this->worker($lendingCompany, 'Declined worker');
        $request = $this->request($project, $lendingManager, $lendingCompany, $worker, 'apply', 'pending');
        $request->update(['message' => 'Original application message.']);
        $rejectionMessage = 'Unfortunately, Declined worker has not been selected.';

        $this->actingAs($hiringManager)
            ->post(route('project-management.respond', $request), [
                'action' => 'reject',
                'message' => $rejectionMessage,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => 'rejected',
            'message' => 'Original application message.',
            'rejection_message' => $rejectionMessage,
        ]);

        $this->actingAs($hiringManager)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('projectData.tabs.requests.data.0.management_requests.0.rejection_message', $rejectionMessage)
                ->where('projectData.tabs.requests.data.0.management_requests.0.message', 'Original application message.')
            );

        $this->actingAs($lendingManager)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('projectData.tabs.requests.data.0.management_requests.0.rejection_message', $rejectionMessage)
                ->where('projectData.tabs.requests.data.0.management_requests.0.message', 'Original application message.')
            );
    }

    public function test_self_employed_users_keep_their_rejected_application_in_request_history(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        $selfEmployed = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'self_employed'])->id,
            'company_id' => null,
        ]);
        $worker = WorkerProfile::create([
            'user_id' => $selfEmployed->id,
            'company_id' => null,
            'name' => 'Independent applicant',
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);
        $project = $this->project($hiringManager, 'Independent rejected application', 'open');
        $request = $this->request($project, $selfEmployed, null, $worker, 'apply', 'rejected');
        $request->update(['responded_at' => now()]);

        $this->actingAs($selfEmployed)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('projectData.counts.requests', 1)
                ->has('projectData.tabs.requests.data', 1)
                ->where('projectData.tabs.requests.data.0.management_requests.0.id', $request->id)
                ->where('projectData.tabs.requests.data.0.management_requests.0.status', 'rejected')
                ->where('projectData.tabs.requests.data.0.management_requests.0.management_context.direction', 'outgoing')
                ->where('projectData.tabs.requests.data.0.management_requests.0.management_context.can_respond', false)
            );
    }

    public function test_accepted_requests_leave_request_history_for_staffing(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$lendingManager, $lendingCompany] = $this->companyUser('Lending Company');
        $project = $this->project($hiringManager, 'Accepted staffing project', 'open');
        $this->request($project, $lendingManager, $lendingCompany, $this->worker($lendingCompany, 'Accepted worker'), 'apply', 'accepted');

        $this->actingAs($hiringManager)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('projectData.counts.requests', 0)
                ->where('projectData.counts.staffing', 1)
                ->where('projectData.tabs.staffing.data.0.management_requests.0.status', 'accepted')
            );
    }

    public function test_external_projects_group_only_the_viewers_company_workers(): void
    {
        [$viewer, $viewerCompany] = $this->companyUser('Viewer Company');
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$otherManager, $otherCompany] = $this->companyUser('Other Company');
        $project = $this->project($hiringManager, 'External in progress', 'in_progress');

        $viewerWorkerA = $this->worker($viewerCompany, 'Viewer worker A');
        $viewerWorkerB = $this->worker($viewerCompany, 'Viewer worker B');
        $otherWorker = $this->worker($otherCompany, 'Other worker');

        $this->request($project, $viewer, $viewerCompany, $viewerWorkerA, 'apply', 'ongoing');
        $this->request($project, $viewer, $viewerCompany, $viewerWorkerB, 'apply', 'completed');
        $this->request($project, $otherManager, $otherCompany, $otherWorker, 'apply', 'ongoing');

        $this->actingAs($viewer)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('projectData.counts.in_progress', 1)
                ->has('projectData.tabs.in_progress.data', 1)
                ->where('projectData.tabs.in_progress.data.0.id', $project->id)
                ->where('projectData.tabs.in_progress.data.0.management_context.relationship', 'external_assignment')
                ->has('projectData.tabs.in_progress.data.0.management_requests', 2)
                ->where('projectData.tabs.in_progress.data.0.management_requests.0.worker.company_id', $viewerCompany->id)
                ->where('projectData.tabs.in_progress.data.0.management_requests.1.worker.company_id', $viewerCompany->id)
            );
    }

    public function test_staffing_contains_open_accepted_and_closed_recruiting_projects(): void
    {
        [$manager, $company] = $this->companyUser('Hiring Company');
        [$externalManager, $externalCompany] = $this->companyUser('External Company');
        $acceptedProject = $this->project($manager, 'Accepted staffing project', 'open');
        $closedProject = $this->project($manager, 'Closed staffing project', 'open');
        $externalAcceptedProject = $this->project($externalManager, 'External accepted staffing project', 'open');
        $closedProject->update(['recruiting_closed_at' => now()]);
        DB::table('projects')->where('id', $closedProject->id)->update(['updated_at' => now()->addMinute()]);
        DB::table('projects')->where('id', $externalAcceptedProject->id)->update(['updated_at' => now()->subMinute()]);

        $worker = $this->worker($externalCompany, 'Accepted worker');
        $viewerWorker = $this->worker($company, 'Viewer accepted worker');
        $this->request($acceptedProject, $externalManager, $externalCompany, $worker, 'apply', 'accepted');
        $this->request($externalAcceptedProject, $manager, $company, $viewerWorker, 'apply', 'accepted');

        $this->actingAs($manager)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('projectData.counts.staffing', 3)
                ->has('projectData.tabs.staffing.data', 3)
                ->where('projectData.tabs.staffing.data.0.id', $closedProject->id)
                ->where('projectData.tabs.staffing.data.0.management_context.can_stop_recruiting', false)
                ->where('projectData.tabs.staffing.data.1.id', $acceptedProject->id)
                ->where('projectData.tabs.staffing.data.1.management_context.recruiting_state', 'recruiting')
                ->where('projectData.tabs.staffing.data.2.id', $externalAcceptedProject->id)
                ->where('projectData.tabs.staffing.data.2.management_context.relationship', 'external_assignment')
            );
    }

    public function test_in_progress_keeps_resolved_worker_history_and_maps_ratings_to_the_matching_worker(): void
    {
        [$manager, $company] = $this->companyUser('Hiring Company');
        [$externalManager, $externalCompany] = $this->companyUser('External Company');
        $project = $this->project($manager, 'Active multi worker project', 'in_progress');

        $ongoingWorker = $this->worker($externalCompany, 'Ongoing worker');
        $completedWorker = $this->worker($externalCompany, 'Completed worker');
        $endedWorker = $this->worker($externalCompany, 'Ended worker');

        $ongoing = $this->request($project, $externalManager, $externalCompany, $ongoingWorker, 'apply', 'ongoing');
        $completed = $this->request($project, $externalManager, $externalCompany, $completedWorker, 'apply', 'completed');
        $endedEarly = $this->request($project, $externalManager, $externalCompany, $endedWorker, 'apply', 'ended_early');
        DB::table('requests')->where('id', $ongoing->id)->update(['created_at' => now()->subMinutes(3)]);
        DB::table('requests')->where('id', $completed->id)->update(['created_at' => now()->subMinutes(2)]);
        DB::table('requests')->where('id', $endedEarly->id)->update(['created_at' => now()->subMinute()]);

        Rating::create([
            'project_id' => $project->id,
            'reviewed_by_user_id' => $manager->id,
            'worker_profile_id' => $completedWorker->id,
            'score' => 5,
            'feedback' => 'Excellent work.',
        ]);
        Rating::create([
            'project_id' => $project->id,
            'reviewed_by_user_id' => $manager->id,
            'worker_profile_id' => $endedWorker->id,
            'score' => 3,
            'feedback' => 'Work ended early.',
        ]);

        $this->actingAs($manager)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('projectData.tabs.in_progress.data', 1)
                ->has('projectData.tabs.in_progress.data.0.management_requests', 3)
                ->where('projectData.tabs.in_progress.data.0.management_requests.0.id', $endedEarly->id)
                ->where('projectData.tabs.in_progress.data.0.management_requests.0.rating.worker_profile_id', $endedWorker->id)
                ->where('projectData.tabs.in_progress.data.0.management_requests.1.id', $completed->id)
                ->where('projectData.tabs.in_progress.data.0.management_requests.1.rating.worker_profile_id', $completedWorker->id)
                ->where('projectData.tabs.in_progress.data.0.management_requests.2.id', $ongoing->id)
            );
    }

    public function test_completed_tab_contains_only_completed_projects_and_paginates_projects_not_requests(): void
    {
        [$manager, $company] = $this->companyUser('Hiring Company');
        [$externalManager, $externalCompany] = $this->companyUser('External Company');

        foreach (range(1, 11) as $number) {
            $project = $this->project($manager, "Completed project {$number}", 'completed');
            $this->request($project, $externalManager, $externalCompany, $this->worker($externalCompany, "Worker {$number}A"), 'apply', 'completed');
            $this->request($project, $externalManager, $externalCompany, $this->worker($externalCompany, "Worker {$number}B"), 'apply', 'ended_early');
        }

        $inProgressProject = $this->project($manager, 'Still active project', 'in_progress');
        $this->request($inProgressProject, $externalManager, $externalCompany, $this->worker($externalCompany, 'Active completed worker'), 'apply', 'completed');

        $this->actingAs($manager)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('projectData.counts.completed', 11)
                ->where('projectData.tabs.completed.total', 11)
                ->where('projectData.tabs.completed.per_page', 10)
                ->has('projectData.tabs.completed.data', 10)
                ->has('projectData.tabs.completed.data.0.management_requests', 2)
            );
    }

    public function test_self_employed_users_receive_only_their_own_nested_rows(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$otherManager, $otherCompany] = $this->companyUser('Other Company');
        $selfEmployed = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'self_employed'])->id,
            'company_id' => null,
        ]);
        $selfWorker = WorkerProfile::create([
            'user_id' => $selfEmployed->id,
            'company_id' => null,
            'name' => 'Independent worker',
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);
        $project = $this->project($hiringManager, 'Independent assignment', 'in_progress');
        $this->request($project, $selfEmployed, null, $selfWorker, 'apply', 'ongoing');
        $this->request($project, $otherManager, $otherCompany, $this->worker($otherCompany, 'Other worker'), 'apply', 'ongoing');

        $this->actingAs($selfEmployed)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('projectData.counts.in_progress', 1)
                ->has('projectData.tabs.in_progress.data', 1)
                ->has('projectData.tabs.in_progress.data.0.management_requests', 1)
                ->where('projectData.tabs.in_progress.data.0.management_requests.0.worker_profile_id', $selfWorker->id)
            );
    }

    private function companyUser(string $companyName): array
    {
        $user = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'company_owner'])->id,
        ]);
        $company = Company::create(['name' => $companyName, 'owner_id' => $user->id]);
        $user->update(['company_id' => $company->id]);

        return [$user->fresh(), $company];
    }

    private function project(User $owner, string $title, string $status): Project
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
            'status' => $status,
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
