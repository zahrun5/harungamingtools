<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\CalculatorUsage;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['telegram_id', 'telegram_username', 'name', 'custom_name', 'avatar_seed', 'avatar_style', 'email', 'password', 'photo_url', 'role', 'last_login_at', 'google_id', 'avatar'])]
#[Hidden(['remember_token'])]

class User extends Authenticatable
{
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
}
