<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            BranchSeeder::class,
            GameTableSeeder::class,
            ProductCatalogSeeder::class,
        ]);
    }
}
