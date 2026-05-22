<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChurchProfile extends Model
{
    protected $appends = [
        'photo_urls',
    ];

    protected $fillable = [
        'center_id',
        'created_by_user_id',
        'church_name',
        'historical_background',
        'mission',
        'vision',
        'photo_paths',
    ];

    protected $casts = [
        'photo_paths' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getPhotoUrlsAttribute(): array
    {
        return collect($this->photo_paths ?? [])
            ->filter()
            ->map(fn (string $path) => route('media.public', ['path' => $path]))
            ->values()
            ->all();
    }
}
