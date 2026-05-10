<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameTable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GameTableController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Tables/Index', [
            'tables' => GameTable::where('branch_id', session('branch_id'))
                ->orderBy('type')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:billar_comun,billar_privado,futbolito,maquina,otro',
            'billing_type' => 'required|in:por_hora,precio_fijo',
            'hourly_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $branchId = session('branch_id');
        abort_if(! $branchId, 400, 'No hay sucursal activa en sesión.');

        $data['branch_id'] = $branchId;
        GameTable::create($data);

        return back();
    }

    public function update(Request $request, GameTable $table)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:billar_comun,billar_privado,futbolito,maquina,otro',
            'billing_type' => 'required|in:por_hora,precio_fijo',
            'hourly_rate' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $table->update($data);

        return back();
    }

    public function destroy(GameTable $table)
    {
        if ($table->activeSession) {
            return back()->withErrors(['table' => 'No se puede eliminar una mesa con sesión activa.']);
        }

        $table->delete();

        return back();
    }
}
