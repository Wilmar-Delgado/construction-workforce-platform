<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SettingsTimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_exposes_the_central_supported_timezone_catalog(): void
    {
        $user = User::factory()->create(['timezone' => 'America/Edmonton']);

        $this->actingAs($user)
            ->get(route('settings'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Settings')
                ->where('timezoneOptions.America/Edmonton', 'mountain')
                ->where('timezoneOptions.UTC', 'utc')
                ->has('timezoneOptions', 8));
    }

    public function test_a_supported_timezone_and_notification_preferences_persist(): void
    {
        $user = User::factory()->create([
            'timezone' => 'UTC',
            'email_notifications' => false,
            'sms_notifications' => false,
            'mission_alerts' => false,
        ]);

        $this->actingAs($user)
            ->postJson(route('settings.notifications.update'), [
                'email' => true,
                'sms' => true,
                'missionAlerts' => true,
                'language' => 'en',
                'timezone' => 'America/Edmonton',
            ])
            ->assertOk()
            ->assertJsonPath('user.timezone', 'America/Edmonton')
            ->assertJsonPath('user.email_notifications', true)
            ->assertJsonPath('user.sms_notifications', true);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'timezone' => 'America/Edmonton',
            'email_notifications' => true,
            'sms_notifications' => true,
            'mission_alerts' => true,
        ]);
    }

    public function test_an_unsupported_timezone_is_rejected_without_changing_preferences(): void
    {
        $user = User::factory()->create([
            'timezone' => 'UTC',
            'email_notifications' => false,
            'sms_notifications' => false,
        ]);

        $this->actingAs($user)
            ->postJson(route('settings.notifications.update'), [
                'email' => true,
                'sms' => true,
                'missionAlerts' => true,
                'language' => 'en',
                'timezone' => 'Europe/Paris',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('timezone');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'timezone' => 'UTC',
            'email_notifications' => false,
            'sms_notifications' => false,
        ]);
    }
}
