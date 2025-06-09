<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'token',
        'user_id',
        'last_hit',
        'hit_count',
        'create_uid',
        'expiry_date',
        'active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_hit' => 'datetime',
        'expiry_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'active' => 'boolean',
        'hit_count' => 'integer',
    ];

    /**
     * The attributes that should be appended.
     *
     * @var array<string>
     */
    protected $appends = ['masked_preview'];

    /**
     * Get the user that owns the token.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that created the token.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'create_uid');
    }

    /**
     * Create a new token for a user.
     *
     * @param int $userId
     * @param \DateTime|null $expiryDate
     * @return array
     */
    public static function createToken($userId, $expiryDate = null)
    {
        // Generate a random token
        $plainTextToken = Str::random(40);
        
        // Create token record
        $token = static::create([
            'token' => hash('sha256', $plainTextToken),
            'user_id' => $userId,
            'create_uid' => $userId, // User creates their own token
            'expiry_date' => $expiryDate,
            'active' => true,
        ]);
        
        // Return a public representation
        $displayToken = [
            'id' => $token->id,
            'full_token' => $plainTextToken, // This will ONLY be shown once to the user
            'masked_token' => substr($plainTextToken, 0, 3) . '...' . substr($plainTextToken, -3),
        ];
        
        return [$token, $displayToken];
    }

    /**
     * Find the token instance matching the given token.
     *
     * @param string $token
     * @return \App\Models\ApiToken|null
     */
    public static function findToken($token)
    {
        if (strpos($token, '|') !== false) {
            [$id, $token] = explode('|', $token, 2);
        }

        return static::where('token', hash('sha256', $token))
            ->where('active', true)
            ->first();
    }

    /**
     * Record a usage of this token.
     *
     * @return bool
     */
    public function recordUsage()
    {
        $this->last_hit = now();
        $this->hit_count = $this->hit_count + 1;
        
        return $this->save();
    }

    /**
     * Revoke this token.
     *
     * @return bool
     */
    public function revoke()
    {
        $this->active = false;
        
        return $this->save();
    }

    /**
     * Determine if the token has expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expiry_date !== null && now()->isAfter($this->expiry_date);
    }

    /**
     * Format the last hit date for display.
     *
     * @return string
     */
    public function getLastHitFormatted()
    {
        if (!$this->last_hit) {
            return 'Never used';
        }
        
        return $this->last_hit->diffForHumans();
    }

    /**
     * Format the expiry date for display.
     *
     * @return string
     */
    public function getExpiryFormatted()
    {
        if (!$this->expiry_date) {
            return 'Never expires';
        }
        
        return $this->expiry_date->format('M d, Y');
    }

    /**
     * Get a masked version of the token for display
     * Shows only first 3 and last 3 characters
     *
     * @return string
     */
    public function getMaskedToken()
    {
        // Note: This only masks the display token, not the stored hash
        // Since we don't store plaintext tokens, this is a placeholder
        return substr($this->token, 0, 3) . '...' . substr($this->token, -3);
    }

    /**
     * Get a masked preview of the token for display.
     *
     * @return string
     */
    public function getMaskedPreviewAttribute()
    {
        // We cannot get the plaintext token, 
        // so this just informs that this is a hashed token
        return 'token-' . substr($this->id, 0, 5);
    }

    /**
     * Disable this token.
     *
     * @return bool
     */
    public function disable()
    {
        $this->active = false;
        return $this->save();
    }

    /**
     * Enable this token.
     *
     * @return bool
     */
    public function enable()
    {
        $this->active = true;
        return $this->save();
    }
}
