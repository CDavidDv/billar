<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\GameTable;
use Illuminate\Database\Seeder;

class GameTableSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('is_main', true)->first();

        $tables = [
            ['name' => 'Mesa 1', 'type' => 'billar_comun', 'billing_type' => 'por_hora', 'hourly_rate' => 50.00],
            ['name' => 'Mesa 2', 'type' => 'billar_comun', 'billing_type' => 'por_hora', 'hourly_rate' => 50.00],
            ['name' => 'Mesa 3', 'type' => 'billar_comun', 'billing_type' => 'por_hora', 'hourly_rate' => 50.00],
            ['name' => 'Mesa 4', 'type' => 'billar_comun', 'billing_type' => 'por_hora', 'hourly_rate' => 50.00],
            ['name' => 'Mesa 5', 'type' => 'billar_comun', 'billing_type' => 'por_hora', 'hourly_rate' => 50.00],
            ['name' => 'Mesa 6', 'type' => 'billar_comun', 'billing_type' => 'por_hora', 'hourly_rate' => 50.00],
            ['name' => 'Cuarto Privado', 'type' => 'billar_privado', 'billing_type' => 'por_hora', 'hourly_rate' => 100.00],
            ['name' => 'Futbolito', 'type' => 'futbolito', 'billing_type' => 'precio_fijo', 'hourly_rate' => 20.00],
            ['name' => 'Máquina 1', 'type' => 'maquina', 'billing_type' => 'precio_fijo', 'hourly_rate' => 10.00],
            ['name' => 'Máquina 2', 'type' => 'maquina', 'billing_type' => 'precio_fijo', 'hourly_rate' => 10.00],
        ];

        foreach ($tables as $table) {
            GameTable::firstOrCreate(
                ['name' => $table['name']],
                array_merge($table, ['branch_id' => $branch?->id])
            );
        }
    }
}
