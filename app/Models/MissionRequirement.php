<?php

namespace App\Models;

use App\Models\Mission;
use Illuminate\Database\Eloquent\Model;

class MissionRequirement extends Model
{
    protected $fillable = [
        'mission_id',
        'name',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }
}