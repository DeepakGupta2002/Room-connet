<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingView extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['post_id', 'user_id', 'session_hash', 'ip_hash', 'viewed_at'];
    protected function casts(): array { return ['viewed_at' => 'datetime']; }
    public function post() { return $this->belongsTo(Post::class); }
    public function user() { return $this->belongsTo(User::class); }
}
