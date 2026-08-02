<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuildLeaderboardSnapshot extends Model
{
    protected $fillable = [
        'server',
        'guild_id',
        'guild_name',
        'period_type',
        'period_start',
        'kill_fame',
        'death_fame',
        'rank',
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
