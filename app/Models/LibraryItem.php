<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LibraryItem extends Model
{
    protected $fillable = [
        'title',
        'description',
        'file_name',
        'telegram_file_id',
        'file_size',
        'mime_type',
        'category',
        'is_free',
        'download_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_free'   => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope to active library items.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to free library items.
     */
    public function scopeFree(Builder $query): Builder
    {
        return $query->where('is_free', true);
    }
}
