<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Mission;
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
        [$mission, $manager] = $this->missionAndManager(3);

        foreach (['accepted', 'ongoing', 'completed', 'pending', 'rejected', 'cancelled'] as $index => $status) {
            $this->createRequest($mission, $manager, $status, "Worker {$index}");
        }

        $mission = Mission::withCommittedWorkerCount()->findOrFail($mission->id);

        $this->assertSame(3, $mission->workers);
        $this->assertSame(3, $mission->committed_worker_count);
        $this->assertSame(0, $mission->remaining_capacity);
    }

    public function test_pending_rejected_and_cancelled_requests_do_not_consume_capacity(): void
    {
        [$mission, $manager] = $this->missionAndManager(3);

        foreach (['pending', 'rejected', 'cancelled'] as $index => $status) {
            $this->createRequest($mission, $manager, $status, "Worker {$index}");
        }

        $mission = Mission::withCommittedWorkerCount()->findOrFail($mission->id);

        $this->assertSame(0, $mission->committed_worker_count);
        $this->assertSame(3, $mission->remaining_capacity);
    }

    public function test_recruiting_closed_at_distinguishes_open_recruiting_from_closed_recruiting(): void
    {
        [$mission] = $this->missionAndManager(2);

        $this->assertTrue($mission->isRecruitingOpen());
        $this->assertTrue($mission->isActionableForStaffing());
        $this->assertTrue(Mission::actionableForStaffing()->whereKey($mission->id)->exists());

        $mission->update(['recruiting_closed_at' => now()]);
        $mission->refresh();

        $this->assertFalse($mission->isRecruitingOpen());
        $this->assertFalse($mission->isActionableForStaffing());
        $this->assertFalse(Mission::actionableForStaffing()->whereKey($mission->id)->exists());
    }

    public function test_multiple_workers_can_each_have_one_rating_for_the_same_mission(): void
    {
        [$mission, $manager] = $this->missionAndManager(2);
        $firstWorker = $this->workerForCompany($manager->company_id, 'First Worker');
        $secondWorker = $this->workerForCompany($manager->company_id, 'Second Worker');

        Rating::create($this->ratingData($mission, $manager, $firstWorker));
        Rating::create($this->ratingData($mission, $manager, $secondWorker));

        $this->assertDatabaseCount('ratings', 2);
    }

    public function test_duplicate_rating_for_the_same_mission_and_worker_is_rejected(): void
    {
        [$mission, $manager] = $this->missionAndManager(1);
        $worker = $this->workerForCompany($manager->company_id, 'Rated Worker');

        Rating::create($this->ratingData($mission, $manager, $worker));

        $this->expectException(QueryException::class);

        Rating::create($this->ratingData($mission, $manager, $worker));
    }

    public function test_one_worker_cannot_have_multiple_request_paths_for_the_same_mission(): void
    {
        [$mission, $manager] = $this->missionAndManager(2);
        $worker = $this->workerForCompany($manager->company_id, 'Requested Worker');

        WorkerRequest::create($this->requestData($mission, $manager, $worker, 'invite', 'pending'));

        $this->expectException(QueryException::class);

        WorkerRequest::create($this->requestData($mission, $manager, $worker, 'apply', 'rejected'));
    }

    private function missionAndManager(int $capacity): array
    {
        $role = Role::create(['name' => 'company_owner']);
        $manager = User::factory()->create(['role_id' => $role->id]);
        $company = Company::create([
            'name' => 'Test Construction Ltd.',
            'owner_id' => $manager->id,
        ]);
        $manager->update(['company_id' => $company->id]);

        $mission = Mission::create([
            'hiring_company_id' => $company->id,
            'created_by' => $manager->id,
            'title' => 'Multi-worker mission',
            'city' => 'Calgary',
            'province' => 'Alberta',
            'job_type' => 'Electrician',
            'workers' => $capacity,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
            'status' => 'open',
        ]);

        return [$mission, $manager->fresh()];
    }

    private function createRequest(Mission $mission, User $manager, string $status, string $name): WorkerRequest
    {
        $worker = $this->workerForCompany($manager->company_id, $name);

        return WorkerRequest::create($this->requestData($mission, $manager, $worker, 'apply', $status));
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
        Mission $mission,
        User $manager,
        WorkerProfile $worker,
        string $type,
        string $status,
    ): array {
        return [
            'mission_id' => $mission->id,
            'requested_by' => $manager->id,
            'company_id' => $manager->company_id,
            'worker_profile_id' => $worker->id,
            'type' => $type,
            'status' => $status,
        ];
    }

    private function ratingData(Mission $mission, User $manager, WorkerProfile $worker): array
    {
        return [
            'mission_id' => $mission->id,
            'reviewed_by_user_id' => $manager->id,
            'worker_profile_id' => $worker->id,
            'score' => 5,
            'feedback' => 'Reliable work.',
        ];
    }
}
