<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('roles')->upsert([
            ['name' => 'seeker', 'display_name' => 'Seeker', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'tenant', 'display_name' => 'Tenant', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'owner', 'display_name' => 'Owner', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'moderator', 'display_name' => 'Moderator', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin', 'display_name' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
        ], ['name'], ['display_name', 'updated_at']);

        $this->call(RoomConnectDemoSeeder::class);
    }
}
