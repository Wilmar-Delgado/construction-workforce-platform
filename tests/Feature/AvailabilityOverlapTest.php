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

class AvailabilityOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_non_overlapping_slot_for_the_same_worker_succeeds(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();

        $this->createAvailability($worker, '2026-10-15', '07:00', '12:00');

        $response = $this->actingAs($manager)->post(route('availability.store'), [
            ...$this->availabilityData($worker, '2026-10-15', '13:00', '16:00'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('availabilities', 2);
    }

    public function test_an_adjacent_slot_for_the_same_worker_succeeds(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();

        $this->createAvailability($worker, '2026-10-15', '07:00', '12:00');

        $response = $this->actingAs($manager)->post(route('availability.store'), [
            ...$this->availabilityData($worker, '2026-10-15', '12:00', '16:00'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('availabilities', 2);
    }

    #[DataProvider('validIntervals')]
    public function test_valid_same_day_intervals_succeed(string $startTime, string $endTime): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();

        $response = $this->actingAs($manager)->post(route('availability.store'), [
            ...$this->availabilityData($worker, '2026-10-15', $startTime, $endTime),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('availabilities', 1);
    }

    public static function validIntervals(): array
    {
        return [
            'hour and minute values' => ['07:00', '15:15'],
            'hour minute and second values' => ['07:00:00', '15:15:00'],
            'one minute interval' => ['07:00', '07:01'],
        ];
    }

    #[DataProvider('overlappingIntervals')]
    public function test_overlapping_slots_are_rejected(string $startTime, string $endTime): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();

        $this->createAvailability($worker, '2026-10-15', '07:00', '12:00', 'booked');

        $response = $this->actingAs($manager)->from(route('availability.index'))->post(route('availability.store'), [
            ...$this->availabilityData($worker, '2026-10-15', $startTime, $endTime, 'unavailable'),
        ]);

        $response
            ->assertRedirect(route('availability.index'))
            ->assertSessionHasErrors('start_time');
        $this->assertDatabaseCount('availabilities', 1);
    }

    public static function overlappingIntervals(): array
    {
        return [
            'partial overlap at the start' => ['06:00', '08:00'],
            'partial overlap at the end' => ['11:00', '16:00'],
            'new interval inside existing interval' => ['08:00', '11:00'],
            'new interval contains existing interval' => ['06:00', '16:00'],
        ];
    }

    public function test_the_same_time_for_a_different_worker_succeeds(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        $otherWorker = $this->workerForCompany($manager->company_id, 'Jordan Lee');

        $this->createAvailability($worker, '2026-10-15', '07:00', '12:00');

        $response = $this->actingAs($manager)->post(route('availability.store'), [
            ...$this->availabilityData($otherWorker, '2026-10-15', '07:00', '12:00'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('availabilities', 2);
    }

    public function test_the_same_worker_on_a_different_date_succeeds(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();

        $this->createAvailability($worker, '2026-10-15', '07:00', '12:00');

        $response = $this->actingAs($manager)->post(route('availability.store'), [
            ...$this->availabilityData($worker, '2026-10-16', '07:00', '12:00'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('availabilities', 2);
    }

    public function test_an_update_does_not_conflict_with_its_own_interval(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        $availability = $this->createAvailability($worker, '2026-10-15', '07:00', '12:00');

        $response = $this->actingAs($manager)->put(route('availability.update', $availability), [
            ...$this->availabilityData($worker, '2026-10-15', '07:00', '12:00', 'booked'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('availabilities', [
            'id' => $availability->id,
            'status' => 'booked',
        ]);
    }

    public function test_an_update_uses_the_same_same_day_time_rule(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        $availability = $this->createAvailability($worker, '2026-10-15', '07:00', '12:00');
        $storedAvailability = Availability::findOrFail($availability->id);
        $originalStartTime = $storedAvailability->start_time;
        $originalEndTime = $storedAvailability->end_time;

        $response = $this->actingAs($manager)->from(route('availability.index'))->put(route('availability.update', $availability), [
            ...$this->availabilityData($worker, '2026-10-15', '15:00', '07:00'),
        ]);

        $response->assertSessionHasErrors([
            'end_time' => __('app.availability_page.validation.end_after_start'),
        ]);

        $this->assertSame($originalStartTime, $storedAvailability->fresh()->start_time);
        $this->assertSame($originalEndTime, $storedAvailability->fresh()->end_time);
    }

    public function test_an_update_into_another_slot_is_rejected(): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();
        $availability = $this->createAvailability($worker, '2026-10-15', '07:00', '09:00');
        $this->createAvailability($worker, '2026-10-15', '10:00', '12:00');
        $storedAvailability = Availability::findOrFail($availability->id);
        $originalStartTime = $storedAvailability->start_time;
        $originalEndTime = $storedAvailability->end_time;

        $response = $this->actingAs($manager)->from(route('availability.index'))->put(route('availability.update', $availability), [
            ...$this->availabilityData($worker, '2026-10-15', '11:00', '13:00'),
        ]);

        $response->assertSessionHasErrors('start_time');

        $this->assertSame($originalStartTime, $storedAvailability->fresh()->start_time);
        $this->assertSame($originalEndTime, $storedAvailability->fresh()->end_time);
    }

    #[DataProvider('invalidIntervals')]
    public function test_zero_length_and_backwards_intervals_are_rejected(string $startTime, string $endTime): void
    {
        [$manager, $worker] = $this->companyManagerAndWorker();

        $response = $this->actingAs($manager)->from(route('availability.index'))->post(route('availability.store'), [
            ...$this->availabilityData($worker, '2026-10-15', $startTime, $endTime),
        ]);

        $response->assertSessionHasErrors('end_time');
        $this->assertDatabaseCount('availabilities', 0);
    }

    public static function invalidIntervals(): array
    {
        return [
            'zero length' => ['12:00', '12:00'],
            'backwards' => ['16:00', '12:00'],
        ];
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

    private function createAvailability(
        WorkerProfile $worker,
        string $date,
        string $startTime,
        string $endTime,
        string $status = 'available'
    ): Availability {
        return Availability::create($this->availabilityData($worker, $date, $startTime, $endTime, $status));
    }

    private function availabilityData(
        WorkerProfile $worker,
        string $date,
        string $startTime,
        string $endTime,
        string $status = 'available'
    ): array {
        return [
            'worker_profile_id' => $worker->id,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $status,
        ];
    }
}
