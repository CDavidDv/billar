<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuOrderRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class MenuOrderController extends Controller
{
    public function create(): Response
    {
        $products = Product::where('is_active', true)
            ->where('is_available', true)
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category?->name,
                'price' => (float) $p->price,
            ]);

        return Inertia::render('Menu/Order', ['products' => $products]);
    }

    public function store(MenuOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        $product = Product::findOrFail($data['product_id']);
        $quantity = $data['quantity'] ?? 1;
        $notes = $data['notes'] ?? null;

        return response()->json([
            'success' => true,
            'message' => "Pedido de {$quantity}x {$product->name} recibido",
            'product' => [
                'name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => (float) $product->price,
                'total' => (float) $product->price * $quantity,
            ],
        ]);
    }
}
