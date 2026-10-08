<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OtpCode extends Model
{
    public $timestamps = false; // Table only has created_at, set manually
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected $fillable = [
        'phone',
        'code',
        'expires_at',
        'used_at',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at'    => 'datetime',
        ];
    }

    /**
     * Scope to only valid (unused, not expired) OTP codes.
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->whereNull('used_at')
                     ->where('expires_at', '>', now());
    }
}
