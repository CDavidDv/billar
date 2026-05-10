<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddIn;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddInController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/AddIns/Index', [
            'addIns' => AddIn::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:add_ins,name',
            'volume_ml' => 'required|integer|min:0|max:999',
            'sort_order' => 'integer|min:0',
        ]);

        AddIn::create($data);

        return back();
    }

    public function update(Request $request, AddIn $addIn)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:add_ins,name,'.$addIn->id,
            'volume_ml' => 'required|integer|min:0|max:999',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $addIn->update($data);

        return back();
    }

    public function destroy(AddIn $addIn)
    {
        if ($addIn->pours()->exists()) {
            $addIn->update(['is_active' => false]);
        } else {
            $addIn->delete();
        }

        return back();
    }
}
