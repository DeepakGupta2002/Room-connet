<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateLimitEvent extends Model
{
    use HasFactory;
    protected $fillable = ['subject_hash', 'action', 'count', 'window_started_at', 'last_request_at'];
    protected function casts(): array { return ['window_started_at' => 'datetime', 'last_request_at' => 'datetime']; }
}
