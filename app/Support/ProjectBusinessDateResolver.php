<?php

namespace App\Support;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ProjectBusinessDateResolver
{
    public function timezoneForProject(Project $project, ?User $fallbackUser = null): string
    {
        $company = $project->relationLoaded('hiringCompany')
            ? $project->hiringCompany
            : $project->hiringCompany()->first();

        return $company?->businessTimezone() ?? $this->timezoneForUser($fallbackUser);
    }

    public function timezoneForUser(?User $user): string
    {
        return $this->isSupported($user?->timezone) ? $user->timezone : 'UTC';
    }

    public function todayForProject(Project $project, ?User $fallbackUser = null): Carbon
    {
        return now($this->timezoneForProject($project, $fallbackUser))->startOfDay();
    }

    public function todayForUser(?User $user): Carbon
    {
        return now($this->timezoneForUser($user))->startOfDay();
    }

    public function hasReachedStartDate(Project $project, ?User $fallbackUser = null): bool
    {
        return $project->start_date !== null
            && $project->start_date->toDateString() <= $this->todayForProject($project, $fallbackUser)->toDateString();
    }

    public function hasReachedEndDate(Project $project, ?User $fallbackUser = null): bool
    {
        return $project->end_date !== null
            && $project->end_date->toDateString() <= $this->todayForProject($project, $fallbackUser)->toDateString();
    }

    /**
     * Adds a portable per-company timezone condition for date-only projects.
     */
    public function whereProjectDateAfterBusinessToday(Builder $query, string $column, ?User $fallbackUser = null): Builder
    {
        $supportedTimezones = array_keys(config('timezones.supported'));
        $fallbackTimezone = $this->timezoneForUser($fallbackUser);

        return $query->where(function (Builder $projectQuery) use ($supportedTimezones, $column, $fallbackTimezone): void {
            foreach ($supportedTimezones as $timezone) {
                $projectQuery->orWhere(function (Builder $timezoneQuery) use ($column, $timezone): void {
                    $timezoneQuery
                        ->whereDate($column, '>', now($timezone)->toDateString())
                        ->whereHas('hiringCompany', fn (Builder $companyQuery) => $companyQuery->where('timezone', $timezone));
                });
            }

            $projectQuery->orWhere(function (Builder $fallbackQuery) use ($column, $supportedTimezones): void {
                $fallbackQuery
                    ->whereDate($column, '>', now('UTC')->toDateString())
                    ->whereHas('hiringCompany', function (Builder $companyQuery) use ($supportedTimezones): void {
                        $companyQuery->whereNull('timezone')->orWhereNotIn('timezone', $supportedTimezones);
                    });
            });

            $projectQuery->orWhere(function (Builder $unownedProjectQuery) use ($column, $fallbackTimezone): void {
                $unownedProjectQuery
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
