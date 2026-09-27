<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Support\ProjectBusinessDateResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessProjectStartDates extends Command
{
    protected $signature = 'projects:process-start-dates';

    protected $description = 'Closes due recruiting and starts projects that have accepted workers.';

    public function __construct(private readonly ProjectBusinessDateResolver $businessDates)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $processed = 0;

        Project::query()
            ->notArchived()
            ->where('status', 'open')
            ->with('hiringCompany')
            ->orderBy('id')
            ->eachById(function (Project $project) use (&$processed): void {
                if (! $this->businessDates->hasReachedStartDate($project)) {
                    return;
                }

                $wasProcessed = DB::transaction(function () use ($project): bool {
                    $lockedProject = Project::query()
                        ->with('hiringCompany')
                        ->lockForUpdate()
                        ->findOrFail($project->id);

                    if ($lockedProject->status !== 'open'
                        || ! $this->businessDates->hasReachedStartDate($lockedProject)) {
                        return false;
                    }

                    $lockedProject->closeRecruitingAndCancelPending();

                    if ($lockedProject->requests()->where('status', 'accepted')->exists()) {
                        $lockedProject->startExecution();
                    }

                    return true;
                });

                if ($wasProcessed) {
                    $processed++;
                }
            });

        // An assignment can end early before the project end date. Once that
        // date arrives, close any in-progress project whose assignments are
        // all resolved without requiring an unrelated follow-up action.
        Project::query()
            ->notArchived()
            ->where('status', 'in_progress')
            ->with('hiringCompany')
            ->orderBy('id')
            ->eachById(function (Project $project) use (&$processed): void {
                if (! $this->businessDates->hasReachedEndDate($project)) {
                    return;
                }

                $wasCompleted = DB::transaction(function () use ($project): bool {
                    $lockedProject = Project::query()
                        ->with('hiringCompany')
                        ->lockForUpdate()
                        ->findOrFail($project->id);

                    if (! $this->businessDates->hasReachedEndDate($lockedProject)) {
                        return false;
                    }

                    return $lockedProject->completeIfReady();
                });

                if ($wasCompleted) {
                    $processed++;
                }
            });

        $this->info("Processed {$processed} due project(s).");

        return self::SUCCESS;
    }
}
