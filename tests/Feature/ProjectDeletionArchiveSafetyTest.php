<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\Rating;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDeletionArchiveSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unused_unarchived_draft_can_be_permanently_deleted(): void
    {
        [$manager] = $this->companyManager('Hiring Company');
        $project = $this->project($manager, 'Unused draft', ['status' => 'draft']);
        $project->requirements()->create(['name' => 'Safety training']);

        $this->actingAs($manager)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('project_requirements', ['project_id' => $project->id]);
    }

    public function test_a_draft_with_request_history_cannot_be_permanently_deleted(): void
    {
        [$manager, $company] = $this->companyManager('Hiring Company');
        $project = $this->project($manager, 'Draft with request history', ['status' => 'draft']);
        $request = $this->request($project, $manager, $company, $this->worker($company, 'Requested worker'), 'pending');

        $this->assertDeletionIsRefused($manager, $project);
        $this->assertDatabaseHas('requests', ['id' => $request->id]);
    }

    public function test_a_draft_with_rating_history_cannot_be_permanently_deleted(): void
    {
        [$manager, $company] = $this->companyManager('Hiring Company');
        $project = $this->project($manager, 'Draft with rating history', ['status' => 'draft']);
        $worker = $this->worker($company, 'Rated worker');
        $rating = Rating::create([
            'project_id' => $project->id,
            'reviewed_by_user_id' => $manager->id,
            'worker_profile_id' => $worker->id,
            'score' => 5,
            'feedback' => 'Historical rating.',
        ]);

        $this->assertDeletionIsRefused($manager, $project);
        $this->assertDatabaseHas('ratings', ['id' => $rating->id]);
    }

    public function test_non_draft_projects_and_archived_projects_cannot_be_permanently_deleted(): void
    {
        [$manager] = $this->companyManager('Hiring Company');

        foreach ([
            ['open', []],
            ['open', ['recruiting_closed_at' => now()]],
            ['in_progress', []],
            ['completed', []],
            ['cancelled', []],
            ['draft', ['archived_at' => now()]],
        ] as [$status, $overrides]) {
            $project = $this->project($manager, "Protected {$status} project", [
                'status' => $status,
                ...$overrides,
            ]);

            $this->assertDeletionIsRefused($manager, $project);
        }
    }

    public function test_archiving_a_completed_project_preserves_all_related_history(): void
    {
        [$hiringManager, $hiringCompany] = $this->companyManager('Hiring Company');
        [$lendingManager, $lendingCompany] = $this->companyManager('Lending Company');
        $project = $this->project($hiringManager, 'Completed historical project', ['status' => 'completed']);
        $project->requirements()->createMany([
            ['name' => 'First Aid'],
            ['name' => 'Fall Protection'],
        ]);

        $completedWorker = $this->worker($lendingCompany, 'Completed worker');
        $endedEarlyWorker = $this->worker($lendingCompany, 'Ended early worker');
        $rejectedWorker = $this->worker($lendingCompany, 'Rejected worker');

        $completedRequest = $this->request($project, $lendingManager, $lendingCompany, $completedWorker, 'completed', [
            'message' => 'Original completed application.',
            'responded_at' => now()->subDays(3),
            'completed_at' => now()->subDay(),
        ]);
        $endedEarlyRequest = $this->request($project, $lendingManager, $lendingCompany, $endedEarlyWorker, 'ended_early', [
            'ended_at' => now()->subDay(),
        ]);
        $rejectedRequest = $this->request($project, $lendingManager, $lendingCompany, $rejectedWorker, 'rejected', [
            'message' => 'Original rejected application.',
            'rejection_message' => 'This application was not selected.',
            'responded_at' => now()->subDays(2),
            'responded_by' => $hiringManager->id,
        ]);
        $rating = Rating::create([
            'project_id' => $project->id,
            'reviewed_by_user_id' => $hiringManager->id,
            'worker_profile_id' => $completedWorker->id,
            'score' => 5,
            'feedback' => 'Excellent work.',
        ]);

        $this->actingAs($hiringManager)
            ->put(route('projects.archive', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertNotNull($project->fresh()->archived_at);
        $this->assertDatabaseHas('requests', [
            'id' => $completedRequest->id,
            'status' => 'completed',
            'message' => 'Original completed application.',
        ]);
        $this->assertDatabaseHas('requests', [
            'id' => $endedEarlyRequest->id,
            'status' => 'ended_early',
        ]);
        $this->assertDatabaseHas('requests', [
            'id' => $rejectedRequest->id,
            'status' => 'rejected',
            'message' => 'Original rejected application.',
            'rejection_message' => 'This application was not selected.',
            'responded_by' => $hiringManager->id,
        ]);
        $this->assertDatabaseHas('ratings', [
            'id' => $rating->id,
            'project_id' => $project->id,
            'worker_profile_id' => $completedWorker->id,
            'reviewed_by_user_id' => $hiringManager->id,
        ]);
        $this->assertDatabaseCount('project_requirements', 2);
    }

    public function test_non_completed_and_already_archived_projects_cannot_be_archived(): void
    {
        [$manager] = $this->companyManager('Hiring Company');

        foreach (['draft', 'open', 'in_progress', 'cancelled'] as $status) {
            $project = $this->project($manager, "{$status} project", ['status' => $status]);

            $this->actingAs($manager)
                ->from(route('projects.index'))
                ->put(route('projects.archive', $project))
                ->assertSessionHasErrors('project');

            $this->assertNull($project->fresh()->archived_at);
        }

        $archivedProject = $this->project($manager, 'Already archived project', [
            'status' => 'completed',
            'archived_at' => now()->subDay(),
        ]);

        $this->actingAs($manager)
            ->from(route('projects.index'))
            ->put(route('projects.archive', $archivedProject))
            ->assertSessionHasErrors('project');

        $this->assertNotNull($archivedProject->fresh()->archived_at);
    }

    public function test_unrelated_company_and_self_employed_users_cannot_manage_projects(): void
    {
        [$owner] = $this->companyManager('Hiring Company');
        [$unrelated] = $this->companyManager('Unrelated Company');
        $project = $this->project($owner, 'Protected draft', ['status' => 'draft']);
        $selfEmployed = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'self_employed'])->id,
            'company_id' => null,
        ]);

        $this->actingAs($unrelated)
            ->delete(route('projects.destroy', $project))
            ->assertForbidden();
        $this->actingAs($selfEmployed)
            ->delete(route('projects.destroy', $project))
            ->assertForbidden();

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    public function test_administrator_cannot_bypass_retention_rules_on_the_delete_endpoint(): void
    {
        [$owner] = $this->companyManager('Hiring Company');
        $project = $this->project($owner, 'Unsafe open project', ['status' => 'open']);
        $administrator = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'administrator'])->id,
        ]);

        $this->actingAs($administrator)
            ->from(route('projects.index'))
            ->delete(route('projects.destroy', $project))
            ->assertSessionHasErrors('project');

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    private function assertDeletionIsRefused(User $manager, Project $project): void
    {
        $this->actingAs($manager)
            ->from(route('projects.index'))
            ->delete(route('projects.destroy', $project))
            ->assertSessionHasErrors('project');

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
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
            'description' => 'Project history retention coverage.',
            'city' => 'Calgary',
            'province' => 'Alberta',
            'country' => 'Canada',
            'job_type' => 'Electrician',
            'workers' => 3,
            'start_date' => today()->subWeek()->toDateString(),
            'end_date' => today()->toDateString(),
            'status' => 'open',
            ...$overrides,
        ]);
    }

    private function worker(Company $company, string $name): WorkerProfile
    {
        return WorkerProfile::create([
            'company_id' => $company->id,
            'name' => $name,
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);
    }

    private function request(
        Project $project,
        User $requestedBy,
        Company $company,
        WorkerProfile $worker,
        string $status,
        array $overrides = [],
    ): WorkerRequest {
        return WorkerRequest::create([
            'project_id' => $project->id,
            'requested_by' => $requestedBy->id,
            'company_id' => $company->id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => $status,
            ...$overrides,
        ]);
    }
}
