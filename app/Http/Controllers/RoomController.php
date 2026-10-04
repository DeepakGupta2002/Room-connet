<?php

namespace App\Http\Controllers;

use App\Models\ContactUnlock;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function show(Post $post): Response
    {
        abort_unless($this->isListingActive($post), 404);
        $post->load('owner:id,name,phone_encrypted,contact_consent_at,status', 'images:id,post_id,image_path,display_order,is_cover');

        return Inertia::render('RoomDetails', [
            'room' => $this->safeRoom($post),
        ]);
    }

    public function contact(Request $request, Post $post): Response
    {
        abort_unless($this->isListingActive($post), 404);
        $post->load('owner:id,name,phone_encrypted,contact_consent_at,status');
        $user = $request->user();
        $isDonor = $user->donor_access_until?->isFuture() ?? false;
        $limit = $isDonor ? config('roomconnect.donor_daily_contact_unlock_limit') : config('roomconnect.free_daily_contact_unlock_limit');
        $used = ContactUnlock::where('user_id', $user->id)->whereDate('unlocked_at', today())->count();
        $existing = ContactUnlock::where('user_id', $user->id)->where('post_id', $post->id)->first();

        return Inertia::render('ContactUnlock', [
            'room' => $this->safeRoom($post),
            'limits' => ['used' => $used, 'limit' => $limit, 'remaining' => max(0, $limit - $used), 'is_donor' => $isDonor],
            'alreadyUnlocked' => (bool) $existing,
            'ownerContact' => $existing ? ['name' => $post->owner?->name, 'phone' => $post->owner?->phone_encrypted] : null,
        ]);
    }

    public function unlock(Request $request, Post $post): RedirectResponse
    {
        abort_unless($this->isListingActive($post), 404);
        $post->load('owner:id,name,phone_encrypted,contact_consent_at,status');

        if (! $post->owner || ! $post->owner->phone_encrypted || ! $post->owner->contact_consent_at) {
            return back()->withErrors(['contact' => 'Is listing ke liye owner contact abhi available nahi hai.']);
        }

        $existing = ContactUnlock::where('user_id', $request->user()->id)->where('post_id', $post->id)->exists();
        if ($existing) {
            return back()->with('status', 'Aapne is room ka contact pehle hi unlock kiya hai.');
        }

        $user = $request->user();
        $isDonor = $user->donor_access_until?->isFuture() ?? false;
        $limit = $isDonor ? config('roomconnect.donor_daily_contact_unlock_limit') : config('roomconnect.free_daily_contact_unlock_limit');
        $used = ContactUnlock::where('user_id', $user->id)->whereDate('unlocked_at', today())->count();
        if ($used >= $limit) {
            return back()->withErrors(['limit' => "Aaj ki contact unlock limit ({$limit}) complete ho gayi hai."]);
        }

        $accessDays = $isDonor ? config('roomconnect.donor_access_duration_days') : config('roomconnect.contact_unlock_access_duration_days');
        DB::transaction(function () use ($user, $post, $accessDays): void {
            ContactUnlock::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'unlocked_at' => now(),
                'access_expires_at' => now()->addDays($accessDays),
            ]);
        });

        return back()->with('status', 'Owner contact unlock ho gaya.');
    }

    private function safeRoom(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'description' => $post->description,
            'rent_amount' => $post->rent_amount,
            'security_deposit' => $post->security_deposit,
            'maintenance_charge' => $post->maintenance_charge,
            'room_type' => $post->room_type,
            'listed_by_role' => $post->listed_by_role,
            'listed_by_label' => $post->listed_by_role === 'tenant' ? 'Listed by Tenant' : 'Listed by Owner',
            'city' => $post->city,
            'area' => $post->area,
            'locality' => $post->locality,
            'location_radius_meters' => $post->location_radius_meters,
            'owner_name' => $post->owner?->name,
            'owner_contact_available' => (bool) ($post->owner?->phone_encrypted && $post->owner?->contact_consent_at),
            'images' => $post->images->map(fn ($image) => ['path' => $image->image_path, 'is_cover' => $image->is_cover])->values()->all(),
            'expires_at' => $post->expires_at?->toDateString(),
        ];
    }

    private function isListingActive(Post $post): bool
    {
        return $post->listing_status === 'active'
            && $post->expires_at
            && ($post->expires_at->isToday() || $post->expires_at->isFuture());
    }
}
