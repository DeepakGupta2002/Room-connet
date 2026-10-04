<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use App\Models\PropertyOwner;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $phone = preg_replace('/[^0-9+]/', '', $data['owner_phone']);
        $phoneHash = hash_hmac('sha256', $phone, config('app.key'));

        DB::transaction(function () use ($request, $data, $phone, $phoneHash): void {
            $owner = PropertyOwner::create([
                'user_id' => $data['listed_by_role'] === 'owner' ? $request->user()->id : null,
                'name' => $data['owner_name'],
                'phone_encrypted' => $phone,
                'phone_hash' => $phoneHash,
                'contact_consent_at' => now(),
                'status' => 'unverified',
            ]);

            $availableFrom = Carbon::parse($data['available_from']);
            $leavingDate = ! empty($data['leaving_date']) ? Carbon::parse($data['leaving_date']) : null;
            $expiresAt = ($leavingDate ?? $availableFrom->copy()->addDays(90))->addDays($leavingDate ? 15 : 0);
            $slug = $this->uniqueSlug($data['title'], $data['city'], $data['area']);

            Post::create([
                'listed_by_user_id' => $request->user()->id,
                'owner_id' => $owner->id,
                'listed_by_role' => $data['listed_by_role'],
                'title' => $data['title'],
                'description' => $data['description'],
                'rent_amount' => $data['rent_amount'],
                'security_deposit' => $data['security_deposit'] ?? null,
                'maintenance_charge' => $data['maintenance_charge'] ?? null,
                'room_type' => $data['room_type'],
                'leaving_date' => $leavingDate,
                'available_from' => $availableFrom,
                'expires_at' => $expiresAt,
                'city' => $data['city'],
                'area' => $data['area'],
                'locality' => $data['locality'] ?? null,
                'pincode' => $data['pincode'] ?? null,
                'approximate_address' => $data['approximate_address'] ?? null,
                'slug' => $slug,
                'listing_status' => 'active',
                'verification_status' => 'unverified',
                'approval_status' => config('roomconnect.owner_approval_required', false) ? 'pending' : 'not_required',
            ]);
        });

        return back()->with('status', 'Room listing successfully create ho gayi.');
    }

    private function uniqueSlug(string $title, string $city, string $area): string
    {
        $base = Str::limit(Str::slug($title.'-'.$area.'-'.$city), 205, '');
        $slug = $base;
        $counter = 2;

        while (Post::where('slug', $slug)->exists()) {
            $slug = Str::limit($base, 195, '').'-'.$counter++;
        }

        return $slug;
    }
}
