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
                ['name' => 'Wasser', 'amount_info' => '0,5l', 'price' => 2.50],
                ['name' => 'Cola', 'amount_info' => '0,33l', 'price' => 3.00],
                ['name' => 'Bier', 'amount_info' => '0,5l', 'price' => 3.50],
                ['name' => 'Apfelschorle', 'amount_info' => '0,5l', 'price' => 3.00],
                ['name' => 'Limonade', 'amount_info' => '0,33l', 'price' => 3.00],
            ],
            'Speisen' => [
                ['name' => 'Bratwurst', 'amount_info' => 'im Brötchen', 'price' => 4.50],
                ['name' => 'Pommes', 'amount_info' => 'Portion', 'price' => 3.50],
                ['name' => 'Currywurst', 'amount_info' => 'mit Brötchen', 'price' => 5.50],
                ['name' => 'Brezel', 'amount_info' => 'groß', 'price' => 2.50],
            ],
            'Sonstiges' => [
                ['name' => 'Kaffee', 'amount_info' => 'Becher', 'price' => 2.00],
                ['name' => 'Kuchen', 'amount_info' => 'Stück', 'price' => 2.50],
                ['name' => 'Eis', 'amount_info' => 'Waffel', 'price' => 1.50],
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
