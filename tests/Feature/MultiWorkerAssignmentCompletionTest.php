<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Mission;
use App\Models\Rating;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MultiWorkerAssignmentCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_one_worker_preserves_other_assignments_and_keeps_the_mission_in_progress(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->fixture();
        $first = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'apply');
        $second = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'invite');
        $third = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'apply');

        $this->complete($hiringManager, $first, 5)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('requests', ['id' => $first->id, 'status' => 'completed']);
        $this->assertNotNull($first->fresh()->completed_at);
        $this->assertDatabaseHas('requests', ['id' => $second->id, 'status' => 'ongoing']);
        $this->assertDatabaseHas('requests', ['id' => $third->id, 'status' => 'ongoing']);
        $this->assertDatabaseHas('missions', ['id' => $mission->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('ratings', [
            'mission_id' => $mission->id,
            'worker_profile_id' => $first->worker_profile_id,
            'score' => 5,
        ]);
    }

    public function test_second_to_last_completion_keeps_the_mission_in_progress_and_final_completion_completes_it(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->fixture();
        $first = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'apply');
        $second = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'invite');
        $third = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'apply');

        $this->complete($hiringManager, $first, 5)->assertSessionHasNoErrors();
        $this->complete($hiringManager, $second, 4)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('missions', ['id' => $mission->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('requests', ['id' => $third->id, 'status' => 'ongoing']);

        $this->complete($hiringManager, $third, 3)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('missions', ['id' => $mission->id, 'status' => 'completed']);
        $this->assertDatabaseCount('ratings', 3);
        $this->assertDatabaseHas('requests', ['id' => $first->id, 'status' => 'completed']);
        $this->assertDatabaseHas('requests', ['id' => $second->id, 'status' => 'completed']);
        $this->assertDatabaseHas('requests', ['id' => $third->id, 'status' => 'completed']);
    }

    public function test_three_workers_can_each_receive_one_rating_for_the_same_mission(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->fixture();
        $assignments = collect([
            $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'apply'),
            $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'invite'),
            $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'apply'),
        ]);

        $assignments->each(function (WorkerRequest $assignment, int $index) use ($hiringManager): void {
            $this->complete($hiringManager, $assignment, $index + 3)->assertSessionHasNoErrors();
        });

        $this->assertSame(3, Rating::where('mission_id', $mission->id)->count());
        $this->assertSame(3, Rating::where('mission_id', $mission->id)->distinct('worker_profile_id')->count('worker_profile_id'));
    }

    public function test_a_duplicate_rating_for_the_same_worker_is_rejected_without_completing_the_assignment(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->fixture();
        $assignment = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'apply');

        Rating::create([
            'mission_id' => $mission->id,
            'reviewed_by_user_id' => $hiringManager->id,
            'worker_profile_id' => $assignment->worker_profile_id,
            'score' => 4,
        ]);

        $this->actingAs($hiringManager)
            ->from(route('mission-management.index'))
            ->post(route('mission-management.complete', $assignment), ['rating' => 5])
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseHas('requests', ['id' => $assignment->id, 'status' => 'ongoing']);
        $this->assertNull($assignment->fresh()->completed_at);
        $this->assertDatabaseHas('missions', ['id' => $mission->id, 'status' => 'in_progress']);
        $this->assertDatabaseCount('ratings', 1);
    }

    public function test_completion_transaction_rolls_back_when_rating_creation_fails(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->fixture();
        $assignment = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'apply');

        Rating::created(function (): void {
            throw new \RuntimeException('Rating persistence failed.');
        });

        try {
            $this->withoutExceptionHandling();
            $this->complete($hiringManager, $assignment, 5);
            $this->fail('The simulated rating failure was not thrown.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Rating persistence failed.', $exception->getMessage());
        } finally {
            Rating::flushEventListeners();
            Rating::setEventDispatcher(app('events'));
        }

        $this->assertDatabaseCount('ratings', 0);
        $this->assertDatabaseHas('requests', ['id' => $assignment->id, 'status' => 'ongoing']);
        $this->assertNull($assignment->fresh()->completed_at);
        $this->assertDatabaseHas('missions', ['id' => $mission->id, 'status' => 'in_progress']);
    }

    public function test_an_unauthorized_company_cannot_complete_or_rate_an_assignment(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->fixture();
        $assignment = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'apply');
        [, $otherManager] = $this->fixture();

        $this->actingAs($otherManager)
            ->post(route('mission-management.complete', $assignment), ['rating' => 5])
            ->assertForbidden();

        $this->assertDatabaseHas('requests', ['id' => $assignment->id, 'status' => 'ongoing']);
        $this->assertDatabaseCount('ratings', 0);
    }

    public function test_invalid_and_repeated_assignment_states_cannot_be_completed(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->fixture();
        $pending = $this->assignment($mission, $hiringManager, $externalManager, 'pending', 'apply');
        $ongoing = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'invite');

        $this->actingAs($hiringManager)
            ->post(route('mission-management.complete', $pending), ['rating' => 5])
            ->assertForbidden();

        $this->complete($hiringManager, $ongoing, 5)->assertSessionHasNoErrors();

        $this->actingAs($hiringManager)
            ->post(route('mission-management.complete', $ongoing), ['rating' => 4])
            ->assertForbidden();

        $this->assertDatabaseCount('ratings', 1);
    }

    public function test_normal_completion_is_rejected_before_the_mission_end_date(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->fixture(today()->addDay());
        $assignment = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'apply');

        $this->actingAs($hiringManager)
            ->from(route('mission-management.index'))
            ->post(route('mission-management.complete', $assignment), ['rating' => 4])
            ->assertSessionHasErrors('assignment');

        $this->assertDatabaseHas('requests', ['id' => $assignment->id, 'status' => 'ongoing']);
        $this->assertDatabaseCount('ratings', 0);
    }

    public function test_a_worker_can_end_early_and_receive_an_independent_rating_without_completing_the_mission(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->fixture(today()->addWeek());
        $early = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'apply');
        $ongoing = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'invite');

        $this->actingAs($hiringManager)
            ->post(route('mission-management.end-early', $early), ['rating' => 2, 'comment' => 'Left the project early.'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('requests', ['id' => $early->id, 'status' => 'ended_early']);
        $this->assertNotNull($early->fresh()->ended_at);
        $this->assertDatabaseHas('requests', ['id' => $ongoing->id, 'status' => 'ongoing']);
        $this->assertDatabaseHas('missions', ['id' => $mission->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('ratings', [
            'mission_id' => $mission->id,
            'worker_profile_id' => $early->worker_profile_id,
            'score' => 2,
        ]);
    }

    public function test_an_early_ended_assignment_does_not_prevent_final_normal_completion(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->fixture();
        $early = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'apply');
        $final = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'invite');

        $this->actingAs($hiringManager)
            ->post(route('mission-management.end-early', $early), ['rating' => 3])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('requests', ['id' => $early->id, 'status' => 'ended_early']);
        $this->assertDatabaseHas('requests', ['id' => $final->id, 'status' => 'ongoing']);

        $this->complete($hiringManager, $final, 5)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('requests', ['id' => $early->id, 'status' => 'ended_early']);
        $this->assertDatabaseHas('requests', ['id' => $final->id, 'status' => 'completed']);
        $this->assertDatabaseHas('missions', ['id' => $mission->id, 'status' => 'completed']);
        $this->assertDatabaseCount('ratings', 2);
    }

    public function test_completed_tab_exposes_an_ended_early_assignment_without_claiming_the_mission_is_completed(): void
    {
        [$mission, $hiringManager, $externalManager] = $this->fixture(today()->addWeek());
        $early = $this->assignment($mission, $hiringManager, $externalManager, 'ongoing', 'apply');

        $this->actingAs($hiringManager)
            ->post(route('mission-management.end-early', $early), ['rating' => 3])
            ->assertSessionHasNoErrors();

        $this->actingAs($hiringManager)
            ->get(route('mission-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('MissionManagement')
                ->has('data.completed.created.data', 1)
                ->where('data.completed.created.data.0.id', $early->id)
                ->where('data.completed.created.data.0.status', 'ended_early')
                ->where('data.completed.created.data.0.mission.status', 'in_progress')
            );
    }

    private function complete(User $manager, WorkerRequest $assignment, int $rating)
    {
        return $this->actingAs($manager)
            ->post(route('mission-management.complete', $assignment), [
                'rating' => $rating,
                'comment' => 'Assignment review.',
            ]);
    }

    private function fixture($endDate = null): array
    {
        $role = Role::firstOrCreate(['name' => 'company_owner']);
        $hiringManager = $this->companyManager($role, 'Hiring Company Ltd.');
        $externalManager = $this->companyManager($role, 'External Trades Ltd.');

        $mission = Mission::create([
            'hiring_company_id' => $hiringManager->company_id,
            'created_by' => $hiringManager->id,
            'title' => 'Multi-worker completion mission',
            'city' => 'Calgary',
            'province' => 'Alberta',
            'job_type' => 'Electrician',
            'workers' => 3,
            'start_date' => today()->subDay()->toDateString(),
            'end_date' => ($endDate ?? today())->toDateString(),
            'status' => 'in_progress',
            'recruiting_closed_at' => now(),
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

    private function assignment(
        Mission $mission,
        User $hiringManager,
        User $externalManager,
        string $status,
        string $type,
    ): WorkerRequest {
        $worker = WorkerProfile::create([
            'company_id' => $externalManager->company_id,
            'name' => 'Worker '.WorkerProfile::query()->count(),
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);

        return WorkerRequest::create([
            'mission_id' => $mission->id,
            'requested_by' => $type === 'invite' ? $hiringManager->id : $externalManager->id,
            'company_id' => $type === 'invite' ? $hiringManager->company_id : $externalManager->company_id,
            'worker_profile_id' => $worker->id,
            'type' => $type,
            'status' => $status,
        ]);
    }
}
