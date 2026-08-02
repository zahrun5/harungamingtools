<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YoutubeChannel extends Model
{
    protected $fillable = [
        'channel_id',
        'channel_title',
        'uploads_playlist_id',
        'status',
        'source',
        'health_score',
        'last_crawled_at',
    ];
}