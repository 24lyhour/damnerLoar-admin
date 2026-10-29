<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the default admin account. Safe to run more than once.
     */
    public function run(): void
    {
        User::firstOrNew(['email' => 'kouchlyhour@gmail.com'])
            ->forceFill([
                'name' => 'Kouch Lyhour',
                'password' => '12345678',
                'email_verified_at' => now(),
            ])
            ->save();
    }
}
