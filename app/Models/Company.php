<?php

namespace App\Models;

use App\Models\Mission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'owner_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function missions()
    {
        return $this->hasMany(Mission::class, 'hiring_company_id');
    }
}
