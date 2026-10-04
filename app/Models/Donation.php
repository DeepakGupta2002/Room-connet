<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'post_id', 'amount', 'currency', 'provider', 'order_id', 'payment_id', 'status', 'verified_at', 'access_granted_until'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'verified_at' => 'datetime', 'access_granted_until' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
    public function post() { return $this->belongsTo(Post::class); }
    public function publicProfile() { return $this->hasOne(DonationPublicProfile::class); }
    public function contactUnlocks() { return $this->hasMany(ContactUnlock::class); }
}
