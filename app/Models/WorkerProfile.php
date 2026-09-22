<?php

namespace App\Models;

use App\Models\Certification;
use App\Models\Company;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class WorkerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'job',
        'years_experience',
        'hourly_rate',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    protected $appends = [
        'rating',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function skills()
    {
        return $this->belongsToMany(
            Skill::class,
            'skill_worker_profile',
            'worker_profile_id',
            'skill_id'
        );
    }

    public function certifications()
    {
        return $this->belongsToMany(
            Certification::class,
            'certification_worker_profile',
            'worker_profile_id',
            'certification_id'
        );
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'worker_profile_id');
    }

    public function requests()
    {
        return $this->hasMany(WorkerRequest::class, 'worker_profile_id');
    }

    /**
     * Legacy direct worker-to-mission references still need retention protection.
     */
    public function directMissions()
    {
        return $this->hasMany(Mission::class, 'worker_profile_id');
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class, 'worker_profile_id');
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function hasBusinessHistory(): bool
    {
        return $this->requests()->exists()
            || $this->ratings()->exists()
            || $this->directMissions()->exists();
    }

    public function hasActiveBusinessActivity(): bool
    {
        return $this->requests()
            ->whereIn('status', ['pending', 'accepted', 'ongoing'])
            ->exists()
            || $this->directMissions()
                ->whereIn('status', ['draft', 'open', 'in_progress'])
                ->exists();
    }

    public function canBePermanentlyDeleted(): bool
    {
        return $this->archived_at === null
            && ! $this->hasBusinessHistory();
    }

    public function canBeArchived(): bool
    {
        return $this->archived_at === null
            && $this->hasBusinessHistory()
            && ! $this->hasActiveBusinessActivity();
    }

    /**
     * Archived profiles remain available to historical relationships, but may
     * not participate in new operational workflows.
     */
    public function isOperationallyAvailable(): bool
    {
        return $this->archived_at === null;
    }

    /**
     * The rounded display value for the average ratings aggregate.
     *
     * Controllers load ratings_avg_score with withAvg(), so this accessor
     * never performs an additional database query.
     */
    public function getRatingAttribute(): ?float
    {
        $average = $this->attributes['ratings_avg_score'] ?? null;

        return $average === null ? null : round((float) $average, 1);
    }
}
