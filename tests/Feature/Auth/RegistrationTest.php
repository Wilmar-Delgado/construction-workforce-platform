<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_registration_screen_only_exposes_company_owner_and_self_employed_roles(): void
    {
        Role::create(['name' => 'administrator']);
        Role::create(['name' => 'company_owner']);
        Role::create(['name' => 'planning_manager']);
        Role::create(['name' => 'self_employed']);

        $this->get('/register')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Register')
                ->has('roles', 2)
                ->where('roles.0.name', 'company_owner')
                ->where('roles.1.name', 'self_employed')
            );
    }

    public function test_new_users_can_register(): void
    {
        $role = Role::create(['name' => 'self_employed']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role_id' => $role->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_company_owner_can_register_and_is_redirected_to_company_onboarding(): void
    {
        $role = Role::create(['name' => 'company_owner']);

        $response = $this->post('/register', [
            'name' => 'Company Owner',
            'email' => 'company-owner@example.com',
            'role_id' => $role->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('company.onboarding', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'company-owner@example.com',
            'role_id' => $role->id,
        ]);
    }

    public function test_planning_manager_role_cannot_be_assigned_through_public_registration(): void
    {
        $planningManager = Role::create(['name' => 'planning_manager']);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Attempted Planning Manager',
            'email' => 'attempted-manager@example.com',
            'role_id' => $planningManager->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('users', ['email' => 'attempted-manager@example.com']);
    }

    public function test_administrator_role_cannot_be_assigned_through_public_registration(): void
    {
        $administrator = Role::create(['name' => 'administrator']);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Attempted Administrator',
            'email' => 'attempted-admin@example.com',
            'role_id' => $administrator->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('users', ['email' => 'attempted-admin@example.com']);
    }

    public function test_invalid_role_id_cannot_be_assigned_through_public_registration(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Invalid Role',
            'email' => 'invalid-role@example.com',
            'role_id' => 999999,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('users', ['email' => 'invalid-role@example.com']);
    }
}
