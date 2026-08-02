<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Reel extends Model
{
    use HasFactory;

    protected $fillable = [
	    "youtube_url",
	    "youtube_id",
	    "title",
	    "channel_name",
	    "thumbnail_url",
	    "added_by",
	    "is_active",
	    "review_status",
	    "found_via",
	    "youtube_channel_id",
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Scope: cuma ambil reel yang aktif, dipakai di feed publik.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * URL embed siap pakai buat iframe (autoplay + mute + loop).
     */
    public function getEmbedUrlAttribute(): string
    {
        return "https://www.youtube.com/embed/{$this->youtube_id}?autoplay=1&mute=1&loop=1&playlist={$this->youtube_id}&playsinline=1";
    }
}