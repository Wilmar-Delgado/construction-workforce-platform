<?php

namespace App\Models;

use App\Models\WorkerProfile;
use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $fillable = ['name'];

    public function workers()
    {
        return $this->belongsToMany(
            WorkerProfile::class,
            'certification_worker_profile',
            'certification_id',
            'worker_profile_id'
        );
    }
}
