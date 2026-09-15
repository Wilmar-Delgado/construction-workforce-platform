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

class MissionLifecycleHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_creation_cannot_set_a_lifecycle_owned_status(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)
            ->from(route('missions.index'))
            ->post(route('missions.store'), $this->missionData(['status' => 'in_progress']))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('missions', 0);
    }

    public function test_standard_update_cannot_set_in_progress_or_completed(): void
    {
        $manager = $this->manager();
        $mission = $this->mission($manager, ['status' => 'open']);

        foreach (['in_progress', 'completed'] as $status) {
            $this->actingAs($manager)
                ->from(route('missions.index'))
                ->put(route('missions.update', $mission), $this->missionData(['status' => $status]))
                ->assertSessionHasErrors('status');

            $this->assertSame('open', $mission->fresh()->status);
        }
    }

    public function test_completed_and_cancelled_missions_cannot_be_reopened_through_standard_editing(): void
    {
        $manager = $this->manager();

        foreach (['completed', 'cancelled'] as $status) {
            $mission = $this->mission($manager, ['status' => $status]);

            $this->actingAs($manager)
                ->from(route('missions.index'))
                ->put(route('missions.update', $mission), $this->missionData(['status' => 'open']))
                ->assertSessionHasErrors('status');

            $this->assertSame($status, $mission->fresh()->status);
        }
    }

    public function test_capacity_cannot_be_reduced_below_committed_assignment_history(): void
    {
        $manager = $this->manager();
        $mission = $this->mission($manager, ['workers' => 2, 'status' => 'open']);
        foreach (['Committed Worker One', 'Committed Worker Two'] as $name) {
            $worker = WorkerProfile::create([
                'company_id' => $manager->company_id,
                'name' => $name,
                'job' => 'Electrician',
                'years_experience' => 5,
                'hourly_rate' => 40,
            ]);

            WorkerRequest::create([
                'mission_id' => $mission->id,
                'requested_by' => $manager->id,
                'company_id' => $manager->company_id,
                'worker_profile_id' => $worker->id,
                'type' => 'apply',
                'status' => 'accepted',
            ]);
        }

        $this->actingAs($manager)
            ->from(route('missions.index'))
            ->put(route('missions.update', $mission), $this->missionData(['workers' => 1, 'status' => 'open']))
            ->assertSessionHasErrors('workers');

        $this->assertSame(2, $mission->fresh()->workers);
    }

    private function manager(): User
    {
        $role = Role::firstOrCreate(['name' => 'company_owner']);
        $manager = User::factory()->create(['role_id' => $role->id]);
        $company = Company::create(['name' => 'Lifecycle Construction Ltd.', 'owner_id' => $manager->id]);
        $manager->update(['company_id' => $company->id]);

        return $manager->fresh();
    }

    private function mission(User $manager, array $overrides = []): Mission
    {
        return Mission::create([
            'hiring_company_id' => $manager->company_id,
            'created_by' => $manager->id,
            ...$this->missionData(),
            ...$overrides,
        ]);
    }

    private function missionData(array $overrides = []): array
    {
        return [
            'title' => 'Lifecycle hardening mission',
            'description' => 'Mission lifecycle validation coverage.',
            'start_date' => today()->addWeek()->toDateString(),
            'end_date' => today()->addWeeks(2)->toDateString(),
            'city' => 'Calgary',
            'province' => 'Alberta',
            'country' => 'Canada',
            'job_type' => 'Electrician',
            'workers' => 2,
            'hourly_rate' => 40,
            'status' => 'draft',
            ...$overrides,
        ];
    }
}
