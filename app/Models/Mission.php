<?php

namespace App\Models;

use App\Models\Company;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\MissionRequirement;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
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
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
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
}
