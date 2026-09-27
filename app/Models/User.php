<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Company;
use App\Models\Role;
use App\Models\WorkerProfile;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'company_id',
        'phone',
        'language',
        'timezone',
        'email_notifications',
        'sms_notifications',
        'project_alerts',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'project_alerts' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function ownedCompany()
    {
        return $this->hasOne(Company::class, 'owner_id');
    }

    public function workerProfiles()
    {
        return $this->hasMany(WorkerProfile::class);
    }

    public function canBeDeactivated(): bool
    {
        return ! $this->ownedCompany()->exists();
    }

    public function hasCompanyOperationalContext(): bool
    {
        return $this->company_id !== null
            && in_array($this->role?->name, ['company_owner', 'planning_manager'], true);
    }

    public function isSelfEmployed(): bool
    {
        return $this->company_id === null
            && $this->role?->name === 'self_employed';
    }

    public function canEstablishCompany(): bool
    {
        return $this->is_active
            && $this->hasVerifiedEmail()
            && $this->role?->name === 'company_owner'
            && $this->company_id === null
            && ! $this->ownedCompany()->exists();
    }
}
