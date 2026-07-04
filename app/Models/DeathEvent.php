<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeathEvent extends Model
{
    protected $fillable = [
        'character_name',
        'character_id',
        'event_id',
        'type',
        'event_timestamp',
        'event_data',
        'total_fame',
    ];

    protected $casts = [
        'event_data' => 'array',
        'event_timestamp' => 'datetime',
    ];
}