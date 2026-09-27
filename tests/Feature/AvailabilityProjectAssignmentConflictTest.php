<?php

namespace Tests\Feature;

use App\Models\Availability;
use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AvailabilityProjectAssignmentConflictTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('activeRequestTypes')]
    public function test_active_assignments_block_availability_creation_for_invites_and_applications(string $type): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        $this->createAssignment($manager, $worker, $type, '2026-10-15', '2026-10-17');

        $response = $this->actingAs($manager)->from(route('availability.index'))->post(route('availability.store'), [
            ...$this->availabilityData($worker, '2026-10-16'),
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseCount('availabilities', 0);
    }

    public static function activeRequestTypes(): array
    {
        return [
            'accepted invitation' => ['invite'],
            'accepted application' => ['apply'],
        ];
    }

    public function test_an_accepted_worker_on_an_open_project_cannot_create_availability_during_project_dates(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        $this->createAssignment($manager, $worker, 'apply', '2026-10-15', '2026-10-17', 'accepted', 'open');

        $this->actingAs($manager)->from(route('availability.index'))->post(route('availability.store'), [
            ...$this->availabilityData($worker, '2026-10-16'),
        ])->assertSessionHasErrors('date');

        $this->assertDatabaseCount('availabilities', 0);
    }

    public function test_an_active_assignment_blocks_each_date_in_its_inclusive_project_range(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        $this->createAssignment($manager, $worker, 'apply', '2026-10-15', '2026-10-17', 'accepted', 'open');

        foreach (['2026-10-15', '2026-10-16', '2026-10-17'] as $date) {
            $response = $this->actingAs($manager)->from(route('availability.index'))->post(route('availability.store'), [
                ...$this->availabilityData($worker, $date),
            ]);

            $response->assertSessionHasErrors('date');
        }

        $this->assertDatabaseCount('availabilities', 0);
    }

    public function test_dates_immediately_before_and_after_an_accepted_open_project_are_allowed(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        $this->createAssignment($manager, $worker, 'invite', '2026-10-15', '2026-10-17', 'accepted', 'open');

        $this->actingAs($manager)->post(route('availability.store'), [
            ...$this->availabilityData($worker, '2026-10-14'),
        ])->assertSessionHasNoErrors();

        $this->actingAs($manager)->post(route('availability.store'), [
            ...$this->availabilityData($worker, '2026-10-18'),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('availabilities', 2);
    }

    #[DataProvider('nonActiveAssignmentStates')]
    public function test_non_active_or_completed_assignment_history_does_not_block_availability(string $requestStatus, string $projectStatus): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        $this->createAssignment($manager, $worker, 'apply', '2026-10-15', '2026-10-17', $requestStatus, $projectStatus);

        $response = $this->actingAs($manager)->post(route('availability.store'), [
            ...$this->availabilityData($worker, '2026-10-16'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('availabilities', 1);
    }

    public static function nonActiveAssignmentStates(): array
    {
        return [
            'pending request' => ['pending', 'open'],
            'rejected request' => ['rejected', 'open'],
            'cancelled request' => ['cancelled', 'open'],
            'completed request' => ['completed', 'open'],
            'completed project' => ['accepted', 'completed'],
            'cancelled project' => ['ongoing', 'cancelled'],
        ];
    }

    public function test_an_active_assignment_for_a_different_worker_does_not_block_availability(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        $otherWorker = $this->workerForCompany($manager->company_id, 'Jordan Lee');
        $this->createAssignment($manager, $worker, 'invite', '2026-10-15', '2026-10-17');

        $response = $this->actingAs($manager)->post(route('availability.store'), [
            ...$this->availabilityData($otherWorker, '2026-10-16'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('availabilities', 1);
    }

    public function test_an_update_into_an_accepted_open_project_date_is_rejected(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        $availability = Availability::create($this->availabilityData($worker, '2026-10-14'));
        $this->createAssignment($manager, $worker, 'apply', '2026-10-15', '2026-10-17', 'accepted', 'open');

        $response = $this->actingAs($manager)->from(route('availability.index'))->put(route('availability.update', $availability), [
            ...$this->availabilityData($worker, '2026-10-16'),
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseHas('availabilities', [
            'id' => $availability->id,
            'date' => '2026-10-14',
        ]);
    }

    public function test_an_ongoing_worker_on_an_in_progress_project_remains_blocked(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        $this->createAssignment($manager, $worker, 'apply', '2026-10-15', '2026-10-17', 'ongoing', 'in_progress');

        $this->actingAs($manager)->from(route('availability.index'))->post(route('availability.store'), [
            ...$this->availabilityData($worker, '2026-10-16'),
        ])->assertSessionHasErrors('date');

        $this->assertDatabaseCount('availabilities', 0);
    }

    private function companyManagerAndWorker(): array
    {
        $role = Role::create(['name' => 'company_owner']);
        $manager = User::factory()->create(['role_id' => $role->id]);
        $company = Company::create([
            'name' => 'Test Construction Ltd.',
            'owner_id' => $manager->id,
        ]);
        $manager->update(['company_id' => $company->id]);

        return [$manager->fresh(), $this->workerForCompany($company->id, 'Alex Martin')];
    }

    private function workerForCompany(int $companyId, string $name): WorkerProfile
    {
        return WorkerProfile::create([
            'company_id' => $companyId,
            'name' => $name,
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 38.00,
        ]);
    }

    private function createAssignment(
        User $workerManager,
        WorkerProfile $worker,
        string $type,
        string $startDate,
        string $endDate,
        string $requestStatus = 'ongoing',
        string $projectStatus = 'in_progress'
    ): WorkerRequest {
        $externalManager = User::factory()->create(['role_id' => $workerManager->role_id]);
        $externalCompany = Company::create([
            'name' => 'External Construction Ltd.',
            'owner_id' => $externalManager->id,
        ]);
        $externalManager->update(['company_id' => $externalCompany->id]);

        $project = Project::create([
            'hiring_company_id' => $externalCompany->id,
            'created_by' => $externalManager->id,
            'title' => 'Commercial Electrical Work',
            'city' => 'Edmonton',
            'province' => 'Alberta',
            'job_type' => 'Electrician',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $projectStatus,
        ]);

        return WorkerRequest::create([
            'project_id' => $project->id,
            'requested_by' => $type === 'invite' ? $externalManager->id : $workerManager->id,
            'company_id' => $type === 'invite' ? $externalCompany->id : $workerManager->company_id,
            'worker_profile_id' => $worker->id,
            'type' => $type,
            'status' => $requestStatus,
        ]);
    }

    private function availabilityData(WorkerProfile $worker, string $date): array
    {
        return [
            'worker_profile_id' => $worker->id,
            'date' => $date,
            'start_time' => '07:00',
            'end_time' => '15:00',
            'status' => 'available',
        ];
    }
}
