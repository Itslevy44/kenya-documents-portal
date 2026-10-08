<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'id_number_encrypted',
        'phone',
        'address',
        'city',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the decrypted ID number.
     */
    public function getIdNumberAttribute(): ?string
    {
        if (empty($this->id_number_encrypted)) {
            return null;
        }

        return Crypt::decrypt($this->id_number_encrypted);
    }

    /**
     * Encrypt the ID number before storing.
     */
    public function setIdNumberAttribute(string $value): void
    {
        $this->attributes['id_number_encrypted'] = Crypt::encrypt($value);
    }
}
