<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\CustomSoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, CustomSoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'deleted',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'deleted' => 'boolean',
    ];

    // Other existing relationships
    public function schemes(){
        return $this->hasMany(Scheme::class);
    }

    public function dataIots(){
        return $this->hasMany(DataIot::class);
    }

    /**
     * Get the API tokens that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function apiTokens()
    {
        return $this->hasMany(ApiToken::class);
    }

    /**
     * Get active API tokens that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activeApiTokens()
    {
        return $this->apiTokens()->where('active', true);
    }

    /**
     * Create a new API token for the user.
     *
     * @param \DateTime|null $expiryDate
     * @return array [$token, $displayToken]
     */
    public function createApiToken($expiryDate = null)
    {
        return ApiToken::createToken(
            $this->id,
            $expiryDate
        );
    }

    /**
     * Get the current token being used for authentication.
     *
     * @return \App\Models\ApiToken|null
     */
    public function currentApiToken()
    {
        return request()->attributes->get('api_token');
    }

    /**
     * Tokens created by this user (as admin)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdTokens()
    {
        return $this->hasMany(ApiToken::class, 'create_uid');
    }
}
