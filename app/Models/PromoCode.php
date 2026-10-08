<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'uses_count',
        'is_active',
        'expires_at',
        'is_referral',
        'referrer_user_id',
    ];

    protected function casts(): array
    {
        return [
            'value'      => 'decimal:2',
            'is_active'  => 'boolean',
            'is_referral' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function promoCodeUses(): HasMany
    {
        return $this->hasMany(PromoCodeUse::class);
    }

    public function referrerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    /**
     * Check whether this promo code is currently valid.
     * A code is valid if it is active, not expired, and has remaining uses.
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        return true;
    }
}
