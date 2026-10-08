<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeneratedDocument extends Model
{
    protected $fillable = [
        'user_id',
        'session_token',
        'template_id',
        'form_data',
        'status',
        'pdf_path',
        'word_path',
        'preview_path',
        'edit_count',
        'edit_window_expires_at',
        'payment_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'form_data'               => 'array',
            'edit_window_expires_at'  => 'datetime',
            'payment_completed_at'    => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }
}
