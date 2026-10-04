<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'action', 'entity_type', 'entity_id', 'meta_data', 'ip_address', 'created_at'];
    protected function casts(): array { return ['meta_data' => 'array', 'created_at' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
}
