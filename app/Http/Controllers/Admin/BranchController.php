<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    public function index(): Response
    {
        $branches = Branch::orderBy('is_main', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'address' => $b->address,
                'phone' => $b->phone,
                'is_active' => $b->is_active,
                'is_main' => $b->is_main,
                'tables_count' => $b->tables()->count(),
                'users' => $b->users()->select('users.id', 'users.name')->get(),
            ]);

        return Inertia::render('Admin/Branches/Index', ['branches' => $branches]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'address' => 'nullable|string|max:300',
            'phone' => 'nullable|string|max:20',
            'is_main' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($data['is_main'] ?? false) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        Branch::create($data);

        return back()->with('success', 'Sucursal creada.');
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'address' => 'nullable|string|max:300',
            'phone' => 'nullable|string|max:20',
            'is_main' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($data['is_main'] ?? false) {
            Branch::where('is_main', true)->where('id', '!=', $branch->id)->update(['is_main' => false]);
        }

        $branch->update($data);

        return back()->with('success', 'Sucursal actualizada.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->is_main) {
            return back()->withErrors(['error' => 'No puedes eliminar la sucursal principal.']);
        }

        if ($branch->tables()->count() > 0) {
            return back()->withErrors(['error' => 'Primero elimina o reassigna las mesas.']);
        }

        $branch->delete();

        return back()->with('success', 'Sucursal eliminada.');
    }

    public function assignUsers(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'is_admin_branch' => 'nullable|boolean',
        ]);

        $branch->users()->sync($data['user_ids'], [
            'is_admin_branch' => $data['is_admin_branch'] ?? false,
        ]);

        return back()->with('success', 'Usuarios asignados.');
    }
}
