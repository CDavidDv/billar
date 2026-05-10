<?php

namespace App\Http\Controllers;

use App\Models\AddIn;
use App\Models\Caguama;
use App\Models\MicheladaRecipe;
use App\Services\BeerPortionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CaguamaController extends Controller
{
    public function __construct(private BeerPortionService $service) {}

    public function index(): Response
    {
        $caguamas = $this->service->getActiveCaguamas()->map(function (Caguama $c) {
            return [
                'id' => $c->id,
                'total_volume_ml' => $c->total_volume_ml,
                'remaining_volume_ml' => $c->remaining_volume_ml,
                'remaining_pct' => round($c->remaining_volume_ml / $c->total_volume_ml * 100),
                'opened_at' => $c->opened_at->toIso8601String(),
                'opened_by' => $c->openedBy?->name,
                'status' => $c->status,
                'pours_count' => $c->pours->count(),
                'hours_open' => round($c->opened_at->diffInMinutes(now()) / 60, 1),
            ];
        });

        $recipes = MicheladaRecipe::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'beer_volume_ml', 'container_volume_ml', 'other_volume_ml']);

        $addIns = AddIn::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'volume_ml']);

        return Inertia::render('Caguamas/Index', [
            'caguamas' => $caguamas,
            'recipes' => $recipes,
            'addIns' => $addIns,
        ]);
    }

    public function open(Request $request)
    {
        $request->validate(['notes' => 'nullable|string|max:300']);

        $this->service->openNewCaguama(auth()->id(), $request->notes);

        return back()->with('success', 'Caguama abierta.');
    }

    public function pour(Request $request, Caguama $caguama)
    {
        $request->validate([
            'michelada_recipe_id' => 'required|exists:michelada_recipes,id',
            'add_in_ids' => 'array',
            'add_in_ids.*' => 'exists:add_ins,id',
        ]);

        $recipe = MicheladaRecipe::findOrFail($request->michelada_recipe_id);
        $addInIds = $request->input('add_in_ids', []);

        try {
            $pour = $this->service->pour($caguama, $recipe, auth()->id(), $addInIds);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['caguama' => $e->getMessage()]);
        }

        return back()->with('success', 'Servida — '.$pour->volume_ml.' mL cerveza descontados.');
    }

    public function close(Caguama $caguama)
    {
        if ($caguama->status !== 'active') {
            return back()->withErrors(['caguama' => 'Caguama ya está vacía.']);
        }

        $this->service->closeCaguama($caguama);

        return back()->with('success', 'Caguama cerrada manualmente.');
    }
}
