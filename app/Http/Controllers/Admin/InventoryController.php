<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $service) {}

    public function index(): Response
    {
        $branchId = session('branch_id');

        $products = Product::with(['category', 'inventories' => fn ($q) => $q->where('branch_id', $branchId)])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category?->name,
                'quantity' => $p->inventories->first() ? (float) $p->inventories->first()->quantity : 0,
                'unit' => $p->inventories->first()?->unit ?? 'piezas',
                'min_stock' => $p->inventories->first() ? (float) $p->inventories->first()->min_stock : 0,
                'is_low' => $p->inventories->first()?->is_low_stock ?? false,
                'inventory_id' => $p->inventories->first()?->id,
            ]);

        return Inertia::render('Admin/Inventory/Index', [
            'products' => $products,
        ]);
    }

    public function adjust(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|in:piezas,ml,oz,g,kg,litros',
            'min_stock' => 'required|numeric|min:0',
            'type' => 'required|in:entrada,ajuste,merma',
            'notes' => 'nullable|string|max:300',
        ]);

        $this->service->updateConfig($product, $data['unit'], $data['min_stock']);

        $inventory = $this->service->getOrCreate($product);

        if ($data['type'] === 'entrada') {
            $this->service->addStock($product, $data['quantity'], $data['notes'] ?? '', auth()->id());
        } elseif ($data['type'] === 'merma') {
            $this->service->deductStock($product, $data['quantity'], 'merma', $data['notes'] ?? '', auth()->id());
        } else {
            $this->service->adjust($product, $data['quantity'], $data['notes'] ?? '', auth()->id());
        }

        return back()->with('success', 'Inventario actualizado.');
    }

    public function movements(Product $product)
    {
        $movements = InventoryMovement::where('product_id', $product->id)
            ->with('user')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'type' => $m->type,
                'quantity' => (float) $m->quantity,
                'quantity_before' => (float) $m->quantity_before,
                'quantity_after' => (float) $m->quantity_after,
                'notes' => $m->notes,
                'user' => $m->user?->name,
                'created_at' => $m->created_at->toIso8601String(),
            ]);

        return response()->json($movements);
    }
}
