<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantSponsorship extends Model
{
    protected $fillable = [
        'participant_id',
        'created_by_user_id',
        'funding_type',
        'sponsorship_status',
        'sponsored_by',
        'sponsor_name',
        'sponsor_type',
        'sponsorship_type',
        'sponsor_physical_address',
        'sponsor_contact',
        'sponsorship_start_date',
        'sponsorship_category',
    ];

    protected $casts = [
        'sponsorship_start_date' => 'date',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeForCenter($query, string|array|null $centerId)
    {
        return $query->whereHas('participant', function ($participantQuery) use ($centerId) {
            if (is_array($centerId)) {
                $participantQuery->whereIn('center_id', $centerId);
                return;
            }

            $participantQuery->where('center_id', $centerId);
        });
    }

    public function scopeVisibleToUser($query, User $user)
    {
        $query->forCenter($user->accessibleCenterIds());

        if ($user->role === User::ROLE_USER) {
            $query->where('created_by_user_id', $user->id);
        }

        return $query;
    }
}
