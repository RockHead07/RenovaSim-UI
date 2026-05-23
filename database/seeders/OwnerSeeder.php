<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'IAMGOD@gmail.com',
            ],
            [
                'name' => 'Owner',
                'username' => 'IAMGOD',
                'email' => 'IAMGOD@gmail.com',
                'password' => Hash::make('GODISGOOD'),
                'role' => 'owner',
                'account_status' => 'active',
            ]
        );
    }
}
