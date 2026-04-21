<?php

namespace Database\Seeders;

use App\Models\Category;
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

        // Demo Data (German POS)
        $categories = [
            'Getränke' => [
                ['name' => 'Bier', 'amount_info' => '0,5l', 'price' => 4.50],
                ['name' => 'Weizen', 'amount_info' => '0,5l', 'price' => 4.50],
                ['name' => 'Wein', 'amount_info' => '0,25l', 'price' => 3.50],
                ['name' => 'Aperol Spritz', 'amount_info' => '0,3l', 'price' => 6.00],
                ['name' => 'Cola/Fanta/Spezi', 'amount_info' => '0,33l', 'price' => 3.00],
                ['name' => 'Apfelschorle', 'amount_info' => '0,5l', 'price' => 2.50],
                ['name' => 'Sprudel', 'amount_info' => '0,5l', 'price' => 2.00],
            ],
            'Speisen' => [
                ['name' => 'Rote', 'amount_info' => 'im Brötchen', 'price' => 4.00],
                ['name' => 'Currywurst', 'amount_info' => 'Portion', 'price' => 4.00],
                ['name' => 'Steak', 'amount_info' => 'mit Brötchen', 'price' => 5.50],
            ],
            'Sonstiges' => [
                ['name' => 'Pfand Glas', 'amount_info' => 'Glas', 'price' => 2.00],
                ['name' => 'Pfand Flasche', 'amount_info' => 'Flasch', 'price' => 1.00],
            ],
        ];

        foreach ($categories as $categoryName => $products) {
            $category = Category::create(['name' => $categoryName]);
            foreach ($products as $productData) {
                $category->products()->create($productData);
            }
        }
    }
}
