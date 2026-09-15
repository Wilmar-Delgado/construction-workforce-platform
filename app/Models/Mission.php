<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Support\MissionBusinessDateResolver;

class Mission extends Model
{
    /**
     * Assignments that consumed one of the mission's requested positions.
     * This is staffing history, so an early-ended worker remains committed.
     */
    public const COMMITTED_REQUEST_STATUSES = ['accepted', 'ongoing', 'completed', 'ended_early'];

    /** Assignments that still require a mission-level resolution. */
    public const ACTIVE_ASSIGNMENT_STATUSES = ['accepted', 'ongoing'];

    protected $fillable = [
        'hiring_company_id',
        'leading_company_id',
        'created_by',
        'worker_profile_id',
        'title',
        'description',
        'city',
        'province',
        'country',
        'address_line_1',
        'address_line_2',
        'postal_code',
        'site_name',
        'directions',
        'latitude',
        'longitude',
        'job_type',
        'workers',
        'start_date',
        'end_date',
        'hourly_rate',
        'status',
        'recruiting_closed_at',
        'archived_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'recruiting_closed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    protected $appends = [
        'remaining_capacity',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function hiringCompany()
    {
        return $this->belongsTo(Company::class, 'hiring_company_id');
    }

    public function lendingCompany()
    {
        return $this->belongsTo(Company::class, 'lending_company_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workerProfile()
    {
        return $this->belongsTo(WorkerProfile::class);
    }

    public function requirements()
    {
        return $this->hasMany(MissionRequirement::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function requests()
    {
        return $this->hasMany(WorkerRequest::class);
    }

    public function committedRequests()
    {
        return $this->requests()->whereIn('status', self::COMMITTED_REQUEST_STATUSES);
    }

    public function scopeWithCommittedWorkerCount(Builder $query): Builder
    {
        return $query->withCount([
            'requests as committed_worker_count' => fn (Builder $requestQuery) => $requestQuery
                ->whereIn('status', self::COMMITTED_REQUEST_STATUSES),
        ]);
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeActionableForStaffing(Builder $query, ?User $fallbackUser = null): Builder
    {
        $statuses = implode(', ', array_fill(0, count(self::COMMITTED_REQUEST_STATUSES), '?'));

        $query
            ->notArchived()
            ->where('status', 'open')
            ->whereNull('recruiting_closed_at')
            ->whereNotNull('workers')
            ->where('workers', '>', 0)
            ->whereRaw(
                "workers > (select count(*) from requests where requests.mission_id = missions.id and requests.status in ({$statuses}))",
                self::COMMITTED_REQUEST_STATUSES,
            );

        return app(MissionBusinessDateResolver::class)
            ->whereMissionDateAfterBusinessToday($query, 'start_date', $fallbackUser);
    }

    public function getCommittedWorkerCountAttribute(): int
    {
        return (int) ($this->attributes['committed_worker_count']
            ?? $this->committedRequests()->count());
    }

    public function getRemainingCapacityAttribute(): int
    {
        return max(0, ((int) $this->workers) - $this->committed_worker_count);
    }

    public function isRecruitingOpen(): bool
    {
        return $this->status === 'open' && $this->recruiting_closed_at === null;
    }

    public function isActionableForStaffing(?User $fallbackUser = null): bool
    {
        return $this->archived_at === null
            && $this->isRecruitingOpen()
            && $this->start_date instanceof Carbon
            && $this->start_date->toDateString() > $this->businessToday($fallbackUser)->toDateString()
            && $this->remaining_capacity > 0;
    }

    public function canBePermanentlyDeleted(): bool
    {
        return $this->status === 'draft'
            && $this->archived_at === null
            && ! $this->requests()->exists()
            && ! $this->ratings()->exists();
    }

    public function canBeArchived(): bool
    {
        return $this->status === 'completed'
            && $this->archived_at === null;
    }

    public function businessTimezone(?User $fallbackUser = null): string
    {
        return app(MissionBusinessDateResolver::class)->timezoneForMission($this, $fallbackUser);
    }

    public function businessToday(?User $fallbackUser = null): Carbon
    {
        return app(MissionBusinessDateResolver::class)->todayForMission($this, $fallbackUser);
    }

    public function closeRecruitingAndCancelPending(): void
    {
        if ($this->recruiting_closed_at === null) {
            $this->update(['recruiting_closed_at' => now()]);
        }

        $this->requests()
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled',
                'responded_at' => now(),
            ]);
    }

    public function startExecution(): void
    {
        $this->requests()
            ->where('status', 'accepted')
            ->update(['status' => 'ongoing']);

        $this->update(['status' => 'in_progress']);
    }

    public function hasActiveAssignments(): bool
    {
        return $this->requests()
            ->whereIn('status', self::ACTIVE_ASSIGNMENT_STATUSES)
            ->exists();
    }

    public function completeIfReady(): bool
    {
        if ($this->status !== 'in_progress'
            || ! $this->end_date instanceof Carbon
            || $this->end_date->toDateString() > $this->businessToday()->toDateString()
            || $this->hasActiveAssignments()
            || ! $this->committedRequests()->exists()) {
            return false;
        }

        $this->update(['status' => 'completed']);

        return true;
    }
}
