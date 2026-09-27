<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiWorkerProjectStartLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_closed_project_with_accepted_workers_can_start_on_its_start_date(): void
    {
        [$project, $manager, $externalManager] = $this->fixture(today());
        $accepted = $this->request($project, $externalManager, 'accepted');

        $this->actingAs($manager)
            ->post(route('project-management.start', $project))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('requests', ['id' => $accepted->id, 'status' => 'ongoing']);
    }

    public function test_a_closed_project_with_accepted_workers_can_start_after_its_start_date(): void
    {
        [$project, $manager, $externalManager] = $this->fixture(today()->subDay());
        $accepted = $this->request($project, $externalManager, 'accepted');

        $this->actingAs($manager)
            ->post(route('project-management.start', $project))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('requests', ['id' => $accepted->id, 'status' => 'ongoing']);
    }

    public function test_a_project_cannot_start_before_its_start_date(): void
    {
        [$project, $manager, $externalManager] = $this->fixture(today()->addDay());
        $accepted = $this->request($project, $externalManager, 'accepted');

        $this->actingAs($manager)
            ->from(route('project-management.index'))
            ->post(route('project-management.start', $project))
            ->assertSessionHasErrors('project');

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'open']);
        $this->assertDatabaseHas('requests', ['id' => $accepted->id, 'status' => 'accepted']);
    }

    public function test_a_project_cannot_start_without_a_committed_worker(): void
    {
        [$project, $manager] = $this->fixture(today());

        $this->actingAs($manager)
            ->from(route('project-management.index'))
            ->post(route('project-management.start', $project))
            ->assertSessionHasErrors('project');

        $this->assertSame('open', $project->fresh()->status);
    }

    public function test_a_project_cannot_start_while_recruiting_is_open(): void
    {
        [$project, $manager, $externalManager] = $this->fixture(today(), false);
        $accepted = $this->request($project, $externalManager, 'accepted');

        $this->actingAs($manager)
            ->from(route('project-management.index'))
            ->post(route('project-management.start', $project))
            ->assertSessionHasErrors('project');

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'open']);
        $this->assertDatabaseHas('requests', ['id' => $accepted->id, 'status' => 'accepted']);
    }

    public function test_start_moves_all_accepted_assignments_to_ongoing_without_changing_existing_ongoing_or_completed_assignments(): void
    {
        [$project, $manager, $externalManager] = $this->fixture(today());
        $firstAccepted = $this->request($project, $externalManager, 'accepted');
        $secondAccepted = $this->request($project, $externalManager, 'accepted');
        $ongoing = $this->request($project, $externalManager, 'ongoing');
        $completed = $this->request($project, $externalManager, 'completed');

        $this->actingAs($manager)
            ->post(route('project-management.start', $project))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('requests', ['id' => $firstAccepted->id, 'status' => 'ongoing']);
        $this->assertDatabaseHas('requests', ['id' => $secondAccepted->id, 'status' => 'ongoing']);
        $this->assertDatabaseHas('requests', ['id' => $ongoing->id, 'status' => 'ongoing']);
        $this->assertDatabaseHas('requests', ['id' => $completed->id, 'status' => 'completed']);
    }

    public function test_the_automatic_lifecycle_closes_recruiting_cancels_stale_pending_requests_and_starts_due_projects_with_accepted_workers(): void
    {
        [$project, , $externalManager] = $this->fixture(today(), false);
        $accepted = $this->request($project, $externalManager, 'accepted');
        $pending = $this->request($project, $externalManager, 'pending');

        $this->artisan('projects:process-start-dates')->assertExitCode(0);

        $project->refresh();
        $this->assertSame('in_progress', $project->status);
        $this->assertNotNull($project->recruiting_closed_at);
        $this->assertDatabaseHas('requests', ['id' => $accepted->id, 'status' => 'ongoing']);
        $this->assertDatabaseHas('requests', ['id' => $pending->id, 'status' => 'cancelled']);
    }

    public function test_the_automatic_lifecycle_closes_due_recruiting_without_auto_cancelling_a_project_with_zero_accepted_workers(): void
    {
        [$project, , $externalManager] = $this->fixture(today(), false);
        $pending = $this->request($project, $externalManager, 'pending');

        $this->artisan('projects:process-start-dates')->assertExitCode(0);

        $project->refresh();
        $this->assertSame('open', $project->status);
        $this->assertNotNull($project->recruiting_closed_at);
        $this->assertDatabaseHas('requests', ['id' => $pending->id, 'status' => 'cancelled']);
    }

    public function test_the_automatic_lifecycle_is_idempotent(): void
    {
        [$project, , $externalManager] = $this->fixture(today(), false);
        $accepted = $this->request($project, $externalManager, 'accepted');

        $this->artisan('projects:process-start-dates')->assertExitCode(0);
        $firstClosedAt = $project->fresh()->recruiting_closed_at;

        $this->artisan('projects:process-start-dates')->assertExitCode(0);

        $project->refresh();
        $this->assertSame('in_progress', $project->status);
        $this->assertTrue($firstClosedAt->equalTo($project->recruiting_closed_at));
        $this->assertDatabaseHas('requests', ['id' => $accepted->id, 'status' => 'ongoing']);
    }

    public function test_the_automatic_lifecycle_ignores_completed_cancelled_and_in_progress_projects(): void
    {
        foreach (['completed', 'cancelled', 'in_progress'] as $status) {
            [$project, , $externalManager] = $this->fixture(today()->subDay(), false, $status);
            $pending = $this->request($project, $externalManager, 'pending');

            $this->artisan('projects:process-start-dates')->assertExitCode(0);

            $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => $status, 'recruiting_closed_at' => null]);
            $this->assertDatabaseHas('requests', ['id' => $pending->id, 'status' => 'pending']);
        }
    }

    private function fixture($startDate, bool $recruitingClosed = true, string $status = 'open'): array
    {
        $companyOwner = Role::firstOrCreate(['name' => 'company_owner']);
        $manager = $this->companyManager($companyOwner, 'Hiring Company Ltd.');
        $externalManager = $this->companyManager($companyOwner, 'External Trades Ltd.');

        $project = Project::create([
            'hiring_company_id' => $manager->company_id,
            'created_by' => $manager->id,
            'title' => 'Project start lifecycle test',
            'city' => 'Calgary',
            'province' => 'Alberta',
            'job_type' => 'Electrician',
            'workers' => 3,
            'start_date' => $startDate->toDateString(),
            'end_date' => $startDate->copy()->addWeek()->toDateString(),
            'status' => $status,
            'recruiting_closed_at' => $recruitingClosed ? now() : null,
        ]);

        return [$project, $manager, $externalManager];
    }

    private function companyManager(Role $role, string $companyName): User
    {
        $manager = User::factory()->create(['role_id' => $role->id]);
        $company = Company::create(['name' => $companyName, 'owner_id' => $manager->id]);
        $manager->update(['company_id' => $company->id]);

        return $manager->fresh();
    }

    private function request(Project $project, User $externalManager, string $status): WorkerRequest
    {
        $worker = WorkerProfile::create([
            'company_id' => $externalManager->company_id,
            'name' => "Worker {$status} ".WorkerProfile::query()->count(),
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);

        return WorkerRequest::create([
            'project_id' => $project->id,
            'requested_by' => $externalManager->id,
            'company_id' => $externalManager->company_id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => $status,
        ]);
    }
}
