<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedUser extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'reason', 'blocked_until', 'blocked_by'];
    protected function casts(): array { return ['blocked_until' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
    public function blockedBy() { return $this->belongsTo(User::class, 'blocked_by'); }
}
