<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Mission;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MissionArchiveFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_archived_at_exists_defaults_to_null_and_can_be_persisted(): void
    {
        [$manager] = $this->companyManager('Archive Company');
        $mission = $this->mission($manager, 'Archivable mission', ['status' => 'completed']);

        $this->assertTrue(Schema::hasColumn('missions', 'archived_at'));
        $this->assertNull($mission->archived_at);

        $this->actingAs($manager)
            ->put(route('missions.archive', $mission))
            ->assertRedirect(route('missions.index'));

        $this->assertNotNull($mission->fresh()->archived_at);
    }

    public function test_archive_scopes_separate_archived_and_operational_missions(): void
    {
        [$manager] = $this->companyManager('Archive Company');
        $operational = $this->mission($manager, 'Operational mission');
        $archived = $this->mission($manager, 'Archived mission', ['archived_at' => now()]);

        $this->assertSame([$operational->id], Mission::notArchived()->pluck('id')->all());
        $this->assertSame([$archived->id], Mission::archived()->pluck('id')->all());
        $this->assertTrue($operational->isActionableForStaffing());
        $this->assertFalse($archived->isActionableForStaffing());
    }

    public function test_archived_missions_are_excluded_from_my_missions_and_counts(): void
    {
        [$manager] = $this->companyManager('Archive Company');
        $operational = $this->mission($manager, 'Operational mission');
        $this->mission($manager, 'Archived mission', ['archived_at' => now()]);

        $this->actingAs($manager)
            ->get(route('missions.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Missions')
                ->has('missions.data', 1)
                ->where('missions.data.0.id', $operational->id)
                ->where('counts.all', 1)
                ->where('counts.open', 1)
            );
    }

    public function test_archived_missions_are_excluded_from_find_missions(): void
    {
        [$viewer] = $this->companyManager('Viewer Company');
        [$hiringManager] = $this->companyManager('Hiring Company');
        $operational = $this->mission($hiringManager, 'Operational mission');
        $this->mission($hiringManager, 'Archived mission', ['archived_at' => now()]);

        $this->actingAs($viewer)
            ->get(route('find-missions.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('FindMissions')
                ->has('missions.data', 1)
                ->where('missions.data.0.id', $operational->id)
            );
    }

    public function test_archived_missions_are_excluded_from_the_find_workers_invitation_selector(): void
    {
        [$manager] = $this->companyManager('Hiring Company');
        $operational = $this->mission($manager, 'Operational mission');
        $this->mission($manager, 'Archived mission', ['archived_at' => now()]);

        $this->actingAs($manager)
            ->get(route('find-workers.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('FindWorkers')
                ->has('missions', 1)
                ->where('missions.0.id', $operational->id)
            );
    }

    public function test_archived_completed_missions_remain_visible_in_authorized_mission_management_history(): void
    {
        [$hiringManager] = $this->companyManager('Hiring Company');
        [$lendingManager, $lendingCompany] = $this->companyManager('Lending Company');
        $mission = $this->mission($hiringManager, 'Archived completed mission', [
            'status' => 'completed',
            'archived_at' => now(),
        ]);
        $worker = WorkerProfile::create([
            'company_id' => $lendingCompany->id,
            'name' => 'Completed worker',
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);
        WorkerRequest::create([
            'mission_id' => $mission->id,
            'requested_by' => $lendingManager->id,
            'company_id' => $lendingCompany->id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->actingAs($hiringManager)
            ->get(route('mission-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('MissionManagement')
                ->where('missionData.counts.completed', 1)
                ->has('missionData.tabs.completed.data', 1)
                ->where('missionData.tabs.completed.data.0.id', $mission->id)
                ->where('missionData.tabs.completed.data.0.management_context.can_view_mission', false)
            );
    }

    public function test_completed_missions_do_not_expose_the_mission_view_capability_in_management_history(): void
    {
        [$hiringManager] = $this->companyManager('Hiring Company');
        [$lendingManager, $lendingCompany] = $this->companyManager('Lending Company');
        $mission = $this->mission($hiringManager, 'Completed mission', ['status' => 'completed']);
        $worker = WorkerProfile::create([
            'company_id' => $lendingCompany->id,
            'name' => 'Completed worker',
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);
        WorkerRequest::create([
            'mission_id' => $mission->id,
            'requested_by' => $lendingManager->id,
            'company_id' => $lendingCompany->id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->actingAs($hiringManager)
            ->get(route('mission-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('MissionManagement')
                ->where('missionData.tabs.completed.data.0.id', $mission->id)
                ->where('missionData.tabs.completed.data.0.management_context.can_view_mission', false)
            );
    }

    public function test_my_missions_exposes_backend_derived_delete_and_archive_capabilities(): void
    {
        [$manager, $company] = $this->companyManager('Archive Company');
        $unusedDraft = $this->mission($manager, 'Unused draft', ['status' => 'draft']);
        $draftWithHistory = $this->mission($manager, 'Draft with history', ['status' => 'draft']);
        $open = $this->mission($manager, 'Open mission');
        $inProgress = $this->mission($manager, 'In progress mission', ['status' => 'in_progress']);
        $completed = $this->mission($manager, 'Completed mission', ['status' => 'completed']);
        $archivedCompleted = $this->mission($manager, 'Archived completed mission', [
            'status' => 'completed',
            'archived_at' => now(),
        ]);
        $worker = WorkerProfile::create([
            'company_id' => $company->id,
            'name' => 'Historical worker',
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);
        WorkerRequest::create([
            'mission_id' => $draftWithHistory->id,
            'requested_by' => $manager->id,
            'company_id' => $company->id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => 'pending',
        ]);

        foreach ([
            [$unusedDraft, 5],
            [$draftWithHistory, 4],
            [$open, 3],
            [$inProgress, 2],
            [$completed, 1],
        ] as [$mission, $minutesAgo]) {
            DB::table('missions')
                ->where('id', $mission->id)
                ->update(['created_at' => now()->subMinutes($minutesAgo)]);
        }

        $this->assertFalse($archivedCompleted->canBePermanentlyDeleted());
        $this->assertFalse($archivedCompleted->canBeArchived());

        $this->actingAs($manager)
            ->get(route('missions.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Missions')
                ->has('missions.data', 5)
                ->where('missions.data.0.id', $completed->id)
                ->where('missions.data.0.can_delete', false)
                ->where('missions.data.0.can_archive', true)
                ->where('missions.data.1.id', $inProgress->id)
                ->where('missions.data.1.can_delete', false)
                ->where('missions.data.1.can_archive', false)
                ->where('missions.data.2.id', $open->id)
                ->where('missions.data.2.can_delete', false)
                ->where('missions.data.2.can_archive', false)
                ->where('missions.data.3.id', $draftWithHistory->id)
                ->where('missions.data.3.can_delete', false)
                ->where('missions.data.3.can_archive', false)
                ->where('missions.data.4.id', $unusedDraft->id)
                ->where('missions.data.4.can_delete', true)
                ->where('missions.data.4.can_archive', false)
            );
    }

    private function companyManager(string $companyName): array
    {
        $role = Role::firstOrCreate(['name' => 'company_owner']);
        $manager = User::factory()->create(['role_id' => $role->id]);
        $company = Company::create(['name' => $companyName, 'owner_id' => $manager->id]);
        $manager->update(['company_id' => $company->id]);

        return [$manager->fresh(), $company];
    }

    private function mission(User $manager, string $title, array $overrides = []): Mission
    {
        return Mission::create([
            'hiring_company_id' => $manager->company_id,
            'created_by' => $manager->id,
            'title' => $title,
            'description' => 'Archive foundation coverage.',
            'city' => 'Calgary',
            'province' => 'Alberta',
            'country' => 'Canada',
            'job_type' => 'Electrician',
            'workers' => 2,
            'start_date' => today()->addWeek()->toDateString(),
            'end_date' => today()->addWeeks(2)->toDateString(),
            'status' => 'open',
            ...$overrides,
        ]);
    }
}
