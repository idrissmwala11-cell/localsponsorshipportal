<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminClusterAssignment extends Model
{
    protected $fillable = [
        'admin_user_id',
        'cluster_name',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
