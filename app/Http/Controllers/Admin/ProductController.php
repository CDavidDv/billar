<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductModifier;
use App\Models\ProductModifierOption;
use App\Models\ProductRecipe;
use App\Models\RecipeIngredient;
use App\Services\RecipeCostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(private RecipeCostService $recipeCostService) {}

    public function index(): Response
    {
        $categories = ProductCategory::withCount('products')
            ->orderBy('sort_order')
            ->get();

        $products = Product::with(['category', 'recipe.ingredients', 'modifiers.options'])
            ->orderBy('product_category_id')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => $this->formatProduct($p));

        return Inertia::render('Admin/Products/Index', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_beer_product' => 'boolean',
            'has_recipe' => 'boolean',
        ]);

        Product::create($data);

        return back()->with('success', 'Producto creado.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'product_category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_beer_product' => 'boolean',
            'has_recipe' => 'boolean',
        ]);

        $product->update($data);

        return back()->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return back()->with('success', 'Producto eliminado.');
    }

    public function showRecipe(Product $product): Response
    {
        $product->load('recipe.ingredients', 'category');
        $breakdown = $this->recipeCostService->getCostBreakdown($product);

        return Inertia::render('Admin/Products/RecipeBuilder', [
            'product' => $this->formatProduct($product),
            'breakdown' => $breakdown,
        ]);
    }

    public function saveRecipe(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'notes' => 'nullable|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.name' => 'required|string|max:100',
            'ingredients.*.amount' => 'required|numeric|min:0.001',
            'ingredients.*.unit' => 'required|in:ml,oz,g,pcs',
            'ingredients.*.unit_cost' => 'required|numeric|min:0',
            'ingredients.*.sort_order' => 'integer|min:0',
        ]);

        $recipe = $product->recipe ?? ProductRecipe::create([
            'product_id' => $product->id,
        ]);

        $recipe->update(['notes' => $data['notes'] ?? null]);

        $recipe->ingredients()->delete();

        foreach ($data['ingredients'] as $i => $ing) {
            RecipeIngredient::create([
                'product_recipe_id' => $recipe->id,
                'name' => $ing['name'],
                'amount' => $ing['amount'],
                'unit' => $ing['unit'],
                'unit_cost' => $ing['unit_cost'],
                'sort_order' => $ing['sort_order'] ?? $i,
            ]);
        }

        $product->update(['has_recipe' => true]);

        return back()->with('success', 'Receta guardada.');
    }

    public function saveModifiers(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'modifiers' => 'required|array',
            'modifiers.*.name' => 'required|string|max:100',
            'modifiers.*.type' => 'required|in:single,multiple',
            'modifiers.*.is_required' => 'boolean',
            'modifiers.*.sort_order' => 'integer|min:0',
            'modifiers.*.options' => 'required|array|min:1',
            'modifiers.*.options.*.name' => 'required|string|max:100',
            'modifiers.*.options.*.extra_cost' => 'numeric|min:0',
            'modifiers.*.options.*.sort_order' => 'integer|min:0',
        ]);

        $product->modifiers()->delete();

        foreach ($data['modifiers'] as $i => $mod) {
            $modifier = ProductModifier::create([
                'product_id' => $product->id,
                'name' => $mod['name'],
                'type' => $mod['type'],
                'is_required' => $mod['is_required'] ?? false,
                'sort_order' => $mod['sort_order'] ?? $i,
            ]);

            foreach ($mod['options'] as $j => $opt) {
                ProductModifierOption::create([
                    'product_modifier_id' => $modifier->id,
                    'name' => $opt['name'],
                    'extra_cost' => $opt['extra_cost'] ?? 0,
                    'sort_order' => $opt['sort_order'] ?? $j,
                ]);
            }
        }

        return back()->with('success', 'Modificadores guardados.');
    }

    private function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'is_active' => $product->is_active,
            'is_beer_product' => $product->is_beer_product,
            'has_recipe' => $product->has_recipe,
            'product_category_id' => $product->product_category_id,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'icon' => $product->category->icon,
            ] : null,
            'modifiers' => $product->relationLoaded('modifiers')
                ? $product->modifiers->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'type' => $m->type,
                    'is_required' => $m->is_required,
                    'sort_order' => $m->sort_order,
                    'options' => $m->options->map(fn ($o) => [
                        'id' => $o->id,
                        'name' => $o->name,
                        'extra_cost' => (float) $o->extra_cost,
                        'sort_order' => $o->sort_order,
                    ])->toArray(),
                ])->toArray()
                : [],
            'recipe' => $product->relationLoaded('recipe') && $product->recipe
                ? [
                    'id' => $product->recipe->id,
                    'notes' => $product->recipe->notes,
                    'ingredients' => $product->recipe->ingredients->map(fn ($ing) => [
                        'id' => $ing->id,
                        'name' => $ing->name,
                        'amount' => (float) $ing->amount,
                        'unit' => $ing->unit,
                        'unit_cost' => (float) $ing->unit_cost,
                        'sort_order' => $ing->sort_order,
                    ])->toArray(),
                ]
                : null,
        ];
    }
}
