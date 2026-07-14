<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Build extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'notes',
        'main_hand_id',
        'off_hand_id',
        'head_id',
        'armor_id',
        'shoes_id',
        'cape_id',
        'bag_id',
        'mount_id',
        'potion_id',
        'food_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // relasi per slot, semua ke tabel items yang sama
    public function mainHand(): BelongsTo { return $this->belongsTo(Item::class, 'main_hand_id'); }
    public function offHand(): BelongsTo { return $this->belongsTo(Item::class, 'off_hand_id'); }
    public function head(): BelongsTo { return $this->belongsTo(Item::class, 'head_id'); }
    public function armor(): BelongsTo { return $this->belongsTo(Item::class, 'armor_id'); }
    public function shoes(): BelongsTo { return $this->belongsTo(Item::class, 'shoes_id'); }
    public function cape(): BelongsTo { return $this->belongsTo(Item::class, 'cape_id'); }
    public function bag(): BelongsTo { return $this->belongsTo(Item::class, 'bag_id'); }
    public function mount(): BelongsTo { return $this->belongsTo(Item::class, 'mount_id'); }
    public function potion(): BelongsTo { return $this->belongsTo(Item::class, 'potion_id'); }
    public function food(): BelongsTo { return $this->belongsTo(Item::class, 'food_id'); }

    /**
     * Ambil item untuk slot tertentu lewat nama slot (dipakai di Blade,
     * biar konsisten sama pola $equipSlots di build_show.blade.php).
     * Contoh: $build->slot('MainHand')
     */
    public function slot(string $slotName): ?Item
    {
        $map = [
            'MainHand' => 'mainHand',
            'OffHand' => 'offHand',
            'Head' => 'head',
            'Armor' => 'armor',
            'Shoes' => 'shoes',
            'Cape' => 'cape',
            'Bag' => 'bag',
            'Mount' => 'mount',
            'Potion' => 'potion',
            'Food' => 'food',
        ];

        $relation = $map[$slotName] ?? null;

        return $relation ? $this->{$relation} : null;
    }
}

