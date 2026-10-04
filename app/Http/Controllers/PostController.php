<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\PropertyOwner;
use App\Models\PostImage;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PostController extends Controller
{
    public function edit(Post $post)
    {
        $this->authorizeOwner($post);
        $post->load('owner:id,name,phone_encrypted', 'images:id,post_id,image_path,display_order,is_cover');

        return \Inertia\Inertia::render('EditListing', [
            'listing' => [
                'id' => $post->id, 'slug' => $post->slug, 'title' => $post->title, 'description' => $post->description,
                'rent_amount' => $post->rent_amount, 'security_deposit' => $post->security_deposit,
                'maintenance_charge' => $post->maintenance_charge, 'room_type' => $post->room_type,
                'available_from' => $post->available_from?->toDateString(), 'leaving_date' => $post->leaving_date?->toDateString(),
                'city' => $post->city, 'area' => $post->area, 'locality' => $post->locality, 'pincode' => $post->pincode,
                'approximate_address' => $post->approximate_address, 'latitude' => $post->latitude, 'longitude' => $post->longitude,
                'owner_name' => $post->owner?->name, 'owner_phone' => $post->owner?->phone_encrypted,
                'images' => $post->images->map(fn ($image) => ['path' => Storage::disk('public')->url($image->image_path)])->values()->all(),
            ],
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $phone = preg_replace('/[^0-9+]/', '', $data['owner_phone']);
        $phoneHash = hash_hmac('sha256', $phone, config('app.key'));
        $newFiles = [];

        try {
            DB::transaction(function () use ($request, $data, $phone, $phoneHash, &$newFiles): void {
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

            $post = Post::create([
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
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'location_radius_meters' => 400,
                'slug' => $slug,
                'listing_status' => 'active',
                'verification_status' => 'unverified',
                'approval_status' => config('roomconnect.owner_approval_required', false) ? 'pending' : 'not_required',
            ]);

            foreach ($request->file('images', []) as $index => $image) {
                $path = $image->store('room-images', 'public');
                $newFiles[] = $path;
                PostImage::create([
                    'post_id' => $post->id,
                    'image_path' => $path,
                    'mime_type' => $image->getMimeType(),
                    'file_size' => $image->getSize(),
                    'display_order' => $index,
                    'is_cover' => $index === 0,
                ]);
            }
            });
        } catch (Throwable $exception) {
            foreach ($newFiles as $path) Storage::disk('public')->delete($path);
            Log::error('Room listing creation failed', ['user_id' => $request->user()->id, 'exception' => $exception->getMessage()]);
            return back()->withErrors(['system' => 'Room listing could not be created. Please try again.']);
        }

        return back()->with('status', 'Room listing created successfully.');
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $this->authorizeOwner($post);
        $data = $request->validated();
        $phone = preg_replace('/[^0-9+]/', '', $data['owner_phone']);
        $phoneHash = hash_hmac('sha256', $phone, config('app.key'));
        $newFiles = [];
        $oldFiles = [];

        try {
            DB::transaction(function () use ($request, $data, $post, $phone, $phoneHash, &$newFiles, &$oldFiles): void {
                $availableFrom = Carbon::parse($data['available_from']);
                $leavingDate = ! empty($data['leaving_date']) ? Carbon::parse($data['leaving_date']) : null;
                $post->update([
                    'title' => $data['title'], 'description' => $data['description'], 'rent_amount' => $data['rent_amount'],
                    'security_deposit' => $data['security_deposit'] ?? null, 'maintenance_charge' => $data['maintenance_charge'] ?? null,
                    'room_type' => $data['room_type'], 'available_from' => $availableFrom, 'leaving_date' => $leavingDate,
                    'expires_at' => ($leavingDate ?? $availableFrom->copy()->addDays(90))->addDays($leavingDate ? 15 : 0),
                    'city' => $data['city'], 'area' => $data['area'], 'locality' => $data['locality'] ?? null,
                    'pincode' => $data['pincode'] ?? null, 'approximate_address' => $data['approximate_address'] ?? null,
                    'latitude' => $data['latitude'] ?? null, 'longitude' => $data['longitude'] ?? null,
                ]);

                $post->owner?->update(['name' => $data['owner_name'], 'phone_encrypted' => $phone, 'phone_hash' => $phoneHash, 'contact_consent_at' => now()]);

                if ($request->hasFile('images')) {
                    $oldFiles = $post->images()->pluck('image_path')->all();
                    $post->images()->delete();
                    foreach ($request->file('images', []) as $index => $image) {
                        $path = $image->store('room-images', 'public');
                        $newFiles[] = $path;
                        PostImage::create(['post_id' => $post->id, 'image_path' => $path, 'mime_type' => $image->getMimeType(), 'file_size' => $image->getSize(), 'display_order' => $index, 'is_cover' => $index === 0]);
                    }
                }
            });

            foreach ($oldFiles as $path) Storage::disk('public')->delete($path);
            return back()->with('status', 'Listing updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            foreach ($newFiles as $path) Storage::disk('public')->delete($path);
            Log::error('Room listing update failed', ['post_id' => $post->id, 'user_id' => $request->user()->id, 'exception' => $exception->getMessage()]);
            return back()->withErrors(['system' => 'Listing could not be updated. Please try again.']);
        }
    }

    private function authorizeOwner(Post $post): void
    {
        abort_unless($post->listed_by_user_id === auth()->id(), 403);
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
