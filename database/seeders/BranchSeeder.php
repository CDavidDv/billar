<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        // Create main branch
        $branch = Branch::create([
            'name' => 'Billar Principal',
            'address' => 'Av. Principal 123, Centro',
            'phone' => '55-1234-5678',
            'is_active' => true,
            'is_main' => true,
        ]);

        // Assign admin user to branch
        $admin = User::where('email', 'admin@billar.local')->first();
        if ($admin) {
            $admin->branches()->attach($branch->id, ['is_admin_branch' => true]);
        }

        echo "Sucursal principal creada: {$branch->name}\n";
    }
}
