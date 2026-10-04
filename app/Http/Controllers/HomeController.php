<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        try {
            $listings = Post::query()
                ->with('owner:id,name,phone_encrypted,contact_consent_at,status')
                ->where('listing_status', 'active')
                ->whereDate('expires_at', '>=', today())
                ->latest()
                ->limit(3)
                ->get()
                ->map(fn (Post $post): array => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'listed_by_label' => $post->listed_by_role === 'tenant' ? 'Listed by Tenant' : 'Listed by Owner',
                    'rent_amount' => $post->rent_amount,
                    'room_type' => $post->room_type,
                    'city' => $post->city,
                    'area' => $post->area,
                    'owner_contact_available' => (bool) ($post->owner?->phone_encrypted && $post->owner?->contact_consent_at),
                ])
                ->values();
        } catch (\Throwable $exception) {
            report($exception);
            $listings = collect();
        }

        return Inertia::render('Home', [
            'featuredListings' => $listings,
            'isAuthenticated' => $request->user() !== null,
        ]);
    }
}
