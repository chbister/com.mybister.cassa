<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo Data (German POS)
        $categoriesData = [
            'Getränke' => [
                ['name' => 'Bier', 'amount_info' => '0,5l', 'price' => 4.50, 'deposit' => 'Glas'],
                ['name' => 'Weizen', 'amount_info' => '0,5l', 'price' => 4.50, 'deposit' => 'Glas'],
                ['name' => 'Wein', 'amount_info' => '0,25l', 'price' => 3.50, 'deposit' => 'Glas'],
                ['name' => 'Aperol Spritz', 'amount_info' => '0,3l', 'price' => 6.00, 'deposit' => 'Glas'],
                ['name' => 'Cola/Fanta/Spezi', 'amount_info' => '0,33l', 'price' => 3.00, 'deposit' => 'Flasche'],
                ['name' => 'Apfelschorle', 'amount_info' => '0,5l', 'price' => 2.50, 'deposit' => 'Flasche'],
                ['name' => 'Sprudel', 'amount_info' => '0,5l', 'price' => 2.00, 'deposit' => 'Flasche'],
            ],
            'Speisen' => [
                ['name' => 'Rote', 'amount_info' => 'im Brötchen', 'price' => 4.00],
                ['name' => 'Currywurst', 'amount_info' => 'Portion', 'price' => 4.00],
                ['name' => 'Steak', 'amount_info' => 'mit Brötchen', 'price' => 5.50],
            ],
            'Pfand' => [
                ['name' => 'Glas', 'amount_info' => 'Glas', 'price' => 2.00],
                ['name' => 'Flasche', 'amount_info' => 'Flasche', 'price' => 1.00],
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $categoryName => $products) {
            $categories[$categoryName] = Category::create([
                'name' => $categoryName,
                'is_deposit' => $categoryName === 'Pfand',
            ]);
        }

        // First pass: create all products
        $allProducts = [];
        foreach ($categoriesData as $categoryName => $products) {
            $category = $categories[$categoryName];
            foreach ($products as $productData) {
                $depositName = $productData['deposit'] ?? null;
                unset($productData['deposit']);
                $product = $category->products()->create($productData);
                $allProducts[$product->name] = [
                    'model' => $product,
                    'deposit_name' => $depositName,
                ];
            }
        }

        // Second pass: link deposits
        foreach ($allProducts as $data) {
            if ($data['deposit_name'] && isset($allProducts[$data['deposit_name']])) {
                $data['model']->update([
                    'deposit_product_id' => $allProducts[$data['deposit_name']]['model']->id,
                ]);
            }
        }
    }
}
