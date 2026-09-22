<?php

namespace Tests\Feature;

use App\Models\Availability;
use App\Models\Certification;
use App\Models\Company;
use App\Models\Mission;
use App\Models\Rating;
use App\Models\Role;
use App\Models\Skill;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkerProfileRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_unused_profiles_and_profiles_with_only_operational_data_can_be_deleted(): void
    {
        [$manager, $company] = $this->companyManager('Hiring Company');

        $unused = $this->worker($company, 'Unused worker');
        $withTags = $this->worker($company, 'Tagged worker');
        $withTags->skills()->attach(Skill::firstOrCreate(['name' => 'Welding'])->id);
        $withTags->certifications()->attach(Certification::firstOrCreate(['name' => 'First Aid'])->id);
        $withAvailability = $this->worker($company, 'Available worker');
        Availability::create([
            'worker_profile_id' => $withAvailability->id,
            'date' => '2026-10-01',
            'start_time' => '07:00',
            'end_time' => '15:00',
            'status' => 'available',
        ]);

        $this->assertTrue(Schema::hasColumn('worker_profiles', 'archived_at'));

        foreach ([$unused, $withTags, $withAvailability] as $worker) {
            $this->actingAs($manager)
                ->delete(route('worker-profiles.destroy', $worker))
                ->assertRedirect(route('worker-profiles.index'));

            $this->assertDatabaseMissing('worker_profiles', ['id' => $worker->id]);
        }

        $this->assertDatabaseMissing('availabilities', ['worker_profile_id' => $withAvailability->id]);
    }

    public function test_business_history_blocks_permanent_deletion_for_requests_ratings_and_direct_missions(): void
    {
        [$manager, $company] = $this->companyManager('Hiring Company');
        $mission = $this->mission($manager, 'Historical mission', ['status' => 'completed']);

        $requested = $this->worker($company, 'Requested worker');
        $this->request($mission, $manager, $company, $requested, 'rejected');

        $rated = $this->worker($company, 'Rated worker');
        Rating::create([
            'mission_id' => $mission->id,
            'reviewed_by_user_id' => $manager->id,
            'worker_profile_id' => $rated->id,
            'score' => 5,
        ]);

        $directMissionWorker = $this->worker($company, 'Legacy mission worker');
        $this->mission($manager, 'Legacy worker mission', [
            'status' => 'completed',
            'worker_profile_id' => $directMissionWorker->id,
        ]);

        foreach ([$requested, $rated, $directMissionWorker] as $worker) {
            $this->actingAs($manager)
                ->from(route('worker-profiles.index'))
                ->delete(route('worker-profiles.destroy', $worker))
                ->assertSessionHasErrors('worker_profile');

            $this->assertDatabaseHas('worker_profiles', ['id' => $worker->id]);
        }
    }

    public function test_profiles_with_resolved_history_can_be_archived_and_preserve_history(): void
    {
        [$manager, $company] = $this->companyManager('Hiring Company');
        $mission = $this->mission($manager, 'Resolved history mission', ['status' => 'completed']);
        $worker = $this->worker($company, 'Historical worker');
        $request = $this->request($mission, $manager, $company, $worker, 'completed', [
            'message' => 'Original application.',
            'completed_at' => now()->subDay(),
        ]);
        $rating = Rating::create([
            'mission_id' => $mission->id,
            'reviewed_by_user_id' => $manager->id,
            'worker_profile_id' => $worker->id,
            'score' => 5,
            'feedback' => 'Excellent work.',
        ]);
        $rejectedMission = $this->mission($manager, 'Rejected history mission', ['status' => 'completed']);
        $rejectedRequest = $this->request($rejectedMission, $manager, $company, $worker, 'rejected', [
            'message' => 'Original rejected application.',
            'rejection_message' => 'This application was not selected.',
            'responded_by' => $manager->id,
            'responded_at' => now()->subDays(2),
        ]);

        $this->actingAs($manager)
            ->put(route('worker-profiles.archive', $worker))
            ->assertRedirect(route('worker-profiles.index'));

        $this->assertNotNull($worker->fresh()->archived_at);
        $this->assertDatabaseHas('worker_profiles', [
            'id' => $worker->id,
            'company_id' => $company->id,
        ]);
        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'worker_profile_id' => $worker->id,
            'message' => 'Original application.',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('ratings', [
            'id' => $rating->id,
            'worker_profile_id' => $worker->id,
            'reviewed_by_user_id' => $manager->id,
        ]);
        $this->assertDatabaseHas('requests', [
            'id' => $rejectedRequest->id,
            'worker_profile_id' => $worker->id,
            'message' => 'Original rejected application.',
            'rejection_message' => 'This application was not selected.',
            'responded_by' => $manager->id,
        ]);
        $this->assertDatabaseHas('missions', ['id' => $mission->id]);
    }

    public function test_rejected_cancelled_completed_and_ended_early_requests_allow_archival(): void
    {
        [$manager, $company] = $this->companyManager('Hiring Company');

        foreach (['rejected', 'cancelled', 'completed', 'ended_early'] as $status) {
            $mission = $this->mission($manager, "{$status} mission", ['status' => 'completed']);
            $worker = $this->worker($company, "{$status} worker");
            $this->request($mission, $manager, $company, $worker, $status);

            $this->assertTrue($worker->canBeArchived());
            $this->actingAs($manager)
                ->put(route('worker-profiles.archive', $worker))
                ->assertRedirect(route('worker-profiles.index'));
        }
    }

    public function test_active_requests_and_direct_active_missions_block_archival(): void
    {
        [$manager, $company] = $this->companyManager('Hiring Company');

        foreach (['pending', 'accepted', 'ongoing'] as $status) {
            $worker = $this->worker($company, "{$status} worker");
            $mission = $this->mission($manager, "{$status} mission", ['status' => 'open']);
            $this->request($mission, $manager, $company, $worker, $status);

            $this->assertArchiveIsRefused($manager, $worker);
        }

        $legacyWorker = $this->worker($company, 'Active legacy mission worker');
        $this->mission($manager, 'Active direct mission', [
            'status' => 'in_progress',
            'worker_profile_id' => $legacyWorker->id,
        ]);

        $this->assertArchiveIsRefused($manager, $legacyWorker);
    }

    public function test_already_archived_workers_cannot_be_archived_or_deleted(): void
    {
        [$manager, $company] = $this->companyManager('Hiring Company');
        $worker = $this->worker($company, 'Archived worker', ['archived_at' => now()->subDay()]);

        $this->actingAs($manager)
            ->from(route('worker-profiles.index'))
            ->put(route('worker-profiles.archive', $worker))
            ->assertSessionHasErrors('worker_profile');

        $this->actingAs($manager)
            ->from(route('worker-profiles.index'))
            ->delete(route('worker-profiles.destroy', $worker))
            ->assertSessionHasErrors('worker_profile');

        $this->assertNotNull($worker->fresh()->archived_at);
    }

    public function test_archived_workers_are_excluded_from_normal_profiles_and_cannot_be_edited(): void
    {
        [$manager, $company] = $this->companyManager('Hiring Company');
        $activeWorker = $this->worker($company, 'Active worker');
        $archivedWorker = $this->worker($company, 'Archived worker', ['archived_at' => now()]);

        $this->actingAs($manager)
            ->get(route('worker-profiles.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('WorkerProfiles')
                ->has('workerProfiles.data', 1)
                ->where('workerProfiles.data.0.id', $activeWorker->id)
            );

        $this->actingAs($manager)
            ->from(route('worker-profiles.index'))
            ->put(route('worker-profiles.update', $archivedWorker), [
                'name' => 'Changed archived worker',
                'job' => 'Electrician',
                'experience' => 6,
                'rate' => 45,
                'certifications' => [],
                'skills' => [],
            ])
            ->assertSessionHasErrors('worker_profile');

        $this->assertSame('Archived worker', $archivedWorker->fresh()->name);
    }

    public function test_policy_and_retention_guards_apply_to_direct_requests_and_administrators(): void
    {
        [$manager, $company] = $this->companyManager('Hiring Company');
        [$unrelated] = $this->companyManager('Unrelated Company');
        $worker = $this->worker($company, 'Protected worker');

        $this->actingAs($unrelated)
            ->delete(route('worker-profiles.destroy', $worker))
            ->assertForbidden();
        $this->actingAs($unrelated)
            ->put(route('worker-profiles.archive', $worker))
            ->assertForbidden();

        $mission = $this->mission($manager, 'Admin retention mission', ['status' => 'open']);
        $this->request($mission, $manager, $company, $worker, 'pending');
        $administrator = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'administrator'])->id,
        ]);

        $this->actingAs($administrator)
            ->from(route('worker-profiles.index'))
            ->delete(route('worker-profiles.destroy', $worker))
            ->assertSessionHasErrors('worker_profile');
        $this->actingAs($administrator)
            ->from(route('worker-profiles.index'))
            ->put(route('worker-profiles.archive', $worker))
            ->assertSessionHasErrors('worker_profile');

        $this->assertDatabaseHas('worker_profiles', ['id' => $worker->id]);
    }

    public function test_worker_profiles_page_exposes_backend_derived_retention_capabilities(): void
    {
        [$manager, $company] = $this->companyManager('Hiring Company');
        $unused = $this->worker($company, 'A Deletable worker');
        $historical = $this->worker($company, 'B Archivable worker');
        $mission = $this->mission($manager, 'Completed archive source', ['status' => 'completed']);
        $this->request($mission, $manager, $company, $historical, 'completed');
        $archived = $this->worker($company, 'C Archived worker', ['archived_at' => now()]);

        $this->actingAs($manager)
            ->get(route('worker-profiles.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('WorkerProfiles')
                ->where('workerProfiles.data.0.can_delete', true)
                ->where('workerProfiles.data.0.can_archive', false)
                ->where('workerProfiles.data.1.can_delete', false)
                ->where('workerProfiles.data.1.can_archive', true));

        $this->assertSame([$archived->id], WorkerProfile::archived()->pluck('id')->all());
    }

    private function assertArchiveIsRefused(User $manager, WorkerProfile $worker): void
    {
        $this->actingAs($manager)
            ->from(route('worker-profiles.index'))
            ->put(route('worker-profiles.archive', $worker))
            ->assertSessionHasErrors('worker_profile');

        $this->assertNull($worker->fresh()->archived_at);
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
            'description' => 'Worker profile retention coverage.',
            'city' => 'Calgary',
            'province' => 'Alberta',
            'country' => 'Canada',
            'job_type' => 'Electrician',
            'workers' => 1,
            'start_date' => today()->subWeek()->toDateString(),
            'end_date' => today()->toDateString(),
            'status' => 'open',
            ...$overrides,
        ]);
    }

    private function worker(Company $company, string $name, array $overrides = []): WorkerProfile
    {
        return WorkerProfile::create([
            'company_id' => $company->id,
            'name' => $name,
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 40,
            ...$overrides,
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
