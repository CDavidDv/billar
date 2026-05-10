<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Caguama;
use App\Models\GameTable;
use Illuminate\Database\Seeder;

class AssignTablesToBranchSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('is_main', true)->first();
        if (! $branch) {
            echo "No hay sucursal principal. Ejecuta BranchSeeder primero.\n";

            return;
        }

        // Assign all existing tables to main branch
        GameTable::whereNull('branch_id')->update(['branch_id' => $branch->id]);
        Caguama::whereNull('branch_id')->update(['branch_id' => $branch->id]);

        echo "Tables migrated to branch: {$branch->name}\n";
    }
}
