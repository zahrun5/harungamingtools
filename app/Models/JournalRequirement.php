<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalRequirement extends Model
{
    protected $fillable = [
        'journal_name',
        'tier',
        'unique_name',
        'max_fame',
        'base_loot_amount',
    ];

    protected $casts = [
        'tier' => 'integer',
        'max_fame' => 'integer',
        'base_loot_amount' => 'decimal:4',
    ];
}
