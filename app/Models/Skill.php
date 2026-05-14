<?php

namespace App\Models;

use App\Models\WorkerProfile;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['name'];

    public function workers()
    {
        return $this->belongsToMany(
            WorkerProfile::class,
            'skill_worker_profile',
            'skill_id',
            'worker_profile_id'
        );
    }
}
