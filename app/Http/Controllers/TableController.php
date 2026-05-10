<?php

namespace App\Http\Controllers;

use App\Models\AddIn;
use App\Models\Caguama;
use App\Models\GameTable;
use App\Models\MicheladaRecipe;
use App\Models\Order;
use App\Models\Product;
use App\Models\TableSession;
use App\Services\BeerPortionService;
use App\Services\SessionBillingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TableController extends Controller
{
    public function index(): Response
    {
        $tables = GameTable::with(['activeSession.openedBy', 'activeSession.order'])
            ->where('is_active', true)
            ->where('branch_id', session('branch_id'))
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(function (GameTable $table) {
                $session = $table->activeSession;

                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'type' => $table->type,
                    'type_label' => $table->getTypeLabel(),
                    'billing_type' => $table->billing_type,
                    'hourly_rate' => $table->hourly_rate,
                    'is_active' => $table->is_active,
                    'session' => $session ? [
                        'id' => $session->id,
                        'opened_at' => $session->opened_at->toIso8601String(),
                        'opened_by' => $session->openedBy?->name,
                        'billing_type' => $session->billing_type,
                        'hourly_rate' => $session->hourly_rate,
                        'order_id' => $session->order_id,
                        'items_count' => $session->order?->items()->count() ?? 0,
                    ] : null,
                ];
            });

        $barOrders = Order::with(['items', 'createdBy'])
            ->whereNull('game_table_id')
            ->where('status', 'open')
            ->where('branch_id', session('branch_id'))
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'subtotal' => (float) $o->subtotal,
                'items_count' => $o->items->count(),
                'created_by' => $o->createdBy?->name,
                'created_at' => $o->created_at->toIso8601String(),
            ]);

        return Inertia::render('Tables/Index', [
            'tables' => $tables,
            'barOrders' => $barOrders,
        ]);
    }

    public function openSession(Request $request, GameTable $table)
    {
        $request->validate([
            'billing_type' => 'required|in:por_hora,precio_fijo',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($table->activeSession) {
            return back()->withErrors(['table' => 'La mesa ya tiene una sesión activa.']);
        }

        $order = Order::create([
            'game_table_id' => $table->id,
            'created_by' => auth()->id(),
            'status' => 'open',
            'branch_id' => session('branch_id'),
        ]);

        $session = TableSession::create([
            'game_table_id' => $table->id,
            'order_id' => $order->id,
            'opened_by' => auth()->id(),
            'opened_at' => now(),
            'billing_type' => $request->billing_type,
            'hourly_rate' => $table->hourly_rate,
            'status' => 'active',
            'notes' => $request->notes,
            'branch_id' => session('branch_id'),
        ]);

        return redirect()->route('tables.sessions.show', $session);
    }

    public function showSession(TableSession $session): Response
    {
        $session->load(['table', 'openedBy', 'order.items']);

        $billing = app(SessionBillingService::class)->calculateTimeCost($session);

        return Inertia::render('Tables/Session', [
            'session' => [
                'id' => $session->id,
                'opened_at' => $session->opened_at->toIso8601String(),
                'billing_type' => $session->billing_type,
                'hourly_rate' => (float) $session->hourly_rate,
                'notes' => $session->notes,
                'opened_by' => $session->openedBy?->name,
            ],
            'table' => [
                'id' => $session->table->id,
                'name' => $session->table->name,
                'type_label' => $session->table->getTypeLabel(),
            ],
            'order' => [
                'id' => $session->order->id,
                'subtotal' => (float) $session->order->subtotal,
                'discount' => (float) $session->order->discount,
                'items' => $session->order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'unit_price' => (float) $item->unit_price,
                    'quantity' => $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                    'notes' => $item->notes,
                ]),
            ],
            'billing' => $billing,
            'products' => $this->productList(),
            'caguamas' => app(BeerPortionService::class)->getActiveCaguamas()->map(fn ($c) => [
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
        ]);
    }

    private function productList(): array
    {
        return Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'is_beer_product'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'is_beer_product' => (bool) $p->is_beer_product,
            ])
            ->toArray();
    }

    public function addBeerItem(Request $request, TableSession $session)
    {
        if ($session->status !== 'active') {
            return back()->withErrors(['session' => 'La sesión no está activa.']);
        }

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

        $item = $session->order->items()->create([
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

        $session->order->recalculateTotal();

        return back();
    }

    public function addItem(Request $request, TableSession $session)
    {
        $request->validate([
            'product_name' => 'required|string|max:200',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:300',
        ]);

        if ($session->status !== 'active') {
            return back()->withErrors(['session' => 'La sesión no está activa.']);
        }

        $subtotal = round($request->unit_price * $request->quantity, 2);

        $session->order->items()->create([
            'product_name' => $request->product_name,
            'unit_price' => $request->unit_price,
            'quantity' => $request->quantity,
            'subtotal' => $subtotal,
            'notes' => $request->notes,
        ]);

        $session->order->recalculateTotal();

        return back();
    }

    public function removeItem(TableSession $session, int $itemId)
    {
        if ($session->status !== 'active') {
            return back()->withErrors(['session' => 'La sesión no está activa.']);
        }

        $item = $session->order->items()->findOrFail($itemId);
        $item->delete();

        $session->order->recalculateTotal();

        return back();
    }

    public function closeSession(Request $request, TableSession $session)
    {
        $request->validate([
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia',
        ]);

        if ($session->status !== 'active') {
            return back()->withErrors(['session' => 'La sesión no está activa.']);
        }

        app(SessionBillingService::class)->closeSession($session, $request->payment_method);

        return redirect()->route('tables.index');
    }
}
