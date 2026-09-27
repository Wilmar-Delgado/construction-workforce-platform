<?php

namespace App\Models;

use App\Models\Mission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'owner_id',
        'timezone',
    ];

    public function businessTimezone(): string
    {
        return is_string($this->timezone)
            && array_key_exists($this->timezone, config('timezones.supported'))
            ? $this->timezone
            : 'UTC';
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function missions()
    {
        return $this->hasMany(Mission::class, 'hiring_company_id');
    }

    public function members()
    {
        return $this->hasMany(User::class);
    }

    public function invitations()
    {
        return $this->hasMany(CompanyInvitation::class);
    }

    public function hasValidOwner(): bool
    {
        $owner = $this->owner()->with('role')->first();

        return $owner !== null
            && $owner->is_active
            && $owner->company_id === $this->id
            && $owner->role?->name === 'company_owner';
    }
}
