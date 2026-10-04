<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyOwner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'name', 'phone_encrypted', 'phone_hash', 'phone_verified_at', 'contact_consent_at', 'status'];

    protected function casts(): array
    {
        return ['phone_encrypted' => 'encrypted', 'phone_verified_at' => 'datetime', 'contact_consent_at' => 'datetime'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function posts() { return $this->hasMany(Post::class, 'owner_id'); }
    public function approvals() { return $this->hasMany(OwnerApproval::class, 'owner_id'); }
}
