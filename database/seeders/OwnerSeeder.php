<?php

namespace Database\Seeders;

use App\Services\SupabaseService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        $supabase = app(SupabaseService::class);

        $email    = 'IAMGOD@gmail.com';
        $existing = $supabase->select('users', 'id', ['email' => $email]);

        $data = [
            'name'           => 'GOD',
            'username'       => 'GOD',
            'email'          => $email,
            'password'       => Hash::make('GODISGOOD'),
            'role'           => 'owner',
            'is_admin'       => true,
            'account_status' => 'active',
        ];

        if (!empty($existing)) {
            $id = $existing[0]['id'];
            $supabase->update('users', $id, $data);
            $this->command?->info("Owner user updated (id={$id}).");
        } else {
            $result = $supabase->insert('users', $data);
            $id = is_array($result) ? ($result[0]['id'] ?? '?') : '?';
            $this->command?->info("Owner user created (id={$id}).");
        }
    }
}
