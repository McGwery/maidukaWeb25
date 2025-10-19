<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $categories = [
            [
                'name' => '🍽️ Food & Groceries',
                'description' => 'Packaged foods, snacks, and grocery items',
            ],
            [
                'name' => '🥤 Beverages',
                'description' => 'All types of drinks including coffee, tea, juices, and soft drinks',
            ],
            [
                'name' => '🏠 Household Items',
                'description' => 'Cleaning supplies, paper products, and home essentials',
            ],
            [
                'name' => '🧴 Personal Care',
                'description' => 'Toiletries, hygiene products, and beauty items',
            ],
            [
                'name' => '📱 Electronics',
                'description' => 'Small electronics, phone accessories, and gadgets',
            ],
            [
                'name' => '👕 Clothing',
                'description' => 'Apparel, fashion items, and accessories',
            ],
            [
                'name' => '🍞 Bakery',
                'description' => 'Bread, pastries, cakes, and baked goods',
            ],
            [
                'name' => '🥛 Dairy & Refrigerated',
                'description' => 'Milk, cheese, yogurt, and other refrigerated products',
            ],
            [
                'name' => '🍎 Fruits & Vegetables',
                'description' => 'Fresh produce, fruits, and vegetables',
            ],
            [
                'name' => '🥩 Meat & Seafood',
                'description' => 'Fresh and frozen meat, poultry, and seafood',
            ],
            [
                'name' => '🧊 Frozen Foods',
                'description' => 'Ice cream, frozen meals, and frozen vegetables',
            ],
            [
                'name' => '🌾 Pantry Staples',
                'description' => 'Rice, flour, oil, spices, and cooking essentials',
            ],
            [
                'name' => '💊 Health & Wellness',
                'description' => 'Vitamins, supplements, and health products',
            ],
            [
                'name' => '📚 Stationery & Office',
                'description' => 'School supplies, office materials, and writing instruments',
            ],
            [
                'name' => '🎮 Toys & Games',
                'description' => 'Children toys, board games, and entertainment items',
            ],
        ];

        // Create categories for each shop
        foreach ($categories as $category) {
            Category::firstOrCreate([
                'name' => $category['name'],
                'description' => $category['description'],
            ]);
        }

        $this->command->info('Categories seeded successfully');
    }
}
