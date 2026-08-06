<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantTreatment extends Model
{
    protected $fillable = [
        'participant_id',
        'created_by_user_id',
        'center_id',
        'treatment',
        'treatment_date',
        'tested_diseases',
        'illness_type',
        'treatment_location',
        'treatment_cost',
        'health_comments',
    ];

    protected $casts = [
        'treatment_date' => 'date',
        'treatment_cost' => 'decimal:2',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeVisibleToUser($query, User $user)
    {
        return $query->whereIn('center_id', $user->accessibleCenterIds());
    }
}
