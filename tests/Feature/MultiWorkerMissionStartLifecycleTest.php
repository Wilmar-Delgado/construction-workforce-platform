<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Mission;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiWorkerMissionStartLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_closed_mission_with_accepted_workers_can_start_on_its_start_date(): void
    {
        [$mission, $manager, $externalManager] = $this->fixture(today());
        $accepted = $this->request($mission, $externalManager, 'accepted');

        $this->actingAs($manager)
            ->post(route('mission-management.start', $mission))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('missions', ['id' => $mission->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('requests', ['id' => $accepted->id, 'status' => 'ongoing']);
    }

    public function test_a_closed_mission_with_accepted_workers_can_start_after_its_start_date(): void
    {
        [$mission, $manager, $externalManager] = $this->fixture(today()->subDay());
        $accepted = $this->request($mission, $externalManager, 'accepted');

        $this->actingAs($manager)
            ->post(route('mission-management.start', $mission))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('missions', ['id' => $mission->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('requests', ['id' => $accepted->id, 'status' => 'ongoing']);
    }

    public function test_a_mission_cannot_start_before_its_start_date(): void
    {
        [$mission, $manager, $externalManager] = $this->fixture(today()->addDay());
        $accepted = $this->request($mission, $externalManager, 'accepted');

        $this->actingAs($manager)
            ->from(route('mission-management.index'))
            ->post(route('mission-management.start', $mission))
            ->assertSessionHasErrors('mission');

        $this->assertDatabaseHas('missions', ['id' => $mission->id, 'status' => 'open']);
        $this->assertDatabaseHas('requests', ['id' => $accepted->id, 'status' => 'accepted']);
    }

    public function test_a_mission_cannot_start_without_a_committed_worker(): void
    {
        [$mission, $manager] = $this->fixture(today());

        $this->actingAs($manager)
            ->from(route('mission-management.index'))
            ->post(route('mission-management.start', $mission))
            ->assertSessionHasErrors('mission');

        $this->assertSame('open', $mission->fresh()->status);
    }

    public function test_a_mission_cannot_start_while_recruiting_is_open(): void
    {
        [$mission, $manager, $externalManager] = $this->fixture(today(), false);
        $accepted = $this->request($mission, $externalManager, 'accepted');

        $this->actingAs($manager)
            ->from(route('mission-management.index'))
            ->post(route('mission-management.start', $mission))
            ->assertSessionHasErrors('mission');

        $this->assertDatabaseHas('missions', ['id' => $mission->id, 'status' => 'open']);
        $this->assertDatabaseHas('requests', ['id' => $accepted->id, 'status' => 'accepted']);
    }

    public function test_start_moves_all_accepted_assignments_to_ongoing_without_changing_existing_ongoing_or_completed_assignments(): void
    {
        [$mission, $manager, $externalManager] = $this->fixture(today());
        $firstAccepted = $this->request($mission, $externalManager, 'accepted');
        $secondAccepted = $this->request($mission, $externalManager, 'accepted');
        $ongoing = $this->request($mission, $externalManager, 'ongoing');
        $completed = $this->request($mission, $externalManager, 'completed');

        $this->actingAs($manager)
            ->post(route('mission-management.start', $mission))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('requests', ['id' => $firstAccepted->id, 'status' => 'ongoing']);
        $this->assertDatabaseHas('requests', ['id' => $secondAccepted->id, 'status' => 'ongoing']);
        $this->assertDatabaseHas('requests', ['id' => $ongoing->id, 'status' => 'ongoing']);
        $this->assertDatabaseHas('requests', ['id' => $completed->id, 'status' => 'completed']);
    }

    public function test_the_automatic_lifecycle_closes_recruiting_cancels_stale_pending_requests_and_starts_due_missions_with_accepted_workers(): void
    {
        [$mission, , $externalManager] = $this->fixture(today(), false);
        $accepted = $this->request($mission, $externalManager, 'accepted');
        $pending = $this->request($mission, $externalManager, 'pending');

        $this->artisan('missions:process-start-dates')->assertExitCode(0);

        $mission->refresh();
        $this->assertSame('in_progress', $mission->status);
        $this->assertNotNull($mission->recruiting_closed_at);
        $this->assertDatabaseHas('requests', ['id' => $accepted->id, 'status' => 'ongoing']);
        $this->assertDatabaseHas('requests', ['id' => $pending->id, 'status' => 'cancelled']);
    }

    public function test_the_automatic_lifecycle_closes_due_recruiting_without_auto_cancelling_a_mission_with_zero_accepted_workers(): void
    {
        [$mission, , $externalManager] = $this->fixture(today(), false);
        $pending = $this->request($mission, $externalManager, 'pending');

        $this->artisan('missions:process-start-dates')->assertExitCode(0);

        $mission->refresh();
        $this->assertSame('open', $mission->status);
        $this->assertNotNull($mission->recruiting_closed_at);
        $this->assertDatabaseHas('requests', ['id' => $pending->id, 'status' => 'cancelled']);
    }

    public function test_the_automatic_lifecycle_is_idempotent(): void
    {
        [$mission, , $externalManager] = $this->fixture(today(), false);
        $accepted = $this->request($mission, $externalManager, 'accepted');

        $this->artisan('missions:process-start-dates')->assertExitCode(0);
        $firstClosedAt = $mission->fresh()->recruiting_closed_at;

        $this->artisan('missions:process-start-dates')->assertExitCode(0);

        $mission->refresh();
        $this->assertSame('in_progress', $mission->status);
        $this->assertTrue($firstClosedAt->equalTo($mission->recruiting_closed_at));
        $this->assertDatabaseHas('requests', ['id' => $accepted->id, 'status' => 'ongoing']);
    }

    public function test_the_automatic_lifecycle_ignores_completed_cancelled_and_in_progress_missions(): void
    {
        foreach (['completed', 'cancelled', 'in_progress'] as $status) {
            [$mission, , $externalManager] = $this->fixture(today()->subDay(), false, $status);
            $pending = $this->request($mission, $externalManager, 'pending');

            $this->artisan('missions:process-start-dates')->assertExitCode(0);

            $this->assertDatabaseHas('missions', ['id' => $mission->id, 'status' => $status, 'recruiting_closed_at' => null]);
            $this->assertDatabaseHas('requests', ['id' => $pending->id, 'status' => 'pending']);
        }
    }

    private function fixture($startDate, bool $recruitingClosed = true, string $status = 'open'): array
    {
        $companyOwner = Role::firstOrCreate(['name' => 'company_owner']);
        $manager = $this->companyManager($companyOwner, 'Hiring Company Ltd.');
        $externalManager = $this->companyManager($companyOwner, 'External Trades Ltd.');

        $mission = Mission::create([
            'hiring_company_id' => $manager->company_id,
            'created_by' => $manager->id,
            'title' => 'Mission start lifecycle test',
            'city' => 'Calgary',
            'province' => 'Alberta',
            'job_type' => 'Electrician',
            'workers' => 3,
            'start_date' => $startDate->toDateString(),
            'end_date' => $startDate->copy()->addWeek()->toDateString(),
            'status' => $status,
            'recruiting_closed_at' => $recruitingClosed ? now() : null,
        ]);

        return [$mission, $manager, $externalManager];
    }

    private function companyManager(Role $role, string $companyName): User
    {
        $manager = User::factory()->create(['role_id' => $role->id]);
        $company = Company::create(['name' => $companyName, 'owner_id' => $manager->id]);
        $manager->update(['company_id' => $company->id]);

        return $manager->fresh();
    }

    private function request(Mission $mission, User $externalManager, string $status): WorkerRequest
    {
        $worker = WorkerProfile::create([
            'company_id' => $externalManager->company_id,
            'name' => "Worker {$status} ".WorkerProfile::query()->count(),
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);

        return WorkerRequest::create([
            'mission_id' => $mission->id,
            'requested_by' => $externalManager->id,
            'company_id' => $externalManager->company_id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => $status,
        ]);
    }
}
