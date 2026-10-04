<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUnlock extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'post_id', 'donation_id', 'unlocked_at', 'access_expires_at'];
    protected function casts(): array { return ['unlocked_at' => 'datetime', 'access_expires_at' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
    public function post() { return $this->belongsTo(Post::class); }
    public function donation() { return $this->belongsTo(Donation::class); }
}
