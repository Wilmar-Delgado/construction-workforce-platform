<?php

namespace App\Models;

use App\Models\Certification;
use App\Models\Company;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WorkerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'job',
        'years_experience',
        'hourly_rate',
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
}
