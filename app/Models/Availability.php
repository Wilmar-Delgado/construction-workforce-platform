<?php

namespace App\Models;

use App\Models\WorkerProfile;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $fillable = [
        'worker_profile_id',
        'date',
        'start_time',
        'end_time',
        'status',
    ];

    public function workerProfile()
    {
        return $this->belongsTo(WorkerProfile::class);
    }
}
