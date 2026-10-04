<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone_encrypted', 'phone_hash', 'phone_verified_at', 'status', 'donor_access_until', 'last_login_at', 'latitude', 'longitude'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, MustVerifyEmailTrait;

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function listedPosts()
    {
        return $this->hasMany(Post::class, 'listed_by_user_id');
    }

    public function contactPosts()
    {
        return $this->hasMany(Post::class, 'contact_user_id');
    }

    public function propertyOwners()
    {
        return $this->hasMany(PropertyOwner::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function contactUnlocks()
    {
        return $this->hasMany(ContactUnlock::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function usesSmsOtp(): bool
    {
        return config('auth.verification.mode') === 'sms';
    }

    public function requiresEmailVerification(): bool
    {
        return config('auth.verification.mode') === 'email'
            && config('auth.verification.email_required') === true;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'phone_encrypted' => 'encrypted',
            'phone_verified_at' => 'datetime',
            'donor_access_until' => 'datetime',
            'last_login_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }
}
