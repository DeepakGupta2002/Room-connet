<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    use HasFactory;
    protected $table = 'blocked_ips';
    protected $fillable = ['ip_address', 'reason', 'blocked_until'];
    protected function casts(): array { return ['blocked_until' => 'datetime']; }
}
