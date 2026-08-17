<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShortUrl extends Model
{
    protected $fillable = [
        'original_url',
        'short_code',
        'alias',
        'expires_at',
        'password',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    public function getShortUrlAttribute(): string
    {
        return url($this->alias ?? $this->short_code);
    }

    public function hasPassword(): bool
    {
        return !empty($this->password);
    }

    public function verifyPassword(string $password): bool
    {
        return \Illuminate\Support\Facades\Hash::check($password, $this->password);
    }
}
