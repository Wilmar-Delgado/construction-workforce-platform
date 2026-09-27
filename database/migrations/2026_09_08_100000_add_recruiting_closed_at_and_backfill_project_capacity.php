<?php

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('recruiting_closed_at')->nullable()->after('status');
        });

        DB::table('projects')
            ->where(function ($query) {
                $query->whereNull('workers')->orWhere('workers', '<=', 0);
            })
            ->orderBy('id')
            ->eachById(function (object $project) {
                $committedWorkerCount = DB::table('requests')
                    ->where('project_id', $project->id)
                    ->whereIn('status', Project::COMMITTED_REQUEST_STATUSES)
                    ->count();

                DB::table('projects')
                    ->where('id', $project->id)
                    ->update(['workers' => max(1, $committedWorkerCount)]);
            });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('recruiting_closed_at');
        });
    }
};
