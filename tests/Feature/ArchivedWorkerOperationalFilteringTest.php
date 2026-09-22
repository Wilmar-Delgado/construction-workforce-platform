<?php

namespace Tests\Feature;

use App\Models\Availability;
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

class ArchivedWorkerOperationalFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_archived_workers_are_excluded_from_worker_discovery_and_job_filters(): void
    {
        [$viewer] = $this->companyUser('Viewer Company');
        [, $externalCompany] = $this->companyUser('External Company');
        $activeWorker = $this->worker($externalCompany, 'Active electrician', 'Electrician');
        $this->worker($externalCompany, 'Archived welder', 'Welder', ['archived_at' => now()]);

        $this->actingAs($viewer)
            ->get(route('find-workers.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('FindWorkers')
                ->has('workers.data', 1)
                ->where('workers.data.0.id', $activeWorker->id)
                ->where('jobs', ['Electrician'])
            );
    }

    public function test_archived_workers_are_excluded_from_application_and_availability_operational_data(): void
    {
        [$applicant, $applicantCompany] = $this->companyUser('Applicant Company');
        [$hiringManager, $hiringCompany] = $this->companyUser('Hiring Company');
        $mission = $this->mission($hiringManager, $hiringCompany, 'Open mission');
        $activeWorker = $this->worker($applicantCompany, 'Active electrician', 'Electrician');
        $archivedWorker = $this->worker($applicantCompany, 'Archived electrician', 'Electrician', [
            'archived_at' => now(),
        ]);
        $availability = Availability::create([
            'worker_profile_id' => $archivedWorker->id,
            'date' => '2026-10-15',
            'start_time' => '07:00',
            'end_time' => '15:00',
            'status' => 'available',
        ]);

        $this->actingAs($applicant)
            ->get(route('find-missions.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('FindMissions')
                ->has('workers', 1)
                ->where('workers.0.id', $activeWorker->id)
            );

        $this->actingAs($applicant)
            ->get(route('availability.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Availability')
                ->has('workerProfiles', 1)
                ->where('workerProfiles.0.id', $activeWorker->id)
                ->has('availability.data', 0)
            );

        $this->actingAs($applicant)
            ->getJson(route('availability.calendar', [
                'start' => '2026-10-15',
                'end' => '2026-10-15',
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'workers')
            ->assertJsonCount(0, 'availabilities');

        $this->assertDatabaseHas('availabilities', ['id' => $availability->id]);
        $this->assertDatabaseHas('missions', ['id' => $mission->id]);
    }

    public function test_archived_workers_are_rejected_by_operational_request_and_availability_endpoints(): void
    {
        [$hiringManager, $hiringCompany] = $this->companyUser('Hiring Company');
        [$lendingManager, $lendingCompany] = $this->companyUser('Lending Company');
        $hiringMission = $this->mission($hiringManager, $hiringCompany, 'Hiring mission');
        $externalMission = $this->mission($lendingManager, $lendingCompany, 'External mission');
        $archivedLendingWorker = $this->worker($lendingCompany, 'Archived lending worker', 'Electrician', [
            'archived_at' => now(),
        ]);
        $archivedHiringWorker = $this->worker($hiringCompany, 'Archived hiring worker', 'Electrician', [
            'archived_at' => now(),
        ]);
        $availability = Availability::create([
            'worker_profile_id' => $archivedHiringWorker->id,
            'date' => '2026-10-16',
            'start_time' => '07:00',
            'end_time' => '15:00',
            'status' => 'available',
        ]);

        $this->actingAs($hiringManager)
            ->from(route('find-workers.index'))
            ->post(route('request-worker.store', $archivedLendingWorker), [
                'mission_id' => $hiringMission->id,
            ])
            ->assertSessionHasErrors('worker');

        $this->actingAs($lendingManager)
            ->from(route('find-missions.index'))
            ->post(route('request-mission.store', $hiringMission), [
                'worker_profile_id' => $archivedLendingWorker->id,
            ])
            ->assertSessionHasErrors('worker_profile_id');

        $this->actingAs($hiringManager)
            ->from(route('availability.index'))
            ->post(route('availability.store'), $this->availabilityPayload($archivedHiringWorker))
            ->assertSessionHasErrors('worker_profile_id');

        $this->actingAs($hiringManager)
            ->from(route('availability.index'))
            ->put(route('availability.update', $availability), $this->availabilityPayload($archivedHiringWorker))
            ->assertSessionHasErrors('worker_profile_id');

        $this->assertDatabaseMissing('requests', [
            'mission_id' => $hiringMission->id,
            'worker_profile_id' => $archivedLendingWorker->id,
        ]);
        $this->assertSame('07:00', $availability->fresh()->start_time);
        $this->assertDatabaseHas('missions', ['id' => $externalMission->id]);
    }

    public function test_administrators_cannot_bypass_archived_worker_operational_guards(): void
    {
        [$hiringManager, $hiringCompany] = $this->companyUser('Hiring Company');
        [, $lendingCompany] = $this->companyUser('Lending Company');
        $mission = $this->mission($hiringManager, $hiringCompany, 'Hiring mission');
        $archivedWorker = $this->worker($lendingCompany, 'Archived worker', 'Electrician', [
            'archived_at' => now(),
        ]);
        $administrator = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'administrator'])->id,
        ]);

        $this->actingAs($administrator)
            ->from(route('find-workers.index'))
            ->post(route('request-worker.store', $archivedWorker), [
                'mission_id' => $mission->id,
            ])
            ->assertSessionHasErrors('worker');

        $this->actingAs($administrator)
            ->from(route('find-missions.index'))
            ->post(route('request-mission.store', $mission), [
                'worker_profile_id' => $archivedWorker->id,
            ])
            ->assertSessionHasErrors('worker_profile_id');

        $this->actingAs($administrator)
            ->from(route('availability.index'))
            ->post(route('availability.store'), $this->availabilityPayload($archivedWorker))
            ->assertSessionHasErrors('worker_profile_id');
    }

    public function test_archived_self_employed_workers_cannot_apply_but_find_missions_remains_browsable(): void
    {
        [$hiringManager, $hiringCompany] = $this->companyUser('Hiring Company');
        $mission = $this->mission($hiringManager, $hiringCompany, 'Hiring mission');
        $selfEmployed = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'self_employed'])->id,
            'company_id' => null,
        ]);
        $archivedWorker = WorkerProfile::create([
            'user_id' => $selfEmployed->id,
            'company_id' => null,
            'name' => $selfEmployed->name,
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
            'archived_at' => now(),
        ]);

        $this->actingAs($selfEmployed)
            ->get(route('find-missions.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('FindMissions')
                ->has('missions.data', 1)
                ->has('workers', 0)
                ->where('hasWorkerProfile', false)
            );

        $this->actingAs($selfEmployed)
            ->from(route('find-missions.index'))
            ->post(route('request-mission.store', $mission), [
                'worker_profile_id' => $archivedWorker->id,
            ])
            ->assertSessionHasErrors('worker_profile_id');
    }

    public function test_home_active_worker_count_excludes_archived_profiles(): void
    {
        [$manager, $company] = $this->companyUser('Hiring Company');
        $this->worker($company, 'Active worker', 'Electrician');
        $this->worker($company, 'Archived worker', 'Electrician', ['archived_at' => now()]);

        $this->actingAs($manager)
            ->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->where('stats.active_workers', 1)
            );
    }

    public function test_archived_workers_remain_resolvable_in_authorized_mission_history_and_contextual_profile_views(): void
    {
        [$hiringManager, $hiringCompany] = $this->companyUser('Hiring Company');
        [$lendingManager, $lendingCompany] = $this->companyUser('Lending Company');
        [$unrelatedManager] = $this->companyUser('Unrelated Company');
        $mission = $this->mission($hiringManager, $hiringCompany, 'Completed mission', [
            'status' => 'completed',
        ]);
        $worker = $this->worker($lendingCompany, 'Historical archived worker', 'Electrician', [
            'archived_at' => now(),
        ]);
        $request = WorkerRequest::create([
            'mission_id' => $mission->id,
            'requested_by' => $lendingManager->id,
            'company_id' => $lendingCompany->id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        Rating::create([
            'mission_id' => $mission->id,
            'reviewed_by_user_id' => $hiringManager->id,
            'worker_profile_id' => $worker->id,
            'score' => 5,
            'feedback' => 'Historical rating remains linked.',
        ]);

        $this->actingAs($lendingManager)
            ->get(route('mission-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('missionData.tabs.completed.data.0.management_requests.0.worker.name', $worker->name)
                ->where('missionData.tabs.completed.data.0.management_requests.0.rating.worker_profile_id', $worker->id)
            );

        $this->assertSame($worker->id, Rating::with('worker')->firstOrFail()->worker->id);

        $this->actingAs($lendingManager)
            ->get(route('find-workers.index', [
                'worker' => $worker->id,
                'request' => $request->id,
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('FindWorkers')
                ->where('selectedWorker.id', $worker->id)
                ->where('selectedWorker.name', $worker->name)
            );

        $this->actingAs($unrelatedManager)
            ->get(route('find-workers.index', [
                'worker' => $worker->id,
                'request' => $request->id,
            ]))
            ->assertForbidden();
    }

    private function companyUser(string $companyName): array
    {
        $manager = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'company_owner'])->id,
        ]);
        $company = Company::create([
            'name' => $companyName,
            'owner_id' => $manager->id,
        ]);
        $manager->update(['company_id' => $company->id]);

        return [$manager->fresh(), $company];
    }

    private function mission(User $manager, Company $company, string $title, array $overrides = []): Mission
    {
        return Mission::create([
            'hiring_company_id' => $company->id,
            'created_by' => $manager->id,
            'title' => $title,
            'description' => 'Archived worker operational filtering coverage.',
            'city' => 'Calgary',
            'province' => 'Alberta',
            'country' => 'Canada',
            'job_type' => 'Electrician',
            'workers' => 1,
            'start_date' => today()->addWeek()->toDateString(),
            'end_date' => today()->addWeeks(2)->toDateString(),
            'status' => 'open',
            ...$overrides,
        ]);
    }

    private function worker(Company $company, string $name, string $job, array $overrides = []): WorkerProfile
    {
        return WorkerProfile::create([
            'company_id' => $company->id,
            'name' => $name,
            'job' => $job,
            'years_experience' => 5,
            'hourly_rate' => 40,
            ...$overrides,
        ]);
    }

    private function availabilityPayload(WorkerProfile $worker): array
    {
        return [
            'worker_profile_id' => $worker->id,
            'date' => '2026-10-20',
            'start_time' => '07:00',
            'end_time' => '15:00',
            'status' => 'available',
        ];
    }
}
