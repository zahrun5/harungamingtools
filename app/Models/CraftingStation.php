<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CraftingStation extends Model
{
    protected $fillable = ['slug', 'name'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'crafting_station_category');
    }
}
