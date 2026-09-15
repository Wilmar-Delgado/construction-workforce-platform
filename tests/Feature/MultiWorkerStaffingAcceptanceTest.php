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

class MultiWorkerStaffingAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_and_second_acceptances_leave_a_three_worker_mission_open(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->staffingFixture(3);
        $first = $this->application($mission, $externalManager, 'Avery Chen');
        $second = $this->application($mission, $externalManager, 'Blair Patel');

        $this->accept($hiringManager, $first)->assertSessionHasNoErrors();

        $mission->refresh();
        $this->assertSame('open', $mission->status);
        $this->assertNull($mission->recruiting_closed_at);
        $this->assertSame(1, $mission->committed_worker_count);

        $this->accept($hiringManager, $second)->assertSessionHasNoErrors();

        $mission->refresh();
        $this->assertSame('open', $mission->status);
        $this->assertNull($mission->recruiting_closed_at);
        $this->assertSame(2, $mission->committed_worker_count);
    }

    public function test_third_acceptance_closes_recruiting_without_starting_the_mission(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->staffingFixture(3);
        $requests = collect(['Avery Chen', 'Blair Patel', 'Casey Martin'])
            ->map(fn (string $name) => $this->application($mission, $externalManager, $name));

        $requests->each(fn (WorkerRequest $workerRequest) => $this->accept($hiringManager, $workerRequest));

        $mission->refresh();
        $this->assertSame('open', $mission->status);
        $this->assertNotNull($mission->recruiting_closed_at);
        $this->assertSame(3, $mission->committed_worker_count);
        $this->assertSame(0, $mission->remaining_capacity);
    }

    public function test_filling_the_final_slot_cancels_pending_requests_and_blocks_later_acceptance(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->staffingFixture(1);
        $accepted = $this->application($mission, $externalManager, 'Avery Chen');
        $cancelled = $this->application($mission, $externalManager, 'Blair Patel');

        $this->accept($hiringManager, $accepted)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('requests', ['id' => $cancelled->id, 'status' => 'cancelled']);
        $this->actingAs($hiringManager)
            ->post(route('mission-management.respond', $cancelled), ['action' => 'accept'])
            ->assertForbidden();
    }

    public function test_rejected_requests_do_not_consume_capacity(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->staffingFixture(1);
        $rejected = $this->application($mission, $externalManager, 'Avery Chen');
        $accepted = $this->application($mission, $externalManager, 'Blair Patel');

        $this->actingAs($hiringManager)
            ->post(route('mission-management.respond', $rejected), ['action' => 'reject'])
            ->assertSessionHasNoErrors();

        $this->accept($hiringManager, $accepted)->assertSessionHasNoErrors();

        $mission->refresh();
        $this->assertSame(1, $mission->committed_worker_count);
        $this->assertNotNull($mission->recruiting_closed_at);
    }

    public function test_invitation_acceptance_uses_the_same_capacity_rules(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->staffingFixture(2);
        $first = $this->invitation($mission, $hiringManager, $externalManager, 'Avery Chen');
        $second = $this->invitation($mission, $hiringManager, $externalManager, 'Blair Patel');

        $this->accept($externalManager, $first)->assertSessionHasNoErrors();
        $mission->refresh();
        $this->assertNull($mission->recruiting_closed_at);

        $this->accept($externalManager, $second)->assertSessionHasNoErrors();
        $this->assertNotNull($mission->fresh()->recruiting_closed_at);
    }

    public function test_manual_stop_recruiting_closes_a_partially_staffed_mission_and_cancels_pending_requests(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->staffingFixture(3);
        $accepted = $this->application($mission, $externalManager, 'Avery Chen');
        $pending = $this->application($mission, $externalManager, 'Blair Patel');

        $this->accept($hiringManager, $accepted);

        $this->actingAs($hiringManager)
            ->post(route('mission-management.close-recruiting', $mission))
            ->assertSessionHasNoErrors();

        $mission->refresh();
        $this->assertSame('open', $mission->status);
        $this->assertNotNull($mission->recruiting_closed_at);
        $this->assertDatabaseHas('requests', ['id' => $pending->id, 'status' => 'cancelled']);
    }

    public function test_manual_stop_recruiting_requires_a_committed_worker(): void
    {
        [$mission, $hiringManager] = $this->staffingFixture(2);

        $this->actingAs($hiringManager)
            ->from(route('mission-management.index'))
            ->post(route('mission-management.close-recruiting', $mission))
            ->assertSessionHasErrors('mission');

        $this->assertNull($mission->fresh()->recruiting_closed_at);
    }

    public function test_closed_recruiting_blocks_new_applications_and_invitations(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->staffingFixture(2);
        $worker = $this->externalWorker($externalManager, 'Avery Chen');
        $mission->update(['recruiting_closed_at' => now()]);

        $this->actingAs($externalManager)
            ->post(route('request-mission.store', $mission), ['worker_profile_id' => $worker->id])
            ->assertForbidden();

        $this->actingAs($hiringManager)
            ->post(route('request-worker.store', $worker), ['mission_id' => $mission->id])
            ->assertForbidden();
    }

    public function test_administrator_cannot_bypass_closed_recruiting_lifecycle_checks(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->staffingFixture(1);
        $accepted = $this->application($mission, $externalManager, 'Avery Chen');
        $cancelled = $this->application($mission, $externalManager, 'Blair Patel');
        $administratorRole = Role::firstOrCreate(['name' => 'administrator']);
        $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

        $this->accept($hiringManager, $accepted);

        $this->actingAs($administrator)
            ->from(route('mission-management.index'))
            ->post(route('mission-management.respond', $cancelled), ['action' => 'accept'])
            ->assertSessionHasErrors('action');

        $this->assertDatabaseHas('requests', ['id' => $cancelled->id, 'status' => 'cancelled']);
    }

    private function accept(User $user, WorkerRequest $workerRequest)
    {
        return $this->actingAs($user)
            ->post(route('mission-management.respond', $workerRequest), ['action' => 'accept']);
    }

    private function staffingFixture(int $capacity): array
    {
        $companyOwner = Role::firstOrCreate(['name' => 'company_owner']);
        $hiringManager = $this->companyManager($companyOwner, 'Hiring Company Ltd.');
        $externalManager = $this->companyManager($companyOwner, 'External Trades Ltd.');

        $mission = Mission::create([
            'hiring_company_id' => $hiringManager->company_id,
            'created_by' => $hiringManager->id,
            'title' => 'Multi-worker staffing mission',
            'city' => 'Calgary',
            'province' => 'Alberta',
            'job_type' => 'Electrician',
            'workers' => $capacity,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
            'status' => 'open',
        ]);

        return [$mission, $hiringManager, $externalManager];
    }

    private function companyManager(Role $role, string $companyName): User
    {
        $manager = User::factory()->create(['role_id' => $role->id]);
        $company = Company::create(['name' => $companyName, 'owner_id' => $manager->id]);
        $manager->update(['company_id' => $company->id]);

        return $manager->fresh();
    }

    private function externalWorker(User $externalManager, string $name): WorkerProfile
    {
        return WorkerProfile::create([
            'company_id' => $externalManager->company_id,
            'name' => $name,
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);
    }

    private function application(Mission $mission, User $externalManager, string $name): WorkerRequest
    {
        $worker = $this->externalWorker($externalManager, $name);

        return WorkerRequest::create([
            'mission_id' => $mission->id,
            'requested_by' => $externalManager->id,
            'company_id' => $externalManager->company_id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => 'pending',
        ]);
    }

    private function invitation(Mission $mission, User $hiringManager, User $externalManager, string $name): WorkerRequest
    {
        $worker = $this->externalWorker($externalManager, $name);

        return WorkerRequest::create([
            'mission_id' => $mission->id,
            'requested_by' => $hiringManager->id,
            'company_id' => $hiringManager->company_id,
            'worker_profile_id' => $worker->id,
            'type' => 'invite',
            'status' => 'pending',
        ]);
    }
}
