<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Mission;
use App\Models\Rating;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionDeletionArchiveSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unused_unarchived_draft_can_be_permanently_deleted(): void
    {
        [$manager] = $this->companyManager('Hiring Company');
        $mission = $this->mission($manager, 'Unused draft', ['status' => 'draft']);
        $mission->requirements()->create(['name' => 'Safety training']);

        $this->actingAs($manager)
            ->delete(route('missions.destroy', $mission))
            ->assertRedirect(route('missions.index'));

        $this->assertDatabaseMissing('missions', ['id' => $mission->id]);
        $this->assertDatabaseMissing('mission_requirements', ['mission_id' => $mission->id]);
    }

    public function test_a_draft_with_request_history_cannot_be_permanently_deleted(): void
    {
        [$manager, $company] = $this->companyManager('Hiring Company');
        $mission = $this->mission($manager, 'Draft with request history', ['status' => 'draft']);
        $request = $this->request($mission, $manager, $company, $this->worker($company, 'Requested worker'), 'pending');

        $this->assertDeletionIsRefused($manager, $mission);
        $this->assertDatabaseHas('requests', ['id' => $request->id]);
    }

    public function test_a_draft_with_rating_history_cannot_be_permanently_deleted(): void
    {
        [$manager, $company] = $this->companyManager('Hiring Company');
        $mission = $this->mission($manager, 'Draft with rating history', ['status' => 'draft']);
        $worker = $this->worker($company, 'Rated worker');
        $rating = Rating::create([
            'mission_id' => $mission->id,
            'reviewed_by_user_id' => $manager->id,
            'worker_profile_id' => $worker->id,
            'score' => 5,
            'feedback' => 'Historical rating.',
        ]);

        $this->assertDeletionIsRefused($manager, $mission);
        $this->assertDatabaseHas('ratings', ['id' => $rating->id]);
    }

    public function test_non_draft_missions_and_archived_missions_cannot_be_permanently_deleted(): void
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
            $mission = $this->mission($manager, "Protected {$status} mission", [
                'status' => $status,
                ...$overrides,
            ]);

            $this->assertDeletionIsRefused($manager, $mission);
        }
    }

    public function test_archiving_a_completed_mission_preserves_all_related_history(): void
    {
        [$hiringManager, $hiringCompany] = $this->companyManager('Hiring Company');
        [$lendingManager, $lendingCompany] = $this->companyManager('Lending Company');
        $mission = $this->mission($hiringManager, 'Completed historical mission', ['status' => 'completed']);
        $mission->requirements()->createMany([
            ['name' => 'First Aid'],
            ['name' => 'Fall Protection'],
        ]);

        $completedWorker = $this->worker($lendingCompany, 'Completed worker');
        $endedEarlyWorker = $this->worker($lendingCompany, 'Ended early worker');
        $rejectedWorker = $this->worker($lendingCompany, 'Rejected worker');

        $completedRequest = $this->request($mission, $lendingManager, $lendingCompany, $completedWorker, 'completed', [
            'message' => 'Original completed application.',
            'responded_at' => now()->subDays(3),
            'completed_at' => now()->subDay(),
        ]);
        $endedEarlyRequest = $this->request($mission, $lendingManager, $lendingCompany, $endedEarlyWorker, 'ended_early', [
            'ended_at' => now()->subDay(),
        ]);
        $rejectedRequest = $this->request($mission, $lendingManager, $lendingCompany, $rejectedWorker, 'rejected', [
            'message' => 'Original rejected application.',
            'rejection_message' => 'This application was not selected.',
            'responded_at' => now()->subDays(2),
            'responded_by' => $hiringManager->id,
        ]);
        $rating = Rating::create([
            'mission_id' => $mission->id,
            'reviewed_by_user_id' => $hiringManager->id,
            'worker_profile_id' => $completedWorker->id,
            'score' => 5,
            'feedback' => 'Excellent work.',
        ]);

        $this->actingAs($hiringManager)
            ->put(route('missions.archive', $mission))
            ->assertRedirect(route('missions.index'));

        $this->assertNotNull($mission->fresh()->archived_at);
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
            'mission_id' => $mission->id,
            'worker_profile_id' => $completedWorker->id,
            'reviewed_by_user_id' => $hiringManager->id,
        ]);
        $this->assertDatabaseCount('mission_requirements', 2);
    }

    public function test_non_completed_and_already_archived_missions_cannot_be_archived(): void
    {
        [$manager] = $this->companyManager('Hiring Company');

        foreach (['draft', 'open', 'in_progress', 'cancelled'] as $status) {
            $mission = $this->mission($manager, "{$status} mission", ['status' => $status]);

            $this->actingAs($manager)
                ->from(route('missions.index'))
                ->put(route('missions.archive', $mission))
                ->assertSessionHasErrors('mission');

            $this->assertNull($mission->fresh()->archived_at);
        }

        $archivedMission = $this->mission($manager, 'Already archived mission', [
            'status' => 'completed',
            'archived_at' => now()->subDay(),
        ]);

        $this->actingAs($manager)
            ->from(route('missions.index'))
            ->put(route('missions.archive', $archivedMission))
            ->assertSessionHasErrors('mission');

        $this->assertNotNull($archivedMission->fresh()->archived_at);
    }

    public function test_unrelated_company_and_self_employed_users_cannot_manage_missions(): void
    {
        [$owner] = $this->companyManager('Hiring Company');
        [$unrelated] = $this->companyManager('Unrelated Company');
        $mission = $this->mission($owner, 'Protected draft', ['status' => 'draft']);
        $selfEmployed = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'self_employed'])->id,
            'company_id' => null,
        ]);

        $this->actingAs($unrelated)
            ->delete(route('missions.destroy', $mission))
            ->assertForbidden();
        $this->actingAs($selfEmployed)
            ->delete(route('missions.destroy', $mission))
            ->assertForbidden();

        $this->assertDatabaseHas('missions', ['id' => $mission->id]);
    }

    public function test_administrator_cannot_bypass_retention_rules_on_the_delete_endpoint(): void
    {
        [$owner] = $this->companyManager('Hiring Company');
        $mission = $this->mission($owner, 'Unsafe open mission', ['status' => 'open']);
        $administrator = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'administrator'])->id,
        ]);

        $this->actingAs($administrator)
            ->from(route('missions.index'))
            ->delete(route('missions.destroy', $mission))
            ->assertSessionHasErrors('mission');

        $this->assertDatabaseHas('missions', ['id' => $mission->id]);
    }

    private function assertDeletionIsRefused(User $manager, Mission $mission): void
    {
        $this->actingAs($manager)
            ->from(route('missions.index'))
            ->delete(route('missions.destroy', $mission))
            ->assertSessionHasErrors('mission');

        $this->assertDatabaseHas('missions', ['id' => $mission->id]);
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
            'description' => 'Mission history retention coverage.',
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
        Mission $mission,
        User $requestedBy,
        Company $company,
        WorkerProfile $worker,
        string $status,
        array $overrides = [],
    ): WorkerRequest {
        return WorkerRequest::create([
            'mission_id' => $mission->id,
            'requested_by' => $requestedBy->id,
            'company_id' => $company->id,
            'worker_profile_id' => $worker->id,
            'type' => 'apply',
            'status' => $status,
            ...$overrides,
        ]);
    }
}
