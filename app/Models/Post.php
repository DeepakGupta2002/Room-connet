<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = ['listed_by_user_id', 'owner_id', 'contact_user_id', 'listed_by_role', 'title', 'description', 'rent_amount', 'rent_type', 'security_deposit', 'maintenance_charge', 'room_type', 'leaving_date', 'available_from', 'expires_at', 'country', 'state', 'city', 'area', 'locality', 'pincode', 'approximate_address', 'latitude', 'longitude', 'location_radius_meters', 'slug', 'listing_status', 'verification_status', 'approval_status', 'trust_score', 'is_flagged', 'last_owner_confirmed_at', 'next_confirmation_at'];

    protected function casts(): array
    {
        return ['rent_amount' => 'decimal:2', 'security_deposit' => 'decimal:2', 'maintenance_charge' => 'decimal:2', 'leaving_date' => 'date', 'available_from' => 'date', 'expires_at' => 'date', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'is_flagged' => 'boolean', 'last_owner_confirmed_at' => 'datetime', 'next_confirmation_at' => 'datetime'];
    }

    public function listedBy() { return $this->belongsTo(User::class, 'listed_by_user_id'); }
    public function owner() { return $this->belongsTo(PropertyOwner::class, 'owner_id'); }
    public function contactUser() { return $this->belongsTo(User::class, 'contact_user_id'); }
    public function images() { return $this->hasMany(PostImage::class); }
    public function donations() { return $this->hasMany(Donation::class); }
    public function contactUnlocks() { return $this->hasMany(ContactUnlock::class); }
    public function favorites() { return $this->hasMany(Favorite::class); }
    public function reports() { return $this->hasMany(Report::class); }
    public function approvals() { return $this->hasMany(OwnerApproval::class); }
    public function views() { return $this->hasMany(ListingView::class); }
}
