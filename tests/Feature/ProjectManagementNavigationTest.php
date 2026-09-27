<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectManagementNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_owner_can_retrieve_own_project_details(): void
    {
        [$owner] = $this->companyUser('Hiring Company');
        $project = $this->project($owner, 'Own project');
        $project->update([
            'site_name' => 'Downtown Site',
            'address_line_1' => '100 Main Street',
            'directions' => 'Use the east entrance.',
        ]);

        $this->actingAs($owner)
            ->getJson(route('project-management.projects.details', $project))
            ->assertOk()
            ->assertJsonPath('project.id', $project->id)
            ->assertJsonPath('project.hiring_company.name', 'Hiring Company')
            ->assertJsonPath('project.operational_details.site_name', 'Downtown Site')
            ->assertJsonMissingPath('project.hiring_company.owner');
    }

    public function test_planning_manager_can_retrieve_their_company_project_details(): void
    {
        [$owner, $company] = $this->companyUser('Hiring Company');
        $planningManager = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'planning_manager'])->id,
            'company_id' => $company->id,
        ]);
        $project = $this->project($owner, 'Company project');

        $this->actingAs($planningManager)
            ->getJson(route('project-management.projects.details', $project))
            ->assertOk()
            ->assertJsonPath('project.id', $project->id);
    }

    public function test_unrelated_company_cannot_retrieve_an_external_project_with_a_crafted_request(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$externalManager, $externalCompany] = $this->companyUser('External Company');
        [$unrelatedManager] = $this->companyUser('Unrelated Company');
        $project = $this->project($hiringManager, 'Private project');
        $request = $this->request(
            $project,
            $externalManager,
            $externalCompany,
            $this->worker($externalCompany, 'External worker'),
        );

        $this->actingAs($unrelatedManager)
            ->getJson(route('project-management.projects.details', [
                'project' => $project,
                'request' => $request->id,
            ]))
            ->assertForbidden();
    }

    public function test_external_company_can_retrieve_details_only_through_its_authorized_request(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$externalManager, $externalCompany] = $this->companyUser('External Company');
        $project = $this->project($hiringManager, 'External project');
        $request = $this->request(
            $project,
            $externalManager,
            $externalCompany,
            $this->worker($externalCompany, 'External worker'),
            'pending',
        );

        $this->actingAs($externalManager)
            ->getJson(route('project-management.projects.details', [
                'project' => $project,
                'request' => $request->id,
            ]))
            ->assertOk()
            ->assertJsonPath('project.id', $project->id)
            ->assertJsonMissingPath('project.operational_details');
    }

    public function test_external_company_owner_and_planning_manager_can_retrieve_their_application_project(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$externalOwner, $externalCompany] = $this->companyUser('External Company');
        $externalPlanner = $this->planningManager($externalCompany);
        $project = $this->project($hiringManager, 'Application project');
        $request = $this->request(
            $project,
            $externalOwner,
            $externalCompany,
            $this->worker($externalCompany, 'External worker'),
        );

        foreach ([$externalOwner, $externalPlanner] as $viewer) {
            $this->actingAs($viewer)
                ->getJson(route('project-management.projects.details', [
                    'project' => $project,
                    'request' => $request->id,
                ]))
                ->assertOk()
                ->assertJsonPath('project.id', $project->id);
        }
    }

    public function test_external_company_owner_and_planning_manager_can_retrieve_their_invited_worker_project(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$externalOwner, $externalCompany] = $this->companyUser('External Company');
        $externalPlanner = $this->planningManager($externalCompany);
        $project = $this->project($hiringManager, 'Invitation project');
        $request = $this->request(
            $project,
            $hiringManager,
            $hiringManager->company,
            $this->worker($externalCompany, 'Invited worker'),
            'pending',
            'invite',
        );

        foreach ([$externalOwner, $externalPlanner] as $viewer) {
            $this->actingAs($viewer)
                ->getJson(route('project-management.projects.details', [
                    'project' => $project,
                    'request' => $request->id,
                ]))
                ->assertOk()
                ->assertJsonPath('project.id', $project->id);
        }
    }

    public function test_self_employed_user_can_retrieve_details_only_through_their_request(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        $project = $this->project($hiringManager, 'Independent project');
        $selfEmployed = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'self_employed'])->id,
            'company_id' => null,
        ]);
        $request = $this->request(
            $project,
            $selfEmployed,
            null,
            $this->selfEmployedWorker($selfEmployed),
        );

        $this->actingAs($selfEmployed)
            ->getJson(route('project-management.projects.details', [
                'project' => $project,
                'request' => $request->id,
            ]))
            ->assertOk()
            ->assertJsonPath('project.id', $project->id);
    }

    public function test_self_employed_user_can_retrieve_their_invitation_project(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        $project = $this->project($hiringManager, 'Independent invitation project');
        $selfEmployed = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'self_employed'])->id,
            'company_id' => null,
        ]);
        $request = $this->request(
            $project,
            $hiringManager,
            $hiringManager->company,
            $this->selfEmployedWorker($selfEmployed),
            'pending',
            'invite',
        );

        $this->actingAs($selfEmployed)
            ->getJson(route('project-management.projects.details', [
                'project' => $project,
                'request' => $request->id,
            ]))
            ->assertOk()
            ->assertJsonPath('project.id', $project->id);
    }

    public function test_hiring_and_external_company_managers_can_retrieve_worker_details_through_an_application(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$externalOwner, $externalCompany] = $this->companyUser('External Company');
        $externalPlanner = $this->planningManager($externalCompany);
        $project = $this->project($hiringManager, 'Worker details application');
        $worker = $this->worker($externalCompany, 'External worker');
        $request = $this->request(
            $project,
            $externalOwner,
            $externalCompany,
            $worker,
        );

        foreach ([$hiringManager, $externalOwner, $externalPlanner] as $viewer) {
            $this->actingAs($viewer)
                ->getJson(route('project-management.workers.details', [
                    'workerProfile' => $worker,
                    'request' => $request->id,
                ]))
                ->assertOk()
                ->assertJsonPath('worker.id', $worker->id)
                ->assertJsonPath('worker.company.name', 'External Company');
        }
    }

    public function test_external_company_managers_can_retrieve_worker_details_through_an_invitation(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$externalOwner, $externalCompany] = $this->companyUser('External Company');
        $externalPlanner = $this->planningManager($externalCompany);
        $project = $this->project($hiringManager, 'Worker details invitation');
        $worker = $this->worker($externalCompany, 'Invited worker');
        $request = $this->request(
            $project,
            $hiringManager,
            $hiringManager->company,
            $worker,
            'pending',
            'invite',
        );

        foreach ([$externalOwner, $externalPlanner] as $viewer) {
            $this->actingAs($viewer)
                ->getJson(route('project-management.workers.details', [
                    'workerProfile' => $worker,
                    'request' => $request->id,
                ]))
                ->assertOk()
                ->assertJsonPath('worker.id', $worker->id);
        }
    }

    public function test_self_employed_user_can_retrieve_their_worker_details_through_their_request(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        $project = $this->project($hiringManager, 'Independent worker details');
        $selfEmployed = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'self_employed'])->id,
            'company_id' => null,
        ]);
        $worker = $this->selfEmployedWorker($selfEmployed);
        $request = $this->request($project, $selfEmployed, null, $worker);

        $this->actingAs($selfEmployed)
            ->getJson(route('project-management.workers.details', [
                'workerProfile' => $worker,
                'request' => $request->id,
            ]))
            ->assertOk()
            ->assertJsonPath('worker.id', $worker->id)
            ->assertJsonPath('worker.company', null);
    }

    public function test_worker_details_remain_available_for_authorized_historical_request_states(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$externalManager, $externalCompany] = $this->companyUser('External Company');

        foreach (['rejected', 'cancelled', 'completed', 'ended_early'] as $status) {
            $project = $this->project($hiringManager, "{$status} worker history");
            $worker = $this->worker($externalCompany, "{$status} worker");

            if ($status === 'completed') {
                $worker->update(['archived_at' => now()]);
            }

            $request = $this->request(
                $project,
                $externalManager,
                $externalCompany,
                $worker,
                $status,
            );

            $this->actingAs($externalManager)
                ->getJson(route('project-management.workers.details', [
                    'workerProfile' => $worker,
                    'request' => $request->id,
                ]))
                ->assertOk()
                ->assertJsonPath('worker.id', $worker->id);
        }
    }

    public function test_worker_details_reject_unrelated_mismatched_and_requestless_access(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$externalManager, $externalCompany] = $this->companyUser('External Company');
        [$unrelatedManager] = $this->companyUser('Unrelated Company');
        $project = $this->project($hiringManager, 'Private worker details');
        $worker = $this->worker($externalCompany, 'External worker');
        $request = $this->request(
            $project,
            $externalManager,
            $externalCompany,
            $worker,
        );
        $otherWorker = $this->worker($externalCompany, 'Other worker');

        $this->actingAs($unrelatedManager)
            ->getJson(route('project-management.workers.details', [
                'workerProfile' => $worker,
                'request' => $request->id,
            ]))
            ->assertForbidden();

        $this->actingAs($externalManager)
            ->getJson(route('project-management.workers.details', [
                'workerProfile' => $otherWorker,
                'request' => $request->id,
            ]))
            ->assertNotFound();

        $this->actingAs($externalManager)
            ->getJson(route('project-management.workers.details', $worker))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('request');
    }

    public function test_mismatched_project_and_request_ids_are_rejected(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$externalManager, $externalCompany] = $this->companyUser('External Company');
        $project = $this->project($hiringManager, 'First project');
        $otherProject = $this->project($hiringManager, 'Second project');
        $request = $this->request(
            $project,
            $externalManager,
            $externalCompany,
            $this->worker($externalCompany, 'External worker'),
        );

        $this->actingAs($externalManager)
            ->getJson(route('project-management.projects.details', [
                'project' => $otherProject,
                'request' => $request->id,
            ]))
            ->assertNotFound();
    }

    public function test_external_project_cannot_be_retrieved_from_project_id_alone(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$externalManager] = $this->companyUser('External Company');
        $project = $this->project($hiringManager, 'Private project');

        $this->actingAs($externalManager)
            ->getJson(route('project-management.projects.details', $project))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('request');
    }

    public function test_external_relationship_statuses_keep_their_intended_detail_visibility(): void
    {
        [$hiringManager] = $this->companyUser('Hiring Company');
        [$externalManager, $externalCompany] = $this->companyUser('External Company');

        foreach (['pending', 'rejected', 'cancelled', 'ended_early'] as $status) {
            $project = $this->project($hiringManager, "{$status} project");

            if ($status === 'ended_early') {
                $project->update(['status' => 'in_progress']);
            }

            $project->update([
                'site_name' => 'Downtown Site',
                'address_line_1' => '100 Main Street',
            ]);
            $request = $this->request(
                $project,
                $externalManager,
                $externalCompany,
                $this->worker($externalCompany, "{$status} worker"),
                $status,
            );

            $this->actingAs($externalManager)
                ->getJson(route('project-management.projects.details', [
                    'project' => $project,
                    'request' => $request->id,
                ]))
                ->assertOk()
                ->assertJsonMissingPath('project.operational_details');
        }

        foreach (['accepted', 'ongoing', 'completed'] as $status) {
            $project = $this->project($hiringManager, "{$status} project");

            if (in_array($status, ['ongoing', 'completed'], true)) {
                $project->update(['status' => 'in_progress']);
            }

            $project->update([
                'site_name' => 'Downtown Site',
                'address_line_1' => '100 Main Street',
            ]);
            $request = $this->request(
                $project,
                $externalManager,
                $externalCompany,
                $this->worker($externalCompany, "{$status} worker"),
                $status,
            );

            $this->actingAs($externalManager)
                ->getJson(route('project-management.projects.details', [
                    'project' => $project,
                    'request' => $request->id,
                ]))
                ->assertOk()
                ->assertJsonPath(
                    'project.operational_details.site_name',
                    'Downtown Site',
                );
        }
    }

    public function test_archived_and_completed_projects_are_rejected_by_the_details_endpoint(): void
    {
        [$owner] = $this->companyUser('Hiring Company');
        $archivedProject = $this->project($owner, 'Archived project');
        $archivedProject->update(['archived_at' => now()]);
        $completedProject = $this->project($owner, 'Completed project');
        $completedProject->update(['status' => 'completed']);

        $this->actingAs($owner)
            ->getJson(route('project-management.projects.details', $archivedProject))
            ->assertNotFound();

        $this->actingAs($owner)
            ->getJson(route('project-management.projects.details', $completedProject))
            ->assertNotFound();
    }

    public function test_normal_my_projects_and_find_projects_pages_do_not_receive_contextual_project_props(): void
    {
        [$owner] = $this->companyUser('Hiring Company');
        [$externalManager] = $this->companyUser('External Company');
        $this->project($owner, 'Normal project');

        $this->actingAs($owner)
            ->get(route('projects.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Projects')
                ->missing('selectedProject')
            );

        $this->actingAs($externalManager)
            ->get(route('find-projects.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('FindProjects')
                ->missing('selectedProject')
            );
    }

    private function companyUser(string $companyName): array
    {
        $user = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'company_owner'])->id,
        ]);
        $company = Company::create([
            'name' => $companyName,
            'owner_id' => $user->id,
        ]);
        $user->update(['company_id' => $company->id]);

        return [$user->fresh(), $company];
    }

    private function planningManager(Company $company): User
    {
        return User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'planning_manager'])->id,
            'company_id' => $company->id,
        ]);
    }

    private function project(User $owner, string $title): Project
    {
        return Project::create([
            'hiring_company_id' => $owner->company_id,
            'created_by' => $owner->id,
            'title' => $title,
            'description' => 'Project description',
            'city' => 'Calgary',
            'province' => 'Alberta',
            'country' => 'Canada',
            'job_type' => 'Electrician',
            'workers' => 8,
            'start_date' => today()->addWeek(),
            'end_date' => today()->addWeeks(2),
            'hourly_rate' => 40,
            'status' => 'open',
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

    private function selfEmployedWorker(User $user): WorkerProfile
    {
        return WorkerProfile::create([
            'user_id' => $user->id,
            'company_id' => null,
            'name' => 'Independent worker',
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);
    }

    private function request(
        Project $project,
        User $requestedBy,
        ?Company $company,
        WorkerProfile $worker,
        string $status = 'pending',
        string $type = 'apply',
    ): WorkerRequest {
        return WorkerRequest::create([
            'project_id' => $project->id,
            'requested_by' => $requestedBy->id,
            'company_id' => $company?->id,
            'worker_profile_id' => $worker->id,
            'type' => $type,
            'status' => $status,
        ]);
    }
}
