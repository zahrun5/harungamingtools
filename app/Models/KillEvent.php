<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KillEvent extends Model
{
    protected $fillable = [
        'event_id',
        'server',
        'killer_id',
        'killer_name',
        'killer_guild_id',
        'killer_guild_name',
        'killer_equipment',
        'victim_id',
        'victim_name',
        'victim_guild_id',
        'victim_guild_name',
        'kill_fame',
        'event_timestamp',
        'fetched_at',
    ];

    protected $casts = [
        'killer_equipment' => 'array',
        'event_timestamp' => 'datetime',
        'fetched_at' => 'datetime',
    ];

    public function scopeServer($query, string $server)
    {
        return $query->where('server', $server);
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('event_timestamp', [$start, $end]);
    }
}
