<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $fillable = [
        'platform', 'external_id', 'name', 'username', 'access_token', 'token_expires_at', 'page_id',
        'enabled', 'last_error', 'connected_by',
    ];

    protected $hidden = ['access_token'];

    protected function casts(): array
    {
        return ['access_token' => 'encrypted', 'token_expires_at' => 'datetime', 'enabled' => 'boolean'];
    }

    public function token(): ?string
    {
        try {
            return $this->access_token;
        } catch (DecryptException) {
            return null;
        }
    }

    public function label(): string
    {
        return $this->platform === 'instagram' ? '@'.($this->username ?: $this->name) : $this->name;
    }
}
