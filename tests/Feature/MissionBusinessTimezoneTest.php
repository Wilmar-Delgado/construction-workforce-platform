<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Mission;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use App\Support\MissionBusinessDateResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MissionBusinessTimezoneTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_company_mission_due_dates_follow_the_company_timezone_not_a_managers_personal_timezone(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 05:30:00', 'UTC'));

        [$owner, $company] = $this->companyManager('company_owner', 'America/Edmonton', 'America/Edmonton');
        [$planner] = $this->companyManager('planning_manager', 'America/Toronto', null, $company);
        $mission = $this->mission($company, $owner, '2026-09-13', '2026-09-20', true);
        $this->assignment($mission, $owner, 'accepted');

        $this->assertFalse(app(MissionBusinessDateResolver::class)->hasReachedStartDate($mission));

        $this->actingAs($owner)
            ->post(route('mission-management.start', $mission))
            ->assertSessionHasErrors('mission');

        $this->actingAs($planner)
            ->post(route('mission-management.start', $mission))
            ->assertSessionHasErrors('mission');

        Carbon::setTestNow(Carbon::parse('2026-09-13 06:30:00', 'UTC'));

        $this->assertTrue(app(MissionBusinessDateResolver::class)->hasReachedStartDate($mission->fresh()));

        $secondMission = $this->mission($company, $owner, '2026-09-13', '2026-09-20', true);
        $this->assignment($secondMission, $owner, 'accepted');

        $this->actingAs($owner)
            ->post(route('mission-management.start', $mission))
            ->assertSessionHasNoErrors();

        $this->actingAs($planner)
            ->post(route('mission-management.start', $secondMission))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('missions', ['id' => $mission->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('missions', ['id' => $secondMission->id, 'status' => 'in_progress']);
    }

    public function test_scheduler_processes_newfoundland_and_vancouver_missions_when_each_own_business_date_is_due(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 03:30:00', 'UTC'));

        [$newfoundlandOwner, $newfoundland] = $this->companyManager('company_owner', 'America/St_Johns', 'America/St_Johns');
        [$vancouverOwner, $vancouver] = $this->companyManager('company_owner', 'America/Vancouver', 'America/Vancouver');

        $newfoundlandMission = $this->mission($newfoundland, $newfoundlandOwner, '2026-09-13', '2026-09-20');
        $vancouverMission = $this->mission($vancouver, $vancouverOwner, '2026-09-13', '2026-09-20');
        $newfoundlandRequest = $this->assignment($newfoundlandMission, $newfoundlandOwner, 'accepted');
        $vancouverRequest = $this->assignment($vancouverMission, $vancouverOwner, 'accepted');

        $this->artisan('missions:process-start-dates')->assertExitCode(0);

        $this->assertDatabaseHas('missions', ['id' => $newfoundlandMission->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('requests', ['id' => $newfoundlandRequest->id, 'status' => 'ongoing']);
        $this->assertDatabaseHas('missions', ['id' => $vancouverMission->id, 'status' => 'open']);
        $this->assertDatabaseHas('requests', ['id' => $vancouverRequest->id, 'status' => 'accepted']);

        $this->artisan('missions:process-start-dates')->assertExitCode(0);
        $this->assertDatabaseHas('missions', ['id' => $newfoundlandMission->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('missions', ['id' => $vancouverMission->id, 'status' => 'open']);
    }

    public function test_scheduler_completes_each_mission_only_after_its_own_business_end_date_is_reached(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 03:30:00', 'UTC'));

        [$newfoundlandOwner, $newfoundland] = $this->companyManager('company_owner', 'America/St_Johns', 'America/St_Johns');
        [$vancouverOwner, $vancouver] = $this->companyManager('company_owner', 'America/Vancouver', 'America/Vancouver');

        $newfoundlandMission = $this->mission($newfoundland, $newfoundlandOwner, '2026-09-01', '2026-09-13', true, 'in_progress');
        $vancouverMission = $this->mission($vancouver, $vancouverOwner, '2026-09-01', '2026-09-13', true, 'in_progress');
        $this->assignment($newfoundlandMission, $newfoundlandOwner, 'completed');
        $this->assignment($vancouverMission, $vancouverOwner, 'completed');

        $this->artisan('missions:process-start-dates')->assertExitCode(0);

        $this->assertDatabaseHas('missions', ['id' => $newfoundlandMission->id, 'status' => 'completed']);
        $this->assertDatabaseHas('missions', ['id' => $vancouverMission->id, 'status' => 'in_progress']);
    }

    public function test_actionable_staffing_scope_uses_each_companys_local_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 03:30:00', 'UTC'));

        [$newfoundlandOwner, $newfoundland] = $this->companyManager('company_owner', 'America/St_Johns', 'America/St_Johns');
        [$vancouverOwner, $vancouver] = $this->companyManager('company_owner', 'America/Vancouver', 'America/Vancouver');

        $newfoundlandMission = $this->mission($newfoundland, $newfoundlandOwner, '2026-09-13', '2026-09-20', false);
        $vancouverMission = $this->mission($vancouver, $vancouverOwner, '2026-09-13', '2026-09-20', false);

        $actionableIds = Mission::query()->actionableForStaffing()->pluck('id')->all();

        $this->assertNotContains($newfoundlandMission->id, $actionableIds);
        $this->assertContains($vancouverMission->id, $actionableIds);
    }

    public function test_regina_uses_its_iana_timezone_without_dst_offset_changes(): void
    {
        [$owner, $company] = $this->companyManager('company_owner', 'America/Regina', 'America/Regina');
        $mission = $this->mission($company, $owner, '2026-01-01', '2026-01-02');
        $resolver = app(MissionBusinessDateResolver::class);

        Carbon::setTestNow(Carbon::parse('2026-01-01 06:30:00', 'UTC'));
        $winter = $resolver->todayForMission($mission)->format('Y-m-d H:i P');

        Carbon::setTestNow(Carbon::parse('2026-07-01 06:30:00', 'UTC'));
        $summer = $resolver->todayForMission($mission)->format('Y-m-d H:i P');

        $this->assertSame('2026-01-01 00:00 -06:00', $winter);
        $this->assertSame('2026-07-01 00:00 -06:00', $summer);
    }

    public function test_invalid_company_timezones_and_self_employed_contexts_fall_back_safely(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 01:30:00', 'UTC'));

        [$owner, $company] = $this->companyManager('company_owner', 'Europe/Paris', 'Europe/Paris');
        $mission = $this->mission($company, $owner, '2026-09-13', '2026-09-20');
        $selfEmployed = User::factory()->create(['timezone' => 'America/Vancouver']);
        $unsupportedSelfEmployed = User::factory()->create(['timezone' => 'Europe/Paris']);
        $resolver = app(MissionBusinessDateResolver::class);

        $this->assertSame('UTC', $resolver->timezoneForMission($mission));
        $this->assertSame('2026-09-13', $resolver->todayForMission($mission)->toDateString());
        $this->assertSame('America/Vancouver', $resolver->timezoneForUser($selfEmployed));
        $this->assertSame('2026-09-12', $resolver->todayForUser($selfEmployed)->toDateString());
        $this->assertSame('UTC', $resolver->timezoneForUser($unsupportedSelfEmployed));
    }

    private function companyManager(string $roleName, string $userTimezone, ?string $companyTimezone, ?Company $company = null): array
    {
        $role = Role::firstOrCreate(['name' => $roleName]);
        $manager = User::factory()->create([
            'role_id' => $role->id,
            'timezone' => $userTimezone,
        ]);

        $company ??= Company::create([
            'name' => "{$roleName} company ".Company::query()->count(),
            'owner_id' => $manager->id,
            'timezone' => $companyTimezone,
        ]);

        $manager->update(['company_id' => $company->id]);

        return [$manager->fresh(), $company->fresh()];
    }

    private function mission(
        Company $company,
        User $owner,
        string $startDate,
        string $endDate,
        bool $recruitingClosed = true,
        string $status = 'open',
    ): Mission {
        return Mission::create([
            'hiring_company_id' => $company->id,
            'created_by' => $owner->id,
            'title' => 'Business timezone mission '.Mission::query()->count(),
            'city' => 'Calgary',
            'province' => 'Alberta',
            'job_type' => 'Electrician',
            'workers' => 3,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'recruiting_closed_at' => $recruitingClosed ? now() : null,
        ]);
    }

    private function assignment(Mission $mission, User $requester, string $status): WorkerRequest
    {
        $worker = WorkerProfile::create([
            'company_id' => $requester->company_id,
            'name' => 'Timezone worker '.WorkerProfile::query()->count(),
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);

        return WorkerRequest::create([
            'mission_id' => $mission->id,
            'requested_by' => $requester->id,
            'company_id' => $requester->company_id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => $status,
        ]);
    }
}
