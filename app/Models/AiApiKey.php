<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;

class AiApiKey extends Model
{
    protected $fillable = [
        'provider', 'label', 'api_key', 'last4', 'enabled', 'priority', 'use_count', 'fail_count',
        'last_used_at', 'last_error', 'cooldown_until', 'created_by',
    ];

    // Never serialised to arrays/JSON, never shown in admin.
    protected $hidden = ['api_key'];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'enabled' => 'boolean',
            'last_used_at' => 'datetime',
            'cooldown_until' => 'datetime',
        ];
    }

    /** The plain key, or null if it can't be decrypted (e.g. APP_KEY changed). */
    public function secret(): ?string
    {
        try {
            return $this->api_key;
        } catch (DecryptException) {
            return null;
        }
    }

    public function masked(): string
    {
        return '••••'.$this->last4;
    }

    public function isCoolingDown(): bool
    {
        return $this->cooldown_until !== null && $this->cooldown_until->isFuture();
    }
}
