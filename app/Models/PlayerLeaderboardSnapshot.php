<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerLeaderboardSnapshot extends Model
{
    protected $fillable = [
        'server',
        'player_id',
        'player_name',
        'guild_id',
        'guild_name',
        'period_type',
        'period_start',
        'kill_fame',
        'death_fame',
        'rank',
        'avatar_ring',
        'computed_at',
    ];

    protected $casts = [
        'period_start' => 'date:Y-m-d',
        'computed_at' => 'datetime',
    ];

    public function scopeServer($query, string $server)
    {
        return $query->where('server', $server);
    }

    public function scopePeriod($query, string $periodType, $periodStart)
    {
        return $query->where('period_type', $periodType)->whereDate('period_start', $periodStart);
    }

    public function scopeTopRank($query)
    {
        return $query->orderBy('rank');
    }
}
