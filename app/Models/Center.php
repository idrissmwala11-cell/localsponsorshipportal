<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Center extends Model
{
    protected $fillable = [
        'center_id',
        'center_name',
        'cluster_name',
        'facilitator_name',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'center_id', 'center_id');
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'center_user_assignments', 'center_id', 'user_id', 'center_id', 'id');
    }
}
