<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CraftingStation extends Model
{
    protected $fillable = ['slug', 'name'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'crafting_station_category');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(CraftingStationMaterial::class, 'station_id');
    }
}
