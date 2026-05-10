<?php

namespace App\Http\Controllers;

use App\Models\AddIn;
use App\Models\Caguama;
use App\Models\MicheladaRecipe;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\BeerPortionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BarOrderController extends Controller
{
    public function create()
    {
        $order = Order::create([
            'game_table_id' => null,
            'created_by' => auth()->id(),
            'status' => 'open',
            'branch_id' => session('branch_id'),
        ]);

        return redirect()->route('bar-orders.show', $order);
    }

    public function show(Order $order): Response
    {
        abort_if($order->game_table_id !== null, 404);

        $order->load('items', 'createdBy');

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'is_beer_product'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'is_beer_product' => (bool) $p->is_beer_product,
            ])
            ->toArray();

        $service = app(BeerPortionService::class);

        return Inertia::render('BarOrders/Show', [
            'products' => $products,
            'caguamas' => $service->getActiveCaguamas()->map(fn ($c) => [
                'id' => $c->id,
                'remaining_volume_ml' => $c->remaining_volume_ml,
                'label' => 'Caguama #'.$c->id.' ('.$c->remaining_volume_ml.' mL)',
            ]),
            'recipes' => MicheladaRecipe::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'container_volume_ml']),
            'addIns' => AddIn::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'volume_ml']),
            'order' => [
                'id' => $order->id,
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) $order->discount,
                'created_by' => $order->createdBy?->name,
                'created_at' => $order->created_at->toIso8601String(),
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'unit_price' => (float) $item->unit_price,
                    'quantity' => $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                    'notes' => $item->notes,
                ]),
            ],
        ]);
    }

    public function addBeerItem(Request $request, Order $order)
    {
        abort_if($order->game_table_id !== null, 404);
        abort_if($order->status !== 'open', 403);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:300',
            'caguama_id' => 'required|exists:caguamas,id',
            'michelada_recipe_id' => 'required|exists:michelada_recipes,id',
            'add_in_ids' => 'array',
            'add_in_ids.*' => 'exists:add_ins,id',
        ]);

        $product = Product::findOrFail($request->product_id);
        $caguama = Caguama::findOrFail($request->caguama_id);
        $recipe = MicheladaRecipe::findOrFail($request->michelada_recipe_id);
        $addInIds = $request->input('add_in_ids', []);

        $service = app(BeerPortionService::class);
        $beerVolume = $service->calcBeerVolume($recipe, $addInIds);

        if (! $service->canPour($caguama, $beerVolume)) {
            return back()->withErrors(['caguama' => 'Remanente insuficiente en la caguama seleccionada.']);
        }

        $item = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => $request->quantity,
            'subtotal' => round($product->price * $request->quantity, 2),
            'notes' => $request->notes,
        ]);

        for ($i = 0; $i < $request->quantity; $i++) {
            $service->pour($caguama, $recipe, auth()->id(), $addInIds, $item->id);
            $caguama->refresh();
        }

        $order->recalculateTotal();

        return back();
    }

    public function addItem(Request $request, Order $order)
    {
        abort_if($order->game_table_id !== null, 404);
        abort_if($order->status !== 'open', 403);

        $request->validate([
            'product_name' => 'required|string|max:200',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:300',
        ]);

        $order->items()->create([
            'product_name' => $request->product_name,
            'unit_price' => $request->unit_price,
            'quantity' => $request->quantity,
            'subtotal' => round($request->unit_price * $request->quantity, 2),
            'notes' => $request->notes,
        ]);

        $order->recalculateTotal();

        return back();
    }

    public function removeItem(Order $order, int $itemId)
    {
        abort_if($order->game_table_id !== null, 404);
        abort_if($order->status !== 'open', 403);

        $order->items()->findOrFail($itemId)->delete();
        $order->recalculateTotal();

        return back();
    }

    public function cancel(Order $order)
    {
        abort_if($order->game_table_id !== null, 404);
        abort_if($order->status !== 'open', 403);

        $order->update([
            'status' => 'cancelled',
            'closed_by' => auth()->id(),
            'closed_at' => now(),
        ]);

        return redirect()->route('tables.index');
    }

    public function close(Request $request, Order $order)
    {
        abort_if($order->game_table_id !== null, 404);
        abort_if($order->status !== 'open', 403);

        $request->validate([
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia',
        ]);

        $order->update([
            'status' => 'closed',
            'payment_method' => $request->payment_method,
            'time_cost' => 0,
            'total' => $order->subtotal - $order->discount,
            'closed_by' => auth()->id(),
            'closed_at' => now(),
        ]);

        return redirect()->route('tables.index');
    }
}
