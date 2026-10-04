<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationPublicProfile extends Model
{
    use HasFactory;
    protected $fillable = ['donation_id', 'public_name', 'is_anonymous', 'show_amount'];
    protected function casts(): array { return ['is_anonymous' => 'boolean', 'show_amount' => 'boolean']; }
    public function donation() { return $this->belongsTo(Donation::class); }
}
