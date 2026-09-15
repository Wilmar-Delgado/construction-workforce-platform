<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasDuplicateMissionWorkerPairs = DB::table('requests')
            ->select('mission_id', 'worker_profile_id')
            ->whereNotNull('worker_profile_id')
            ->groupBy('mission_id', 'worker_profile_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateMissionWorkerPairs) {
            throw new \RuntimeException(
                'Cannot add the mission-worker request uniqueness constraint while duplicate request records exist. Resolve duplicate mission_id/worker_profile_id pairs first.'
            );
        }

        // Add the replacement indexes before removing the legacy indexes. On MySQL,
        // the legacy ratings index is required by the mission_id foreign key until the
        // composite index can satisfy that same leftmost-column requirement.
        if (! Schema::hasIndex('requests', 'requests_mission_worker_unique')) {
            Schema::table('requests', function (Blueprint $table) {
                $table->unique(['mission_id', 'worker_profile_id'], 'requests_mission_worker_unique');
            });
        }

        if (Schema::hasIndex('requests', 'request_unique_per_worker')) {
            Schema::table('requests', function (Blueprint $table) {
                $table->dropUnique('request_unique_per_worker');
            });
        }

        if (! Schema::hasIndex('ratings', 'ratings_mission_worker_unique')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->unique(['mission_id', 'worker_profile_id'], 'ratings_mission_worker_unique');
            });
        }

        if (Schema::hasIndex('ratings', 'ratings_mission_id_unique')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->dropUnique('ratings_mission_id_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasIndex('ratings', 'ratings_mission_id_unique')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->unique('mission_id', 'ratings_mission_id_unique');
            });
        }

        if (Schema::hasIndex('ratings', 'ratings_mission_worker_unique')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->dropUnique('ratings_mission_worker_unique');
            });
        }

        if (! Schema::hasIndex('requests', 'request_unique_per_worker')) {
            Schema::table('requests', function (Blueprint $table) {
                $table->unique(['mission_id', 'worker_profile_id', 'type'], 'request_unique_per_worker');
            });
        }

        if (Schema::hasIndex('requests', 'requests_mission_worker_unique')) {
            Schema::table('requests', function (Blueprint $table) {
                $table->dropUnique('requests_mission_worker_unique');
            });
        }
    }
};
