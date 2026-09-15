<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('timezone')->default('UTC');
        });

        $supportedTimezones = array_flip(array_keys(config('timezones.supported')));

        foreach (DB::table('companies')
            ->leftJoin('users', 'companies.owner_id', '=', 'users.id')
            ->orderBy('companies.id')
            ->select('companies.id as company_id', 'users.timezone as owner_timezone')
            ->cursor() as $company) {
            $timezone = isset($supportedTimezones[$company->owner_timezone])
                ? $company->owner_timezone
                : 'UTC';

            DB::table('companies')
                ->where('id', $company->company_id)
                ->update(['timezone' => $timezone]);
        }
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
