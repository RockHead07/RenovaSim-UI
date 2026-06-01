<?php

namespace Database\Seeders;

use App\Services\SupabaseService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $supabase = app(SupabaseService::class);

        // Create admin user
        $this->upsertUser($supabase, [
            'email'          => 'admin@gmail.com',
            'username'       => 'admin',
            'password'       => Hash::make('admin123'),
            'role'           => 'admin',
            'is_admin'       => true,
            'account_status' => 'active',
        ]);

        // Create test user
        $this->upsertUser($supabase, [
            'email'          => 'test@example.com',
            'username'       => 'testuser',
            'password'       => Hash::make('password'),
            'role'           => 'user',
            'is_admin'       => false,
            'account_status' => 'active',
        ]);

        $this->call([
            OwnerSeeder::class,
        ]);
    }

    private function upsertUser(SupabaseService $supabase, array $data): void
    {
        $existing = $supabase->select('users', 'id', ['email' => $data['email']]);
        if (!empty($existing)) {
            $supabase->update('users', $existing[0]['id'], $data);
        } else {
            $supabase->insert('users', $data);
        }
        $this->command?->info("User {$data['email']} ready.");
    }
}
