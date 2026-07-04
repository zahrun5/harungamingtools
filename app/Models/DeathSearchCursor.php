<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeathSearchCursor extends Model
{
    protected $table = 'death_search_cursor';

    protected $fillable = [
        'character_id',
        'kills_offset',
        'deaths_offset',
        'kills_exhausted',
        'deaths_exhausted',
        'last_fetched_at',
    ];

    protected $casts = [
        'kills_exhausted' => 'boolean',
        'deaths_exhausted' => 'boolean',
        'last_fetched_at' => 'datetime',
    ];
}