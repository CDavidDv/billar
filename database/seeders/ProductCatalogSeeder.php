<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Cervezas', 'icon' => '🍺', 'sort_order' => 1],
            ['name' => 'Cocteles', 'icon' => '🍹', 'sort_order' => 2],
            ['name' => 'Bebidas sin alcohol', 'icon' => '🥤', 'sort_order' => 3],
            ['name' => 'Botanas', 'icon' => '🍿', 'sort_order' => 4],
            ['name' => 'Varios', 'icon' => '📦', 'sort_order' => 5],
        ];

        foreach ($categories as $catData) {
            $cat = ProductCategory::firstOrCreate(['name' => $catData['name']], $catData);

            if ($cat->name === 'Cervezas') {
                Product::firstOrCreate(['name' => 'Caguama Modelo'], [
                    'product_category_id' => $cat->id,
                    'price' => 80.00,
                    'is_beer_product' => true,
                ]);
                Product::firstOrCreate(['name' => 'Corona 355ml'], [
                    'product_category_id' => $cat->id,
                    'price' => 45.00,
                    'is_beer_product' => false,
                ]);
            }

            if ($cat->name === 'Bebidas sin alcohol') {
                Product::firstOrCreate(['name' => 'Refresco 600ml'], [
                    'product_category_id' => $cat->id,
                    'price' => 25.00,
                ]);
                Product::firstOrCreate(['name' => 'Agua natural 600ml'], [
                    'product_category_id' => $cat->id,
                    'price' => 20.00,
                ]);
            }

            if ($cat->name === 'Botanas') {
                Product::firstOrCreate(['name' => 'Cacahuates'], [
                    'product_category_id' => $cat->id,
                    'price' => 30.00,
                ]);
                Product::firstOrCreate(['name' => 'Chicharrón'], [
                    'product_category_id' => $cat->id,
                    'price' => 35.00,
                ]);
            }
        }
    }
}
