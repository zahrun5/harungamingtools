<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CraftingStationMaterial extends Model
{
    protected $fillable = [
        'station_id',
        'item_id',
        'category_id',
        'api_id',
        'enc',
        'tier',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(CraftingStation::class, 'station_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
