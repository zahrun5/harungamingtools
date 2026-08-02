<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\CalculatorUsage;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


#[Fillable([
    'telegram_id', 'telegram_username', 'name', 'custom_name', 'avatar_seed',
    'avatar_style', 'email', 'password', 'photo_url', 'role', 'last_login_at',
    'google_id', 'avatar', 'is_public',
    // Identitas Albion Online (Tahap 2 — planning-profil-publik.md)
    'albion_ign', 'albion_player_id', 'albion_server',
    'albion_guild_name', 'albion_alliance_name', 'albion_alliance_tag',
    'albion_avatar_code', 'albion_avatar_ring',
    'kill_fame', 'death_fame', 'average_item_power',
    'lifetime_statistics', 'albion_data_refreshed_at',
    'verification_status', 'verification_code',
    'verification_code_expires_at', 'verified_at',
])]
#[Hidden(['remember_token'])]

class User extends Authenticatable
{
    public function resolveRouteBinding($value, $field = null)
{
    if (ctype_digit((string) $value)) {
        return $this->where('id', $value)->firstOrFail();
    }

    return $this->where('custom_name', $value)->firstOrFail();
}
    
    
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
protected function casts(): array
{
    return [
        'last_login_at' => 'datetime',
        'lifetime_statistics' => 'array',
        'albion_data_refreshed_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_public' => 'boolean',
    ];
}

public function getDisplayNameAttribute(): string
{
    return $this->custom_name ?? $this->name;
}

public function getDisplayAvatarAttribute(): string
{
    if ($this->avatar_seed) {
        return "https://api.dicebear.com/9.x/{$this->avatar_style}/svg?seed={$this->avatar_seed}";
    }
    return $this->photo_url ?? $this->avatar ?? 'https://api.dicebear.com/9.x/pixel-art/svg?seed=default';
}

public function calculatorUsages()
{
    return $this->hasMany(CalculatorUsage::class);
}

// Tahap 3 — Build & Set: 1 user bisa punya banyak build
public function builds(): HasMany
{
    return $this->hasMany(Build::class);
}

// build yang lagi "dipasang" ke profil publik (is_active = true), max 1 per user
public function activeBuild(): HasOne
{
    return $this->hasOne(Build::class)->where('is_active', true);
}
}
