<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramAttendanceSession extends Model
{
    protected $appends = [
        'activity_photo_url',
        'activity_photo_urls',
        'activity_photo_gallery',
    ];

    protected $fillable = [
        'center_id',
        'created_by_user_id',
        'attendance_type',
        'attendance_date',
        'activity_name',
        'activity_photo_path',
        'activity_photo_caption',
        'activity_photo_paths',
        'activity_photo_captions',
        'instructor_name',
        'topic',
        'comment',
        'present_count',
        'absent_count',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'activity_photo_paths' => 'array',
        'activity_photo_captions' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function entries()
    {
        return $this->hasMany(ProgramAttendanceEntry::class);
    }

    public function scopeVisibleToUser($query, User $user)
    {
        $query->whereIn('center_id', $user->accessibleCenterIds());

        if ($user->role === User::ROLE_USER) {
            $query->where('created_by_user_id', $user->id);
        }

        return $query;
    }

    public function getActivityPhotoUrlAttribute(): ?string
    {
        return $this->activity_photo_path
            ? route('media.public', ['path' => $this->activity_photo_path])
            : null;
    }

    public function getActivityPhotoUrlsAttribute(): array
    {
        $paths = collect($this->activity_photo_paths ?? [])
            ->filter()
            ->values();

        if ($paths->isEmpty() && filled($this->activity_photo_path)) {
            $paths = collect([$this->activity_photo_path]);
        }

        return $paths
            ->map(fn (string $path) => route('media.public', ['path' => $path]))
            ->all();
    }

    public function getActivityPhotoGalleryAttribute(): array
    {
        $paths = collect($this->activity_photo_paths ?? [])
            ->filter()
            ->values();
        $captions = collect($this->activity_photo_captions ?? [])->values();

        if ($paths->isEmpty() && filled($this->activity_photo_path)) {
            $paths = collect([$this->activity_photo_path]);
            $captions = collect([$this->activity_photo_caption]);
        }

        return $paths
            ->map(function (string $path, int $index) use ($captions) {
                return [
                    'url' => route('media.public', ['path' => $path]),
                    'caption' => $captions->get($index),
                ];
            })
            ->all();
    }
}
