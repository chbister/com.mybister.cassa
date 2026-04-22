<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::updateOrCreate(
            ['name' => 'posadmin'],
            [
                'email' => 'admin@example.com',
                'password' => Hash::make('Foobar2026'),
                'email_verified_at' => now(),
            ]
        );

        $this->call(DemoDataSeeder::class);
    }
}
