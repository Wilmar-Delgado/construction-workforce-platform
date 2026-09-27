<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use App\Support\ProjectBusinessDateResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProjectBusinessTimezoneTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_company_project_due_dates_follow_the_company_timezone_not_a_managers_personal_timezone(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 05:30:00', 'UTC'));

        [$owner, $company] = $this->companyManager('company_owner', 'America/Edmonton', 'America/Edmonton');
        [$planner] = $this->companyManager('planning_manager', 'America/Toronto', null, $company);
        $project = $this->project($company, $owner, '2026-09-13', '2026-09-20', true);
        $this->assignment($project, $owner, 'accepted');

        $this->assertFalse(app(ProjectBusinessDateResolver::class)->hasReachedStartDate($project));

        $this->actingAs($owner)
            ->post(route('project-management.start', $project))
            ->assertSessionHasErrors('project');

        $this->actingAs($planner)
            ->post(route('project-management.start', $project))
            ->assertSessionHasErrors('project');

        Carbon::setTestNow(Carbon::parse('2026-09-13 06:30:00', 'UTC'));

        $this->assertTrue(app(ProjectBusinessDateResolver::class)->hasReachedStartDate($project->fresh()));

        $secondProject = $this->project($company, $owner, '2026-09-13', '2026-09-20', true);
        $this->assignment($secondProject, $owner, 'accepted');

        $this->actingAs($owner)
            ->post(route('project-management.start', $project))
            ->assertSessionHasNoErrors();

        $this->actingAs($planner)
            ->post(route('project-management.start', $secondProject))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('projects', ['id' => $secondProject->id, 'status' => 'in_progress']);
    }

    public function test_scheduler_processes_newfoundland_and_vancouver_projects_when_each_own_business_date_is_due(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 03:30:00', 'UTC'));

        [$newfoundlandOwner, $newfoundland] = $this->companyManager('company_owner', 'America/St_Johns', 'America/St_Johns');
        [$vancouverOwner, $vancouver] = $this->companyManager('company_owner', 'America/Vancouver', 'America/Vancouver');

        $newfoundlandProject = $this->project($newfoundland, $newfoundlandOwner, '2026-09-13', '2026-09-20');
        $vancouverProject = $this->project($vancouver, $vancouverOwner, '2026-09-13', '2026-09-20');
        $newfoundlandRequest = $this->assignment($newfoundlandProject, $newfoundlandOwner, 'accepted');
        $vancouverRequest = $this->assignment($vancouverProject, $vancouverOwner, 'accepted');

        $this->artisan('projects:process-start-dates')->assertExitCode(0);

        $this->assertDatabaseHas('projects', ['id' => $newfoundlandProject->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('requests', ['id' => $newfoundlandRequest->id, 'status' => 'ongoing']);
        $this->assertDatabaseHas('projects', ['id' => $vancouverProject->id, 'status' => 'open']);
        $this->assertDatabaseHas('requests', ['id' => $vancouverRequest->id, 'status' => 'accepted']);

        $this->artisan('projects:process-start-dates')->assertExitCode(0);
        $this->assertDatabaseHas('projects', ['id' => $newfoundlandProject->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('projects', ['id' => $vancouverProject->id, 'status' => 'open']);
    }

    public function test_scheduler_completes_each_project_only_after_its_own_business_end_date_is_reached(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 03:30:00', 'UTC'));

        [$newfoundlandOwner, $newfoundland] = $this->companyManager('company_owner', 'America/St_Johns', 'America/St_Johns');
        [$vancouverOwner, $vancouver] = $this->companyManager('company_owner', 'America/Vancouver', 'America/Vancouver');

        $newfoundlandProject = $this->project($newfoundland, $newfoundlandOwner, '2026-09-01', '2026-09-13', true, 'in_progress');
        $vancouverProject = $this->project($vancouver, $vancouverOwner, '2026-09-01', '2026-09-13', true, 'in_progress');
        $this->assignment($newfoundlandProject, $newfoundlandOwner, 'completed');
        $this->assignment($vancouverProject, $vancouverOwner, 'completed');

        $this->artisan('projects:process-start-dates')->assertExitCode(0);

        $this->assertDatabaseHas('projects', ['id' => $newfoundlandProject->id, 'status' => 'completed']);
        $this->assertDatabaseHas('projects', ['id' => $vancouverProject->id, 'status' => 'in_progress']);
    }

    public function test_actionable_staffing_scope_uses_each_companys_local_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 03:30:00', 'UTC'));

        [$newfoundlandOwner, $newfoundland] = $this->companyManager('company_owner', 'America/St_Johns', 'America/St_Johns');
        [$vancouverOwner, $vancouver] = $this->companyManager('company_owner', 'America/Vancouver', 'America/Vancouver');

        $newfoundlandProject = $this->project($newfoundland, $newfoundlandOwner, '2026-09-13', '2026-09-20', false);
        $vancouverProject = $this->project($vancouver, $vancouverOwner, '2026-09-13', '2026-09-20', false);

        $actionableIds = Project::query()->actionableForStaffing()->pluck('id')->all();

        $this->assertNotContains($newfoundlandProject->id, $actionableIds);
        $this->assertContains($vancouverProject->id, $actionableIds);
    }

    public function test_regina_uses_its_iana_timezone_without_dst_offset_changes(): void
    {
        [$owner, $company] = $this->companyManager('company_owner', 'America/Regina', 'America/Regina');
        $project = $this->project($company, $owner, '2026-01-01', '2026-01-02');
        $resolver = app(ProjectBusinessDateResolver::class);

        Carbon::setTestNow(Carbon::parse('2026-01-01 06:30:00', 'UTC'));
        $winter = $resolver->todayForProject($project)->format('Y-m-d H:i P');

        Carbon::setTestNow(Carbon::parse('2026-07-01 06:30:00', 'UTC'));
        $summer = $resolver->todayForProject($project)->format('Y-m-d H:i P');

        $this->assertSame('2026-01-01 00:00 -06:00', $winter);
        $this->assertSame('2026-07-01 00:00 -06:00', $summer);
    }

    public function test_invalid_company_timezones_and_self_employed_contexts_fall_back_safely(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-13 01:30:00', 'UTC'));

        [$owner, $company] = $this->companyManager('company_owner', 'Europe/Paris', 'Europe/Paris');
        $project = $this->project($company, $owner, '2026-09-13', '2026-09-20');
        $selfEmployed = User::factory()->create(['timezone' => 'America/Vancouver']);
        $unsupportedSelfEmployed = User::factory()->create(['timezone' => 'Europe/Paris']);
        $resolver = app(ProjectBusinessDateResolver::class);

        $this->assertSame('UTC', $resolver->timezoneForProject($project));
        $this->assertSame('2026-09-13', $resolver->todayForProject($project)->toDateString());
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

    private function project(
        Company $company,
        User $owner,
        string $startDate,
        string $endDate,
        bool $recruitingClosed = true,
        string $status = 'open',
    ): Project {
        return Project::create([
            'hiring_company_id' => $company->id,
            'created_by' => $owner->id,
            'title' => 'Business timezone project '.Project::query()->count(),
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

    private function assignment(Project $project, User $requester, string $status): WorkerRequest
    {
        $worker = WorkerProfile::create([
            'company_id' => $requester->company_id,
            'name' => 'Timezone worker '.WorkerProfile::query()->count(),
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);

        return WorkerRequest::create([
            'project_id' => $project->id,
            'requested_by' => $requester->id,
            'company_id' => $requester->company_id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => $status,
        ]);
    }
}
