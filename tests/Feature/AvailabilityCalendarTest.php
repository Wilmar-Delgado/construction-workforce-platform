<?php

namespace Tests\Feature;

use App\Models\Availability;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AvailabilityCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_company_manager_receives_only_own_company_workers_and_availability(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        [, $externalWorker] = $this->companyManagerAndWorker('External Construction Ltd.', 'Sam Taylor');

        $this->createAvailability($worker, '2026-10-15');
        $this->createAvailability($externalWorker, '2026-10-15');

        $response = $this->actingAs($manager)->getJson(route('availability.calendar', [
            'start' => '2026-10-15',
            'end' => '2026-10-15',
        ]));

        $response
            ->assertOk()
            ->assertJsonCount(1, 'workers')
            ->assertJsonCount(1, 'availabilities')
            ->assertJsonPath('workers.0.id', $worker->id)
            ->assertJsonPath('availabilities.0.worker_profile_id', $worker->id);
    }

    public function test_a_self_employed_user_receives_only_their_own_independent_profile_and_availability(): void
    {
        [$user, $worker] = $this->selfEmployedUserAndWorker('Maria Chen');
        [, $otherWorker] = $this->selfEmployedUserAndWorker('Noah Wilson');

        $this->createAvailability($worker, '2026-10-15');
        $this->createAvailability($otherWorker, '2026-10-15');

        $response = $this->actingAs($user)->getJson(route('availability.calendar', [
            'start' => '2026-10-15',
            'end' => '2026-10-15',
        ]));

        $response
            ->assertOk()
            ->assertJsonCount(1, 'workers')
            ->assertJsonCount(1, 'availabilities')
            ->assertJsonPath('workers.0.id', $worker->id)
            ->assertJsonPath('availabilities.0.worker_profile_id', $worker->id);
    }

    public function test_an_unauthorized_worker_filter_is_rejected(): void
    {
        [$manager] = $this->companyManagerAndWorker();
        [, $externalWorker] = $this->companyManagerAndWorker('External Construction Ltd.', 'Sam Taylor');

        $response = $this->actingAs($manager)->getJson(route('availability.calendar', [
            'start' => '2026-10-15',
            'end' => '2026-10-15',
            'worker_profile_id' => $externalWorker->id,
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('worker_profile_id');
    }

    public function test_the_requested_date_range_and_worker_filter_are_respected(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        $otherWorker = $this->workerForCompany($manager->company_id, 'Jordan Lee');

        $inRange = $this->createAvailability($worker, '2026-10-15');
        $this->createAvailability($worker, '2026-10-20');
        $this->createAvailability($otherWorker, '2026-10-15');

        $response = $this->actingAs($manager)->getJson(route('availability.calendar', [
            'start' => '2026-10-14',
            'end' => '2026-10-16',
            'worker_profile_id' => $worker->id,
        ]));

        $response
            ->assertOk()
            ->assertJsonCount(1, 'workers')
            ->assertJsonCount(1, 'availabilities')
            ->assertJsonPath('workers.0.id', $worker->id)
            ->assertJsonPath('availabilities.0.id', $inRange->id);
    }

    #[DataProvider('invalidCalendarParameters')]
    public function test_invalid_calendar_ranges_are_rejected(array $parameters, string $errorField): void
    {
        [$manager] = $this->companyManagerAndWorker();

        $response = $this->actingAs($manager)->getJson(route('availability.calendar', $parameters));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errorField);
    }

    public static function invalidCalendarParameters(): array
    {
        return [
            'invalid start date' => [
                ['start' => 'not-a-date', 'end' => '2026-10-15'],
                'start',
            ],
            'invalid end date' => [
                ['start' => '2026-10-15', 'end' => 'not-a-date'],
                'end',
            ],
            'end before start' => [
                ['start' => '2026-10-16', 'end' => '2026-10-15'],
                'end',
            ],
            'range larger than 62 inclusive days' => [
                ['start' => '2026-10-01', 'end' => '2026-12-02'],
                'end',
            ],
        ];
    }

    private function companyManagerAndWorker(
        string $companyName = 'Test Construction Ltd.',
        string $workerName = 'Alex Martin'
    ): array {
        $role = Role::firstOrCreate(['name' => 'company_owner']);
        $manager = User::factory()->create(['role_id' => $role->id]);
        $company = Company::create([
            'name' => $companyName,
            'owner_id' => $manager->id,
        ]);
        $manager->update(['company_id' => $company->id]);

        return [$manager->fresh(), $this->workerForCompany($company->id, $workerName)];
    }

    private function selfEmployedUserAndWorker(string $workerName): array
    {
        $role = Role::firstOrCreate(['name' => 'self_employed']);
        $user = User::factory()->create(['role_id' => $role->id, 'company_id' => null]);
        $worker = WorkerProfile::create([
            'user_id' => $user->id,
            'company_id' => null,
            'name' => $workerName,
            'job' => 'Electrician',
            'years_experience' => 5,
            'hourly_rate' => 38.00,
        ]);

        return [$user, $worker];
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

    private function createAvailability(WorkerProfile $worker, string $date): Availability
    {
        return Availability::create([
            'worker_profile_id' => $worker->id,
            'date' => $date,
            'start_time' => '07:00',
            'end_time' => '15:30',
            'status' => 'available',
        ]);
    }
}
