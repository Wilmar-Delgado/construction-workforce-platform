<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CompanyTimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_timezone_column_exists_and_defaults_to_utc(): void
    {
        $owner = User::factory()->create(['timezone' => 'UTC']);

        $company = Company::create([
            'name' => 'UTC Construction',
            'owner_id' => $owner->id,
        ]);

        $this->assertTrue(Schema::hasColumn('companies', 'timezone'));
        $this->assertSame('UTC', $company->fresh()->timezone);
        $this->assertSame('UTC', $company->fresh()->businessTimezone());
    }

    public function test_migration_backfills_supported_owner_timezones_and_falls_back_to_utc(): void
    {
        $mountainOwner = User::factory()->create(['timezone' => 'America/Edmonton']);
        $unsupportedOwner = User::factory()->create(['timezone' => 'Europe/Paris']);

        $mountainCompany = Company::create([
            'name' => 'Mountain Construction',
            'owner_id' => $mountainOwner->id,
        ]);
        $fallbackCompany = Company::create([
            'name' => 'Fallback Construction',
            'owner_id' => $unsupportedOwner->id,
        ]);

        $migration = require database_path('migrations/2026_09_12_100000_add_timezone_to_companies_table.php');
        $migration->down();
        $this->assertFalse(Schema::hasColumn('companies', 'timezone'));

        $migration->up();

        $this->assertSame('America/Edmonton', DB::table('companies')->where('id', $mountainCompany->id)->value('timezone'));
        $this->assertSame('UTC', DB::table('companies')->where('id', $fallbackCompany->id)->value('timezone'));
        $this->assertSame('America/Edmonton', $mountainOwner->fresh()->timezone);
        $this->assertSame('Europe/Paris', $unsupportedOwner->fresh()->timezone);
    }

    public function test_company_business_timezone_falls_back_for_invalid_or_missing_values(): void
    {
        $owner = User::factory()->create();
        $company = Company::create([
            'name' => 'Fallback Timezone Construction',
            'owner_id' => $owner->id,
            'timezone' => 'America/Toronto',
        ]);

        $this->assertSame('America/Toronto', $company->businessTimezone());

        $company->timezone = 'Europe/Paris';

        $this->assertSame('UTC', $company->businessTimezone());

        $company->timezone = null;

        $this->assertSame('UTC', $company->businessTimezone());
    }

    public function test_personal_settings_do_not_change_company_business_timezone(): void
    {
        $owner = User::factory()->create(['timezone' => 'America/Edmonton']);
        $company = Company::create([
            'name' => 'Independent Company Timezone',
            'owner_id' => $owner->id,
            'timezone' => 'America/Toronto',
        ]);
        $owner->update(['company_id' => $company->id]);

        $this->actingAs($owner)
            ->postJson(route('settings.notifications.update'), [
                'email' => true,
                'sms' => false,
                'projectAlerts' => true,
                'language' => 'en',
                'timezone' => 'America/Vancouver',
            ])
            ->assertOk();

        $this->assertSame('America/Vancouver', $owner->fresh()->timezone);
        $this->assertSame('America/Toronto', $company->fresh()->timezone);
    }
}
