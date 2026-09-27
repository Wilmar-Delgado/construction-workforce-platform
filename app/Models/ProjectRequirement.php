<?php

namespace App\Models;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class ProjectRequirement extends Model
{
    protected $fillable = [
        'project_id',
        'name',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}