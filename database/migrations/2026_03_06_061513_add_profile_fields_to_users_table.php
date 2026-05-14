<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('language')->default('en')->after('phone');
            $table->string('timezone')->default('UTC')->after('language');
            $table->boolean('email_notifications')->default(true)->after('timezone');
            $table->boolean('sms_notifications')->default(false)->after('email_notifications');
            $table->boolean('mission_alerts')->default(true)->after('sms_notifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'language',
                'timezone',
                'email_notifications',
                'sms_notifications',
                'mission_alerts'
            ]);
        });
    }
};
