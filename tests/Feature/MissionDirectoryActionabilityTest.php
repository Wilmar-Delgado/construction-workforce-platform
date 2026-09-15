<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Mission;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MissionDirectoryActionabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_missions_exposes_only_actionable_external_missions_with_staffing_counts(): void
    {
        [$viewer] = $this->companyManager('Viewer Company Ltd.');
        [$owner] = $this->companyManager('Hiring Company Ltd.');

        $partial = $this->mission($owner, 'Partially staffed', 3);
        $this->committedRequest($partial, $owner, 'accepted');

        $full = $this->mission($owner, 'Fully staffed', 1);
        $this->committedRequest($full, $owner, 'accepted');

        $closed = $this->mission($owner, 'Closed recruiting', 3, ['recruiting_closed_at' => now()]);
        $started = $this->mission($owner, 'Started mission', 3, ['status' => 'in_progress']);

        $this->actingAs($viewer)
            ->get(route('find-missions.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('FindMissions')
                ->has('missions.data', 1)
                ->where('missions.data.0.id', $partial->id)
                ->where('missions.data.0.committed_worker_count', 1)
                ->where('missions.data.0.remaining_capacity', 2)
                ->missing('missions.data.1')
            );

        $this->assertNotSame($partial->id, $full->id);
        $this->assertNotSame($partial->id, $closed->id);
        $this->assertNotSame($partial->id, $started->id);
    }

    public function test_find_workers_invitation_selector_excludes_non_actionable_missions(): void
    {
        [$manager] = $this->companyManager('Hiring Company Ltd.');

        $partial = $this->mission($manager, 'Partially staffed', 3);
        $this->committedRequest($partial, $manager, 'accepted');

        $full = $this->mission($manager, 'Fully staffed', 1);
        $this->committedRequest($full, $manager, 'accepted');
        $draft = $this->mission($manager, 'Draft mission', 3, ['status' => 'draft']);
        $closed = $this->mission($manager, 'Closed mission', 3, ['recruiting_closed_at' => now()]);
        $started = $this->mission($manager, 'Started mission', 3, ['status' => 'in_progress']);

        $this->actingAs($manager)
            ->get(route('find-workers.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('FindWorkers')
                ->has('missions', 1)
                ->where('missions.0.id', $partial->id)
                ->where('missions.0.committed_worker_count', 1)
                ->where('missions.0.remaining_capacity', 2)
            );

        $this->assertNotSame($partial->id, $full->id);
        $this->assertNotSame($partial->id, $draft->id);
        $this->assertNotSame($partial->id, $closed->id);
        $this->assertNotSame($partial->id, $started->id);
    }

    public function test_find_missions_reports_existing_request_history_only_for_the_viewers_workers_and_current_missions(): void
    {
        [$viewer, $viewerCompany] = $this->companyManager('Viewer Company Ltd.');
        [$owner] = $this->companyManager('Hiring Company Ltd.');
        [$otherManager, $otherCompany] = $this->companyManager('Other Company Ltd.');

        $firstMission = $this->mission($owner, 'Office electrical upgrade', 4);
        $secondMission = $this->mission($owner, 'Warehouse electrical upgrade', 4);

        $invitedWorker = $this->worker($viewerCompany, 'Invited worker');
        $appliedWorker = $this->worker($viewerCompany, 'Applied worker');
        $historicalWorker = $this->worker($viewerCompany, 'Historical worker');
        $availableWorker = $this->worker($viewerCompany, 'Available worker');
        $unrelatedWorker = $this->worker($otherCompany, 'Unrelated worker');

        WorkerRequest::create([
            'mission_id' => $firstMission->id,
            'requested_by' => $owner->id,
            'company_id' => $owner->company_id,
            'worker_profile_id' => $invitedWorker->id,
            'type' => 'invite',
            'status' => 'pending',
        ]);

        WorkerRequest::create([
            'mission_id' => $secondMission->id,
            'requested_by' => $viewer->id,
            'company_id' => $viewer->company_id,
            'worker_profile_id' => $appliedWorker->id,
            'type' => 'apply',
            'status' => 'rejected',
        ]);

        WorkerRequest::create([
            'mission_id' => $firstMission->id,
            'requested_by' => $viewer->id,
            'company_id' => $viewer->company_id,
            'worker_profile_id' => $historicalWorker->id,
            'type' => 'apply',
            'status' => 'cancelled',
        ]);

        WorkerRequest::create([
            'mission_id' => $firstMission->id,
            'requested_by' => $otherManager->id,
            'company_id' => $otherManager->company_id,
            'worker_profile_id' => $unrelatedWorker->id,
            'type' => 'apply',
            'status' => 'pending',
        ]);

        $this->actingAs($viewer)
            ->get(route('find-missions.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('FindMissions')
                ->has('existingRequests', 3)
                ->where('existingRequests.0.mission_id', $firstMission->id)
                ->where('existingRequests.0.worker_profile_id', $invitedWorker->id)
                ->where('existingRequests.0.type', 'invite')
                ->where('existingRequests.0.status', 'pending')
                ->where('existingRequests.1.mission_id', $secondMission->id)
                ->where('existingRequests.1.worker_profile_id', $appliedWorker->id)
                ->where('existingRequests.1.type', 'apply')
                ->where('existingRequests.1.status', 'rejected')
                ->where('existingRequests.2.mission_id', $firstMission->id)
                ->where('existingRequests.2.worker_profile_id', $historicalWorker->id)
                ->where('existingRequests.2.status', 'cancelled')
                ->missing('existingRequests.3')
            );

        $this->assertNotSame($availableWorker->id, $invitedWorker->id);
        $this->assertNotSame($availableWorker->id, $appliedWorker->id);
        $this->assertNotSame($availableWorker->id, $historicalWorker->id);
        $this->assertNotSame($availableWorker->id, $unrelatedWorker->id);
    }

    public function test_self_employed_users_receive_existing_request_statuses_without_losing_mission_visibility(): void
    {
        [$owner] = $this->companyManager('Hiring Company Ltd.');
        $selfEmployedUser = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'self_employed'])->id,
            'company_id' => null,
        ]);
        $worker = WorkerProfile::create([
            'user_id' => $selfEmployedUser->id,
            'company_id' => null,
            'name' => 'Independent worker',
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);

        $requestStates = [
            ['type' => 'apply', 'status' => 'pending'],
            ['type' => 'invite', 'status' => 'pending'],
            ['type' => 'apply', 'status' => 'accepted'],
            ['type' => 'apply', 'status' => 'ongoing'],
            ['type' => 'apply', 'status' => 'completed'],
            ['type' => 'apply', 'status' => 'ended_early'],
        ];

        foreach ($requestStates as $index => $state) {
            $mission = $this->mission($owner, "Existing request mission {$index}", 2);

            WorkerRequest::create([
                'mission_id' => $mission->id,
                'requested_by' => $selfEmployedUser->id,
                'company_id' => null,
                'worker_profile_id' => $worker->id,
                'type' => $state['type'],
                'status' => $state['status'],
            ]);
        }

        $availableMission = $this->mission($owner, 'Available mission', 2);

        $this->actingAs($selfEmployedUser)
            ->get(route('find-missions.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('FindMissions')
                ->has('missions.data', 7)
                ->has('existingRequests', 6)
                ->where('existingRequests.0.type', 'apply')
                ->where('existingRequests.0.status', 'pending')
                ->where('existingRequests.1.type', 'invite')
                ->where('existingRequests.1.status', 'pending')
                ->where('existingRequests.2.status', 'accepted')
                ->where('existingRequests.3.status', 'ongoing')
                ->where('existingRequests.4.status', 'completed')
                ->where('existingRequests.5.status', 'ended_early')
            );

        $this->assertNotNull($availableMission->id);
    }

    private function companyManager(string $companyName): array
    {
        $role = Role::firstOrCreate(['name' => 'company_owner']);
        $manager = User::factory()->create(['role_id' => $role->id]);
        $company = Company::create(['name' => $companyName, 'owner_id' => $manager->id]);
        $manager->update(['company_id' => $company->id]);

        return [$manager->fresh(), $company];
    }

    private function mission(User $owner, string $title, int $workers, array $overrides = []): Mission
    {
        return Mission::create([
            'hiring_company_id' => $owner->company_id,
            'created_by' => $owner->id,
            'title' => $title,
            'city' => 'Calgary',
            'province' => 'Alberta',
            'job_type' => 'Electrician',
            'workers' => $workers,
            'start_date' => today()->addWeek()->toDateString(),
            'end_date' => today()->addWeeks(2)->toDateString(),
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

    private function committedRequest(Mission $mission, User $missionOwner, string $status): WorkerRequest
    {
        $worker = WorkerProfile::create([
            'company_id' => $missionOwner->company_id,
            'name' => 'Committed worker '.WorkerProfile::query()->count(),
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
        ]);

        return WorkerRequest::create([
            'mission_id' => $mission->id,
            'requested_by' => $missionOwner->id,
            'company_id' => $missionOwner->company_id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => $status,
        ]);
    }
}
