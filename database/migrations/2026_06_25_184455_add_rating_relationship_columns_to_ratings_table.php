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
        Schema::table('ratings', function (Blueprint $table) {

            $table->foreignId('reviewed_by_user_id')
                ->after('mission_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('worker_profile_id')
                ->after('reviewed_by_user_id')
                ->constrained('worker_profiles')
                ->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {

            $table->dropForeign(['reviewed_by_user_id']);
            $table->dropForeign(['worker_profile_id']);

            $table->dropColumn([
                'reviewed_by_user_id',
                'worker_profile_id'
            ]);

        });
    }
};
