<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    protected $fillable = ['post_id', 'user_id', 'reason', 'details', 'status', 'moderation_action', 'moderation_note', 'reviewed_by', 'reviewed_at'];
    protected function casts(): array { return ['reviewed_at' => 'datetime']; }
    public function post() { return $this->belongsTo(Post::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
