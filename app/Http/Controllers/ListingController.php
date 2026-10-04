<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    public function search(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:120'],
            'room_type' => ['nullable', 'string', 'max:32'],
            'listed_by_role' => ['nullable', 'in:tenant,owner'],
            'min_rent' => ['nullable', 'numeric', 'min:0'],
            'max_rent' => ['nullable', 'numeric', 'min:0'],
        ]);

        $query = Post::query()
            ->with('owner:id,name,phone_encrypted,contact_consent_at,status')
            ->where('listing_status', 'active')
            ->whereDate('expires_at', '>=', today())
            ->whereNull('deleted_at');

        if (! empty($filters['q'])) {
            $term = $filters['q'];
            $query->where(function ($builder) use ($term): void {
                $builder->where('title', 'like', "%{$term}%")
                    ->orWhere('city', 'like', "%{$term}%")
                    ->orWhere('area', 'like', "%{$term}%")
                    ->orWhere('locality', 'like', "%{$term}%");
            });
        }

        foreach (['city', 'area', 'room_type', 'listed_by_role'] as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        $query->when(isset($filters['min_rent']), fn ($builder) => $builder->where('rent_amount', '>=', $filters['min_rent']))
            ->when(isset($filters['max_rent']), fn ($builder) => $builder->where('rent_amount', '<=', $filters['max_rent']));

        $listings = $query->latest()->paginate(12)->withQueryString();
        $listings->through(fn (Post $post): array => $this->publicListing($post));

        return Inertia::render('Search', [
            'listings' => $listings,
            'filters' => $filters,
        ]);
    }

    public function mine(Request $request): Response
    {
        $listings = $request->user()->listedPosts()->with('owner:id,name,status')->latest()->paginate(10);
        $listings->through(fn (Post $post): array => [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'listed_by_role' => $post->listed_by_role,
            'rent_amount' => $post->rent_amount,
            'city' => $post->city,
            'area' => $post->area,
            'listing_status' => $post->listing_status,
            'verification_status' => $post->verification_status,
            'approval_status' => $post->approval_status,
            'expires_at' => $post->expires_at?->toDateString(),
            'owner_name' => $post->owner?->name,
        ]);

        return Inertia::render('MyListings', ['listings' => $listings]);
    }

    private function publicListing(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'listed_by_role' => $post->listed_by_role,
            'listed_by_label' => $post->listed_by_role === 'tenant' ? 'Listed by Tenant' : 'Listed by Owner',
            'rent_amount' => $post->rent_amount,
            'security_deposit' => $post->security_deposit,
            'maintenance_charge' => $post->maintenance_charge,
            'room_type' => $post->room_type,
            'city' => $post->city,
            'area' => $post->area,
            'locality' => $post->locality,
            'location_radius_meters' => $post->location_radius_meters,
            'owner_contact_available' => (bool) ($post->owner?->phone_encrypted && $post->owner?->contact_consent_at),
            'verification_status' => $post->verification_status,
            'expires_at' => $post->expires_at?->toDateString(),
        ];
    }
}
