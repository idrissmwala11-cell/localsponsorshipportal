<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CenterUserAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'center_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function center()
    {
        return $this->belongsTo(Center::class, 'center_id', 'center_id');
    }
}
