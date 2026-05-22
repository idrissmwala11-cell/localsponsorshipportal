<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CenterNotification extends Model
{
    protected $fillable = [
        'center_id',
        'participant_id',
        'sent_by_user_id',
        'target_user_id',
        'type',
        'title',
        'message',
        'event_key',
        'due_date',
        'meta',
        'is_manual',
        'sent_to_all_users',
    ];

    protected $casts = [
        'due_date' => 'date',
        'meta' => 'array',
        'is_manual' => 'boolean',
        'sent_to_all_users' => 'boolean',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(CenterNotificationRead::class);
    }

    public function scopeForCenter($query, string|array|null $centerId)
    {
        if (is_array($centerId)) {
            return $query->whereIn('center_id', $centerId);
        }

        return $query->where('center_id', $centerId);
    }

    public function scopeManual($query)
    {
        return $query->where('is_manual', true);
    }

    public function scopeVisibleToUser($query, User $user)
    {
        return $query->where(function ($visibilityQuery) use ($user) {
            $visibilityQuery
                ->where('sent_by_user_id', $user->id)
                ->orWhere('target_user_id', $user->id)
                ->orWhere(function ($broadcastQuery) use ($user) {
                    $broadcastQuery
                        ->forCenter($user->accessibleCenterIds())
                        ->where(function ($recipientQuery) {
                            $recipientQuery
                                ->where('sent_to_all_users', true)
                                ->orWhereNull('target_user_id');
                        });
                });
        });
    }
}
