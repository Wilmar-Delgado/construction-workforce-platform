<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SettingsPersonalInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_a_name_preserves_email_verification(): void
    {
        $user = User::factory()->create();
        $verifiedAt = $user->email_verified_at;

        $this->actingAs($user)
            ->from(route('settings'))
            ->put(route('settings.personal.update'), [
                'name' => 'Updated Name',
                'email' => $user->email,
                'phone' => $user->phone,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('settings'));

        $user->refresh();

        $this->assertSame('Updated Name', $user->name);
        $this->assertTrue($user->email_verified_at->equalTo($verifiedAt));
    }

    public function test_updating_a_phone_number_preserves_email_verification(): void
    {
        $user = User::factory()->create();
        $verifiedAt = $user->email_verified_at;

        $this->actingAs($user)
            ->put(route('settings.personal.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '780-555-0100',
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame('780-555-0100', $user->phone);
        $this->assertTrue($user->email_verified_at->equalTo($verifiedAt));
    }

    public function test_submitting_the_existing_email_preserves_email_verification(): void
    {
        $user = User::factory()->create();
        $verifiedAt = $user->email_verified_at;

        $this->actingAs($user)
            ->put(route('settings.personal.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue($user->refresh()->email_verified_at->equalTo($verifiedAt));
    }

    public function test_changing_an_email_persists_it_and_clears_verification(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('settings.personal.update'), [
                'name' => $user->name,
                'email' => 'updated@example.com',
                'phone' => $user->phone,
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame('updated@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_duplicate_email_is_rejected_without_changing_identity_data(): void
    {
        $user = User::factory()->create();
        $verifiedAt = $user->email_verified_at;
        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->putJson(route('settings.personal.update'), [
                'name' => 'Changed Name',
                'email' => $otherUser->email,
                'phone' => '780-555-0100',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $user->refresh();

        $this->assertNotSame('Changed Name', $user->name);
        $this->assertNotSame($otherUser->email, $user->email);
        $this->assertTrue($user->email_verified_at->equalTo($verifiedAt));
    }

    public function test_changed_email_is_subject_to_the_existing_verification_requirement(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('settings.personal.update'), [
                'name' => $user->name,
                'email' => 'unverified@example.com',
                'phone' => $user->phone,
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($user->fresh())
            ->get(route('home'))
            ->assertRedirect(route('verification.notice', absolute: false));

        $this->actingAs($user->fresh())
            ->get(route('verification.notice'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/VerifyEmail')
                ->where('auth.user.email', 'unverified@example.com')
                ->where('auth.user.email_verified_at', null));
    }

    public function test_updated_identity_is_exposed_through_shared_inertia_auth_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('settings.personal.update'), [
                'name' => 'Updated Name',
                'email' => $user->email,
                'phone' => '780-555-0100',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($user->fresh())
            ->get(route('settings'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Settings')
                ->where('auth.user.name', 'Updated Name')
                ->where('auth.user.phone', '780-555-0100')
                ->where('auth.user.email', $user->email)
                ->where('auth.user.email_verified_at', $user->email_verified_at?->toJSON()));
    }

    public function test_an_inactive_user_cannot_access_settings(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->actingAs($user)
            ->get(route('settings'))
            ->assertRedirect(route('login', absolute: false));

        $this->assertGuest();
    }
}
