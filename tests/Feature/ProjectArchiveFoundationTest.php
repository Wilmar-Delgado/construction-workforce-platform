<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectArchiveFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_archived_at_exists_defaults_to_null_and_can_be_persisted(): void
    {
        [$manager] = $this->companyManager('Archive Company');
        $project = $this->project($manager, 'Archivable project', ['status' => 'completed']);

        $this->assertTrue(Schema::hasColumn('projects', 'archived_at'));
        $this->assertNull($project->archived_at);

        $this->actingAs($manager)
            ->put(route('projects.archive', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertNotNull($project->fresh()->archived_at);
    }

    public function test_archive_scopes_separate_archived_and_operational_projects(): void
    {
        [$manager] = $this->companyManager('Archive Company');
        $operational = $this->project($manager, 'Operational project');
        $archived = $this->project($manager, 'Archived project', ['archived_at' => now()]);

        $this->assertSame([$operational->id], Project::notArchived()->pluck('id')->all());
        $this->assertSame([$archived->id], Project::archived()->pluck('id')->all());
        $this->assertTrue($operational->isActionableForStaffing());
        $this->assertFalse($archived->isActionableForStaffing());
    }

    public function test_archived_projects_are_excluded_from_my_projects_and_counts(): void
    {
        [$manager] = $this->companyManager('Archive Company');
        $operational = $this->project($manager, 'Operational project');
        $this->project($manager, 'Archived project', ['archived_at' => now()]);

        $this->actingAs($manager)
            ->get(route('projects.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects')
                ->has('projects.data', 1)
                ->where('projects.data.0.id', $operational->id)
                ->where('counts.all', 1)
                ->where('counts.open', 1)
            );
    }

    public function test_archived_projects_are_excluded_from_find_projects(): void
    {
        [$viewer] = $this->companyManager('Viewer Company');
        [$hiringManager] = $this->companyManager('Hiring Company');
        $operational = $this->project($hiringManager, 'Operational project');
        $this->project($hiringManager, 'Archived project', ['archived_at' => now()]);

        $this->actingAs($viewer)
            ->get(route('find-projects.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('FindProjects')
                ->has('projects.data', 1)
                ->where('projects.data.0.id', $operational->id)
            );
    }

    public function test_archived_projects_are_excluded_from_the_find_workers_invitation_selector(): void
    {
        [$manager] = $this->companyManager('Hiring Company');
        $operational = $this->project($manager, 'Operational project');
        $this->project($manager, 'Archived project', ['archived_at' => now()]);

        $this->actingAs($manager)
            ->get(route('find-workers.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('FindWorkers')
                ->has('projects', 1)
                ->where('projects.0.id', $operational->id)
            );
    }

    public function test_archived_completed_projects_remain_visible_in_authorized_project_management_history(): void
    {
        [$hiringManager] = $this->companyManager('Hiring Company');
        [$lendingManager, $lendingCompany] = $this->companyManager('Lending Company');
        $project = $this->project($hiringManager, 'Archived completed project', [
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
            'project_id' => $project->id,
            'requested_by' => $lendingManager->id,
            'company_id' => $lendingCompany->id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->actingAs($hiringManager)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('ProjectManagement')
                ->where('projectData.counts.completed', 1)
                ->has('projectData.tabs.completed.data', 1)
                ->where('projectData.tabs.completed.data.0.id', $project->id)
                ->where('projectData.tabs.completed.data.0.management_context.can_view_project', false)
            );
    }

    public function test_completed_projects_do_not_expose_the_project_view_capability_in_management_history(): void
    {
        [$hiringManager] = $this->companyManager('Hiring Company');
        [$lendingManager, $lendingCompany] = $this->companyManager('Lending Company');
        $project = $this->project($hiringManager, 'Completed project', ['status' => 'completed']);
        $worker = WorkerProfile::create([
            'company_id' => $lendingCompany->id,
            'name' => 'Completed worker',
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);
        WorkerRequest::create([
            'project_id' => $project->id,
            'requested_by' => $lendingManager->id,
            'company_id' => $lendingCompany->id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->actingAs($hiringManager)
            ->get(route('project-management.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('ProjectManagement')
                ->where('projectData.tabs.completed.data.0.id', $project->id)
                ->where('projectData.tabs.completed.data.0.management_context.can_view_project', false)
            );
    }

    public function test_my_projects_exposes_backend_derived_delete_and_archive_capabilities(): void
    {
        [$manager, $company] = $this->companyManager('Archive Company');
        $unusedDraft = $this->project($manager, 'Unused draft', ['status' => 'draft']);
        $draftWithHistory = $this->project($manager, 'Draft with history', ['status' => 'draft']);
        $open = $this->project($manager, 'Open project');
        $inProgress = $this->project($manager, 'In progress project', ['status' => 'in_progress']);
        $completed = $this->project($manager, 'Completed project', ['status' => 'completed']);
        $archivedCompleted = $this->project($manager, 'Archived completed project', [
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
            'project_id' => $draftWithHistory->id,
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
        ] as [$project, $minutesAgo]) {
            DB::table('projects')
                ->where('id', $project->id)
                ->update(['created_at' => now()->subMinutes($minutesAgo)]);
        }

        $this->assertFalse($archivedCompleted->canBePermanentlyDeleted());
        $this->assertFalse($archivedCompleted->canBeArchived());

        $this->actingAs($manager)
            ->get(route('projects.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects')
                ->has('projects.data', 5)
                ->where('projects.data.0.id', $completed->id)
                ->where('projects.data.0.can_delete', false)
                ->where('projects.data.0.can_archive', true)
                ->where('projects.data.1.id', $inProgress->id)
                ->where('projects.data.1.can_delete', false)
                ->where('projects.data.1.can_archive', false)
                ->where('projects.data.2.id', $open->id)
                ->where('projects.data.2.can_delete', false)
                ->where('projects.data.2.can_archive', false)
                ->where('projects.data.3.id', $draftWithHistory->id)
                ->where('projects.data.3.can_delete', false)
                ->where('projects.data.3.can_archive', false)
                ->where('projects.data.4.id', $unusedDraft->id)
                ->where('projects.data.4.can_delete', true)
                ->where('projects.data.4.can_archive', false)
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

    private function project(User $manager, string $title, array $overrides = []): Project
    {
        return Project::create([
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
