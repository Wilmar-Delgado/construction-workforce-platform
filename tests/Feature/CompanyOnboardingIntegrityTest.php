<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CompanyOnboardingIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_eligible_company_owner_can_create_their_first_company(): void
    {
        $owner = $this->userWithRole('company_owner');

        $response = $this->actingAs($owner)->post(route('company.store'), $this->companyPayload());

        $response->assertRedirect(route('home', absolute: false));
        $this->assertDatabaseHas('companies', [
            'name' => 'Northstar Build Group',
            'owner_id' => $owner->id,
        ]);

        $this->assertNotNull($owner->fresh()->company_id);
    }

    public function test_company_owner_cannot_create_another_company_after_onboarding(): void
    {
        $owner = $this->companyUser('company_owner');

        $response = $this->actingAs($owner)
            ->from(route('company.onboarding'))
            ->post(route('company.store'), $this->companyPayload());

        $response->assertRedirect(route('company.onboarding', absolute: false));
        $response->assertSessionHasErrors('company');
        $this->assertSame(1, Company::count());
    }

    public function test_company_onboarding_does_not_overwrite_an_existing_company_context(): void
    {
        $manager = $this->companyUser('planning_manager');
        $originalCompanyId = $manager->company_id;

        $response = $this->actingAs($manager)
            ->from(route('company.onboarding'))
            ->post(route('company.store'), $this->companyPayload());

        $response->assertRedirect(route('company.onboarding', absolute: false));
        $response->assertSessionHasErrors('company');
        $this->assertSame($originalCompanyId, $manager->fresh()->company_id);
        $this->assertSame(1, Company::count());
    }

    #[DataProvider('ineligibleCompanyCreatorRoles')]
    public function test_ineligible_roles_cannot_create_a_company(string $roleName): void
    {
        $user = $this->userWithRole($roleName);

        $response = $this->actingAs($user)
            ->from(route('company.onboarding'))
            ->post(route('company.store'), $this->companyPayload());

        $response->assertRedirect(route('company.onboarding', absolute: false));
        $response->assertSessionHasErrors('company');
        $this->assertDatabaseCount('companies', 0);
        $this->assertNull($user->fresh()->company_id);
    }

    public function test_orphan_planning_manager_is_redirected_away_from_company_onboarding(): void
    {
        $manager = $this->userWithRole('planning_manager');

        $this->actingAs($manager)
            ->get(route('company.onboarding'))
            ->assertRedirect(route('home', absolute: false));
    }

    public static function ineligibleCompanyCreatorRoles(): array
    {
        return [
            'planning manager' => ['planning_manager'],
            'self-employed user' => ['self_employed'],
            'administrator' => ['administrator'],
        ];
    }

    public function test_administrator_without_company_context_cannot_create_a_mission(): void
    {
        $administrator = $this->userWithRole('administrator');

        $response = $this->actingAs($administrator)
            ->from(route('missions.index'))
            ->post(route('missions.store'), $this->missionPayload());

        $response->assertRedirect(route('missions.index', absolute: false));
        $response->assertSessionHasErrors('company');
        $this->assertDatabaseCount('missions', 0);
    }

    public function test_planning_manager_with_company_context_retains_mission_creation_access(): void
    {
        $manager = $this->companyUser('planning_manager');

        $response = $this->actingAs($manager)->post(route('missions.store'), $this->missionPayload());

        $response->assertRedirect(route('missions.index', absolute: false));
        $this->assertDatabaseHas('missions', [
            'title' => 'Concrete Formwork Support',
            'hiring_company_id' => $manager->company_id,
        ]);
    }

    public function test_orphan_planning_manager_cannot_create_company_owned_records(): void
    {
        $manager = $this->userWithRole('planning_manager');

        $response = $this->actingAs($manager)->post(route('missions.store'), $this->missionPayload());

        $response->assertForbidden();
        $this->assertDatabaseCount('missions', 0);
    }

    private function userWithRole(string $roleName): User
    {
        return User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => $roleName])->id,
            'company_id' => null,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }

    private function companyUser(string $roleName): User
    {
        $owner = $this->userWithRole('company_owner');
        $company = Company::create([
            'name' => 'Existing Company',
            'phone' => '403-555-0100',
            'address' => '100 Test Street',
            'owner_id' => $owner->id,
        ]);

        $owner->update(['company_id' => $company->id]);

        $user = $roleName === 'company_owner'
            ? $owner
            : $this->userWithRole($roleName);

        $user->update(['company_id' => $company->id]);

        return $user->fresh();
    }

    private function companyPayload(): array
    {
        return [
            'name' => 'Northstar Build Group',
            'phone' => '403-555-0111',
            'address' => '200 Builder Avenue',
        ];
    }

    private function missionPayload(): array
    {
        return [
            'title' => 'Concrete Formwork Support',
            'description' => 'Support a concrete formwork crew.',
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
            'city' => 'Calgary',
            'province' => 'Alberta',
            'country' => 'Canada',
            'job_type' => 'General Labourer',
            'workers' => 2,
            'hourly_rate' => 30,
            'status' => 'draft',
        ];
    }
}
