<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'generated_document_id',
        'user_id',
        'phone',
        'amount',
        'mpesa_receipt',
        'payhero_reference',
        'status',
        'callback_payload',
        'promo_code_id',
        'discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'callback_payload' => 'array',
            'amount'           => 'decimal:2',
            'discount_amount'  => 'decimal:2',
        ];
    }

    public function generatedDocument(): BelongsTo
    {
        return $this->belongsTo(GeneratedDocument::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }
}
