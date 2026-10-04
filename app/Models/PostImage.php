<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    use HasFactory;
    protected $fillable = ['post_id', 'image_path', 'mime_type', 'file_size', 'display_order', 'is_cover'];
    protected function casts(): array { return ['is_cover' => 'boolean']; }
    public function post() { return $this->belongsTo(Post::class); }
}
