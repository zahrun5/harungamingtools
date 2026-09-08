<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlipScan extends Model
{
    protected $fillable = [
        'filter_hash',
        'sub_category_id',
        'tier',
        'enchant',
        'server',
        'results',
        'result_count',
        'scanned_at',
    ];

    protected $casts = [
        'results'    => 'array',
        'scanned_at' => 'datetime',
    ];

    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }
}
