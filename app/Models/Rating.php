<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'mission_id',
        'reviewed_by_user_id',
        'worker_profile_id',
        'score',
        'feedback',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function worker()
    {
        return $this->belongsTo(WorkerProfile::class, 'worker_profile_id');
    }
}
