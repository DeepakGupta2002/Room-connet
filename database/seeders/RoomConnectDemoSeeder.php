<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PropertyOwner;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoomConnectDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->user('Demo Admin', 'admin@roomconnect.test', '9876500001');
        $ownerUser = $this->user('Demo Owner', 'owner@roomconnect.test', '9876500002');
        $tenantUser = $this->user('Demo Tenant', 'tenant@roomconnect.test', '9876500003');
        $seeker = $this->user('Demo Seeker', 'seeker@roomconnect.test', '9876500004');

        $this->assignRole($admin, 'admin');
        $this->assignRole($admin, 'moderator');
        $this->assignRole($ownerUser, 'owner');
        $this->assignRole($tenantUser, 'tenant');
        $this->assignRole($seeker, 'seeker');

        $owners = [
            'owner' => PropertyOwner::updateOrCreate(
                ['phone_hash' => $this->phoneHash('9876500010')],
                ['user_id' => $ownerUser->id, 'name' => 'Amit Verma', 'phone_encrypted' => '9876500010', 'phone_verified_at' => now(), 'contact_consent_at' => now(), 'status' => 'verified'],
            ),
            'tenant' => PropertyOwner::updateOrCreate(
                ['phone_hash' => $this->phoneHash('9876500011')],
                ['user_id' => null, 'name' => 'Rohit Sharma', 'phone_encrypted' => '9876500011', 'phone_verified_at' => now(), 'contact_consent_at' => now(), 'status' => 'verified'],
            ),
        ];

        $rooms = [
            [
                'slug' => 'sunny-private-room-koramangala-bengaluru', 'listed_by_user_id' => $ownerUser->id, 'owner_id' => $owners['owner']->id, 'listed_by_role' => 'owner',
                'title' => 'Sunny private room near Koramangala', 'description' => 'Fully furnished private room with attached bathroom, Wi-Fi and power backup. Suitable for working professionals.',
                'rent_amount' => 15000, 'security_deposit' => 30000, 'maintenance_charge' => 1500, 'room_type' => 'private_room', 'available_from' => now()->toDateString(),
                'city' => 'Bengaluru', 'area' => 'Koramangala', 'locality' => '5th Block', 'pincode' => '560095', 'approximate_address' => 'Around Koramangala 5th Block, Bengaluru', 'latitude' => 12.9352, 'longitude' => 77.6245,
            ],
            [
                'slug' => 'tenant-listed-shared-flat-hsr-layout-bengaluru', 'listed_by_user_id' => $tenantUser->id, 'owner_id' => $owners['tenant']->id, 'listed_by_role' => 'tenant',
                'title' => 'Room in a 3BHK shared flat in HSR Layout', 'description' => 'One room available in a clean 3BHK flat. Existing flatmates are working professionals. No brokerage.',
                'rent_amount' => 11000, 'security_deposit' => 22000, 'maintenance_charge' => 1000, 'room_type' => 'shared_flat', 'available_from' => now()->addDays(10)->toDateString(),
                'city' => 'Bengaluru', 'area' => 'HSR Layout', 'locality' => 'Sector 2', 'pincode' => '560102', 'approximate_address' => 'Around HSR Layout Sector 2, Bengaluru', 'latitude' => 12.9116, 'longitude' => 77.6389,
            ],
            [
                'slug' => 'furnished-studio-room-indiranagar-bengaluru', 'listed_by_user_id' => $ownerUser->id, 'owner_id' => $owners['owner']->id, 'listed_by_role' => 'owner',
                'title' => 'Furnished studio near Indiranagar Metro', 'description' => 'Compact furnished studio with kitchen space and good public transport connectivity. Direct owner listing.',
                'rent_amount' => 19000, 'security_deposit' => 38000, 'maintenance_charge' => 1200, 'room_type' => 'studio', 'available_from' => now()->addDays(5)->toDateString(),
                'city' => 'Bengaluru', 'area' => 'Indiranagar', 'locality' => '12th Main', 'pincode' => '560038', 'approximate_address' => 'Around Indiranagar 12th Main, Bengaluru', 'latitude' => 12.9784, 'longitude' => 77.6408,
            ],
            [
                'slug' => 'private-room-vijaynagar-indore', 'listed_by_user_id' => $tenantUser->id, 'owner_id' => $owners['tenant']->id, 'listed_by_role' => 'tenant',
                'title' => 'Private room near Vijay Nagar, Indore', 'description' => 'Bright private room in a shared apartment with kitchen access and parking. Tenant-listed, owner contact available after unlock.',
                'rent_amount' => 8500, 'security_deposit' => 17000, 'maintenance_charge' => 700, 'room_type' => 'private_room', 'available_from' => now()->addDays(15)->toDateString(),
                'city' => 'Indore', 'area' => 'Vijay Nagar', 'locality' => 'Scheme No. 54', 'pincode' => '452010', 'approximate_address' => 'Around Vijay Nagar Scheme No. 54, Indore', 'latitude' => 22.7533, 'longitude' => 75.8937,
            ],
        ];

        foreach ($rooms as $room) {
            $availableFrom = $room['available_from'];
            Post::updateOrCreate(
                ['slug' => $room['slug']],
                array_merge($room, [
                    'country' => 'India', 'state' => $room['city'] === 'Indore' ? 'Madhya Pradesh' : 'Karnataka', 'rent_type' => 'monthly',
                    'expires_at' => now()->addDays(90)->toDateString(), 'location_radius_meters' => 400, 'listing_status' => 'active',
                    'verification_status' => 'verified', 'approval_status' => 'not_required', 'trust_score' => 80, 'is_flagged' => false,
                    'next_confirmation_at' => now()->addDays(30),
                ]),
            );
        }

        $this->command?->info('RoomConnect demo data seeded. Password for all demo users: password');
    }

    private function user(string $name, string $email, string $phone): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make('password'), 'phone_encrypted' => $phone, 'phone_hash' => $this->phoneHash($phone), 'phone_verified_at' => now(), 'email_verified_at' => now(), 'status' => 'active'],
        );
    }

    private function assignRole(User $user, string $role): void
    {
        $user->roles()->syncWithoutDetaching([Role::where('name', $role)->value('id')]);
    }

    private function phoneHash(string $phone): string
    {
        return hash_hmac('sha256', $phone, config('app.key'));
    }
}
