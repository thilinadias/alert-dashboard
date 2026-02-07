<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OAuthToken extends Model
{
    use HasFactory;

    protected $table = 'oauth_tokens';

    protected $fillable = [
        'email',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Check if the access token is expired
     */
    public function isExpired(): bool
    {
        return Carbon::now()->greaterThanOrEqualTo($this->expires_at);
    }

    /**
     * Get the token for a specific email
     */
    public static function getForEmail(string $email): ?self
    {
        return self::where('email', $email)->first();
    }
}
