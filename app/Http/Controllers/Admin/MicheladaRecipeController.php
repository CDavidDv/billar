<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MicheladaRecipe;
use Illuminate\Http\Request;

class MicheladaRecipeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150|unique:michelada_recipes,name',
            'beer_volume_ml' => 'required|integer|min:100|max:1200',
            'other_volume_ml' => 'required|integer|min:0|max:500',
            'container_volume_ml' => 'nullable|integer|min:100|max:1200',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        if (empty($data['container_volume_ml'])) {
            $data['container_volume_ml'] = $data['beer_volume_ml'] + ($data['other_volume_ml'] ?? 0);
        }

        MicheladaRecipe::create($data);

        return back()->with('success', 'Receta creada.');
    }

    public function quickStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150|unique:michelada_recipes,name',
            'container_volume_ml' => 'required|integer|min:50|max:1200',
        ]);

        $recipe = MicheladaRecipe::create([
            'name' => $data['name'],
            'container_volume_ml' => $data['container_volume_ml'],
            'beer_volume_ml' => $data['container_volume_ml'],
            'other_volume_ml' => 0,
            'is_active' => true,
        ]);

        return back()->with('new_recipe_id', $recipe->id);
    }

    public function update(Request $request, MicheladaRecipe $recipe)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150|unique:michelada_recipes,name,'.$recipe->id,
            'beer_volume_ml' => 'required|integer|min:100|max:1200',
            'other_volume_ml' => 'required|integer|min:0|max:500',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $recipe->update($data);

        return back()->with('success', 'Receta actualizada.');
    }

    public function destroy(MicheladaRecipe $recipe)
    {
        if ($recipe->pours()->exists()) {
            return back()->withErrors(['recipe' => 'No se puede eliminar una receta con servicios registrados.']);
        }

        $recipe->delete();

        return back()->with('success', 'Receta eliminada.');
    }
}
