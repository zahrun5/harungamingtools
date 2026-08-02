<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Build extends Model
{
    protected $fillable = [
        'name', 'notes', 'is_active',
        'main_hand_id', 'off_hand_id', 'head_id', 'armor_id', 'shoes_id',
        'cape_id', 'bag_id', 'mount_id', 'potion_id', 'food_id',
        'qualities',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'qualities' => 'array',
        ];
    }

    // dipakai buat eager-load semua slot sekaligus: Build::with(Build::SLOT_RELATIONS)
    public const SLOT_RELATIONS = [
        'mainHand', 'offHand', 'head', 'armor', 'shoes',
        'cape', 'bag', 'mount', 'potion', 'food',
    ];

    private const SLOT_TO_RELATION = [
        'MainHand' => 'mainHand', 'OffHand' => 'offHand', 'Head' => 'head',
        'Armor' => 'armor', 'Shoes' => 'shoes', 'Cape' => 'cape',
        'Bag' => 'bag', 'Mount' => 'mount', 'Potion' => 'potion', 'Food' => 'food',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mainHand(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'main_hand_id');
    }

    public function offHand(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'off_hand_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'head_id');
    }

    public function armor(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'armor_id');
    }

    public function shoes(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'shoes_id');
    }

    public function cape(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'cape_id');
    }

    public function bag(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'bag_id');
    }

    public function mount(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'mount_id');
    }

    public function potion(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'potion_id');
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'food_id');
    }

    /**
     * Dipakai dari blade (builds/_form.blade.php, builds/_paperdoll.blade.php):
     * $build->slot('MainHand') -> instance Item|null
     */
    public function slot(string $slotName): ?Item
    {
        $relation = self::SLOT_TO_RELATION[$slotName] ?? null;

        return $relation ? $this->{$relation} : null;
    }

    /**
     * Quality VISUAL (1-5) yang dipilih user buat slot ini. Item di DB tetap Normal,
     * ini cuma dipakai buat nambah suffix ?quality=N ke URL render item.
     * $field = nama kolom, misal 'head_id' (bukan nama slot 'Head').
     */
    public function qualityFor(string $field): int
    {
        return (int) ($this->qualities[$field] ?? 1);
    }
}
