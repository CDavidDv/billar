<?php

namespace Database\Seeders;

use App\Models\AddIn;
use Illuminate\Database\Seeder;

class AddInsSeeder extends Seeder
{
    public function run(): void
    {
        $addIns = [
            ['name' => 'Clamato',       'volume_ml' => 150, 'sort_order' => 1],
            ['name' => 'Salsa Valentina','volume_ml' => 20,  'sort_order' => 2],
            ['name' => 'Salsa Maggi',   'volume_ml' => 10,  'sort_order' => 3],
            ['name' => 'Limón (jugo)',  'volume_ml' => 30,  'sort_order' => 4],
            ['name' => 'Sal',           'volume_ml' => 0,   'sort_order' => 5],
            ['name' => 'Hielo',         'volume_ml' => 50,  'sort_order' => 6],
        ];

        foreach ($addIns as $addIn) {
            AddIn::firstOrCreate(['name' => $addIn['name']], $addIn);
        }
    }
}
