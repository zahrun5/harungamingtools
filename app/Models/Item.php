<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['category_id', 'name', 'api_id', 'tier', 'enc', 'quality', 'desc'];

    public const QUALITY_MAP = [
        'Normal'      => 1,
        'Good'        => 2,
        'Outstanding' => 3,
        'Excellent'   => 4,
        'Masterpiece' => 5,
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function localizations()
    {
        return $this->hasMany(ItemLocalization::class, 'api_id', 'api_id');
    }

    public function getLocalizedNameAttribute()
    {
        $apiLocale = static::currentApiLocale();

        return ItemLocalization::nameFor($this->api_id, $apiLocale) ?? $this->name;
    }

    public static function currentApiLocale(): string
    {
        return config('item_locale.' . app()->getLocale(), 'EN-US');
    }
}