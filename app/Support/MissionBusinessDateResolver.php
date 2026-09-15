<?php

namespace App\Support;

use App\Models\Mission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MissionBusinessDateResolver
{
    public function timezoneForMission(Mission $mission, ?User $fallbackUser = null): string
    {
        $company = $mission->relationLoaded('hiringCompany')
            ? $mission->hiringCompany
            : $mission->hiringCompany()->first();

        return $company?->businessTimezone() ?? $this->timezoneForUser($fallbackUser);
    }

    public function timezoneForUser(?User $user): string
    {
        return $this->isSupported($user?->timezone) ? $user->timezone : 'UTC';
    }

    public function todayForMission(Mission $mission, ?User $fallbackUser = null): Carbon
    {
        return now($this->timezoneForMission($mission, $fallbackUser))->startOfDay();
    }

    public function todayForUser(?User $user): Carbon
    {
        return now($this->timezoneForUser($user))->startOfDay();
    }

    public function hasReachedStartDate(Mission $mission, ?User $fallbackUser = null): bool
    {
        return $mission->start_date !== null
            && $mission->start_date->toDateString() <= $this->todayForMission($mission, $fallbackUser)->toDateString();
    }

    public function hasReachedEndDate(Mission $mission, ?User $fallbackUser = null): bool
    {
        return $mission->end_date !== null
            && $mission->end_date->toDateString() <= $this->todayForMission($mission, $fallbackUser)->toDateString();
    }

    /**
     * Adds a portable per-company timezone condition for date-only missions.
     */
    public function whereMissionDateAfterBusinessToday(Builder $query, string $column, ?User $fallbackUser = null): Builder
    {
        $supportedTimezones = array_keys(config('timezones.supported'));
        $fallbackTimezone = $this->timezoneForUser($fallbackUser);

        return $query->where(function (Builder $missionQuery) use ($supportedTimezones, $column, $fallbackTimezone): void {
            foreach ($supportedTimezones as $timezone) {
                $missionQuery->orWhere(function (Builder $timezoneQuery) use ($column, $timezone): void {
                    $timezoneQuery
                        ->whereDate($column, '>', now($timezone)->toDateString())
                        ->whereHas('hiringCompany', fn (Builder $companyQuery) => $companyQuery->where('timezone', $timezone));
                });
            }

            $missionQuery->orWhere(function (Builder $fallbackQuery) use ($column, $supportedTimezones): void {
                $fallbackQuery
                    ->whereDate($column, '>', now('UTC')->toDateString())
                    ->whereHas('hiringCompany', function (Builder $companyQuery) use ($supportedTimezones): void {
                        $companyQuery->whereNull('timezone')->orWhereNotIn('timezone', $supportedTimezones);
                    });
            });

            $missionQuery->orWhere(function (Builder $unownedMissionQuery) use ($column, $fallbackTimezone): void {
                $unownedMissionQuery
                    ->whereDate($column, '>', now($fallbackTimezone)->toDateString())
                    ->doesntHave('hiringCompany');
            });
        });
    }

    private function isSupported(mixed $timezone): bool
    {
        return is_string($timezone) && array_key_exists($timezone, config('timezones.supported'));
    }
}
