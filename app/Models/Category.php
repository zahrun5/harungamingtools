<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = ['parent_id', 'name', 'group', 'equipment_slot'];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }
   public function craftingStations(): BelongsToMany
    {
        return $this->belongsToMany(CraftingStation::class, 'crafting_station_category');
    }

}
