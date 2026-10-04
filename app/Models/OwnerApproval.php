<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerApproval extends Model
{
    use HasFactory;
    protected $fillable = ['post_id', 'owner_id', 'token_hash', 'status', 'expires_at', 'used_at', 'rejection_reason'];
    protected function casts(): array { return ['expires_at' => 'datetime', 'used_at' => 'datetime']; }
    public function post() { return $this->belongsTo(Post::class); }
    public function owner() { return $this->belongsTo(PropertyOwner::class, 'owner_id'); }
}
