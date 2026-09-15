<?php

use App\Models\Mission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->timestamp('recruiting_closed_at')->nullable()->after('status');
        });

        DB::table('missions')
            ->where(function ($query) {
                $query->whereNull('workers')->orWhere('workers', '<=', 0);
            })
            ->orderBy('id')
            ->eachById(function (object $mission) {
                $committedWorkerCount = DB::table('requests')
                    ->where('mission_id', $mission->id)
                    ->whereIn('status', Mission::COMMITTED_REQUEST_STATUSES)
                    ->count();

                DB::table('missions')
                    ->where('id', $mission->id)
                    ->update(['workers' => max(1, $committedWorkerCount)]);
            });
    }

    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn('recruiting_closed_at');
        });
    }
};
