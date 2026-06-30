<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemPrice extends Model
{
    protected $fillable = [
        'item_api_id',
        'enc',
        'city',
        'sell_price_min',
        'fetched_at',
    ];

    protected $casts = [
        'enc'            => 'integer',
        'sell_price_min' => 'integer',
        'fetched_at'     => 'datetime',
    ];
}
