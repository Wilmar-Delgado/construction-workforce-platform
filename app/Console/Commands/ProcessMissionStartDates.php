<?php

namespace App\Console\Commands;

use App\Models\Mission;
use App\Support\MissionBusinessDateResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessMissionStartDates extends Command
{
    protected $signature = 'missions:process-start-dates';

    protected $description = 'Closes due recruiting and starts missions that have accepted workers.';

    public function __construct(private readonly MissionBusinessDateResolver $businessDates)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $processed = 0;

        Mission::query()
            ->notArchived()
            ->where('status', 'open')
            ->with('hiringCompany')
            ->orderBy('id')
            ->eachById(function (Mission $mission) use (&$processed): void {
                if (! $this->businessDates->hasReachedStartDate($mission)) {
                    return;
                }

                $wasProcessed = DB::transaction(function () use ($mission): bool {
                    $lockedMission = Mission::query()
                        ->with('hiringCompany')
                        ->lockForUpdate()
                        ->findOrFail($mission->id);

                    if ($lockedMission->status !== 'open'
                        || ! $this->businessDates->hasReachedStartDate($lockedMission)) {
                        return false;
                    }

                    $lockedMission->closeRecruitingAndCancelPending();

                    if ($lockedMission->requests()->where('status', 'accepted')->exists()) {
                        $lockedMission->startExecution();
                    }

                    return true;
                });

                if ($wasProcessed) {
                    $processed++;
                }
            });

        // An assignment can end early before the mission end date. Once that
        // date arrives, close any in-progress mission whose assignments are
        // all resolved without requiring an unrelated follow-up action.
        Mission::query()
            ->notArchived()
            ->where('status', 'in_progress')
            ->with('hiringCompany')
            ->orderBy('id')
            ->eachById(function (Mission $mission) use (&$processed): void {
                if (! $this->businessDates->hasReachedEndDate($mission)) {
                    return;
                }

                $wasCompleted = DB::transaction(function () use ($mission): bool {
                    $lockedMission = Mission::query()
                        ->with('hiringCompany')
                        ->lockForUpdate()
                        ->findOrFail($mission->id);

                    if (! $this->businessDates->hasReachedEndDate($lockedMission)) {
                        return false;
                    }

                    return $lockedMission->completeIfReady();
                });

                if ($wasCompleted) {
                    $processed++;
                }
            });

        $this->info("Processed {$processed} due mission(s).");

        return self::SUCCESS;
    }
}
