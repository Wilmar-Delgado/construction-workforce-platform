<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\Rating;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiWorkerStaffingFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_requested_capacity_is_preserved_and_remaining_capacity_is_calculated_from_committed_requests(): void
    {
        [$project, $manager] = $this->projectAndManager(3);

        foreach (['accepted', 'ongoing', 'completed', 'pending', 'rejected', 'cancelled'] as $index => $status) {
            $this->createRequest($project, $manager, $status, "Worker {$index}");
        }

        $project = Project::withCommittedWorkerCount()->findOrFail($project->id);

        $this->assertSame(3, $project->workers);
        $this->assertSame(3, $project->committed_worker_count);
        $this->assertSame(0, $project->remaining_capacity);
    }

    public function test_pending_rejected_and_cancelled_requests_do_not_consume_capacity(): void
    {
        [$project, $manager] = $this->projectAndManager(3);

        foreach (['pending', 'rejected', 'cancelled'] as $index => $status) {
            $this->createRequest($project, $manager, $status, "Worker {$index}");
        }

        $project = Project::withCommittedWorkerCount()->findOrFail($project->id);

        $this->assertSame(0, $project->committed_worker_count);
        $this->assertSame(3, $project->remaining_capacity);
    }

    public function test_recruiting_closed_at_distinguishes_open_recruiting_from_closed_recruiting(): void
    {
        [$project] = $this->projectAndManager(2);

        $this->assertTrue($project->isRecruitingOpen());
        $this->assertTrue($project->isActionableForStaffing());
        $this->assertTrue(Project::actionableForStaffing()->whereKey($project->id)->exists());

        $project->update(['recruiting_closed_at' => now()]);
        $project->refresh();

        $this->assertFalse($project->isRecruitingOpen());
        $this->assertFalse($project->isActionableForStaffing());
        $this->assertFalse(Project::actionableForStaffing()->whereKey($project->id)->exists());
    }

    public function test_multiple_workers_can_each_have_one_rating_for_the_same_project(): void
    {
        [$project, $manager] = $this->projectAndManager(2);
        $firstWorker = $this->workerForCompany($manager->company_id, 'First Worker');
        $secondWorker = $this->workerForCompany($manager->company_id, 'Second Worker');

        Rating::create($this->ratingData($project, $manager, $firstWorker));
        Rating::create($this->ratingData($project, $manager, $secondWorker));

        $this->assertDatabaseCount('ratings', 2);
    }

    public function test_duplicate_rating_for_the_same_project_and_worker_is_rejected(): void
    {
        [$project, $manager] = $this->projectAndManager(1);
        $worker = $this->workerForCompany($manager->company_id, 'Rated Worker');

        Rating::create($this->ratingData($project, $manager, $worker));

        $this->expectException(QueryException::class);

        Rating::create($this->ratingData($project, $manager, $worker));
    }

    public function test_one_worker_cannot_have_multiple_request_paths_for_the_same_project(): void
    {
        [$project, $manager] = $this->projectAndManager(2);
        $worker = $this->workerForCompany($manager->company_id, 'Requested Worker');

        WorkerRequest::create($this->requestData($project, $manager, $worker, 'invite', 'pending'));

        $this->expectException(QueryException::class);

        WorkerRequest::create($this->requestData($project, $manager, $worker, 'apply', 'rejected'));
    }

    private function projectAndManager(int $capacity): array
    {
        $role = Role::create(['name' => 'company_owner']);
        $manager = User::factory()->create(['role_id' => $role->id]);
        $company = Company::create([
            'name' => 'Test Construction Ltd.',
            'owner_id' => $manager->id,
        ]);
        $manager->update(['company_id' => $company->id]);

        $project = Project::create([
            'hiring_company_id' => $company->id,
            'created_by' => $manager->id,
            'title' => 'Multi-worker project',
            'city' => 'Calgary',
            'province' => 'Alberta',
            'job_type' => 'Electrician',
            'workers' => $capacity,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
            'status' => 'open',
        ]);

        return [$project, $manager->fresh()];
    }

    private function createRequest(Project $project, User $manager, string $status, string $name): WorkerRequest
    {
        $worker = $this->workerForCompany($manager->company_id, $name);

        return WorkerRequest::create($this->requestData($project, $manager, $worker, 'apply', $status));
    }

    private function workerForCompany(int $companyId, string $name): WorkerProfile
    {
        return WorkerProfile::create([
            'company_id' => $companyId,
            'name' => $name,
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);
    }

    private function requestData(
        Project $project,
        User $manager,
        WorkerProfile $worker,
        string $type,
        string $status,
    ): array {
        return [
            'project_id' => $project->id,
            'requested_by' => $manager->id,
            'company_id' => $manager->company_id,
            'worker_profile_id' => $worker->id,
            'type' => $type,
            'status' => $status,
        ];
    }

    private function ratingData(Project $project, User $manager, WorkerProfile $worker): array
    {
        return [
            'project_id' => $project->id,
            'reviewed_by_user_id' => $manager->id,
            'worker_profile_id' => $worker->id,
            'score' => 5,
            'feedback' => 'Reliable work.',
        ];
    }
}
