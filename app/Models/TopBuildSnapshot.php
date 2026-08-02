<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopBuildSnapshot extends Model
{
    protected $fillable = [
        'server',
        'period_type',
        'period_start',
        'equipment',
        'usage_count',
        'computed_at',
    ];

    protected $casts = [
        'equipment' => 'array',
        'period_start' => 'date:Y-m-d',
        'computed_at' => 'datetime',
    ];

    public function scopeServer($query, string $server)
    {
        return $query->where('server', $server);
    }

    public function scopePeriod($query, string $periodType, $periodStart)
    {
        return $query->where('period_type', $periodType)->where('period_start', $periodStart);
    }

    public function scopeMostUsed($query)
    {
        return $query->orderByDesc('usage_count');
    }
}
