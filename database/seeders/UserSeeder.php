<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password_hash' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'username' => 'employe',
            'email' => 'employe@gmail.com',
            'password_hash' => Hash::make('password'),
            'role' => 'production',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

    }
}
