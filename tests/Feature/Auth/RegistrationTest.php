<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
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

    public function test_each_public_role_can_register(): void
    {
        foreach (['company_owner', 'planning_manager', 'self_employed'] as $roleName) {
            $role = Role::create(['name' => $roleName]);
            $email = "{$roleName}@example.com";

            $response = $this->post('/register', [
                'name' => ucwords(str_replace('_', ' ', $roleName)),
                'email' => $email,
                'role_id' => $role->id,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $expectedRoute = in_array($roleName, ['company_owner', 'planning_manager'])
                ? 'company.onboarding'
                : 'home';

            $response->assertRedirect(route($expectedRoute, absolute: false));
            $this->assertDatabaseHas('users', [
                'email' => $email,
                'role_id' => $role->id,
            ]);

            auth()->logout();
        }
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
