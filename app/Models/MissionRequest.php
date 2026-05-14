<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionRequest extends Model
{
    protected $table = 'requests';

    protected $fillable = [
        'mission_id',
        'requested_by',
        'company_id',
        'worker_profile_id',
        'type',
        'message',
        'status',
        'responded_by',
        'responded_at',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function worker()
    {
        return $this->belongsTo(WorkerProfile::class, 'worker_profile_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
