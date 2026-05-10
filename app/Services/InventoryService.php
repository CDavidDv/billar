<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Collection;

class InventoryService
{
    public function getOrCreate(Product $product, ?int $branchId = null): Inventory
    {
        $branchId ??= (int) session('branch_id') ?: null;

        return Inventory::firstOrCreate(
            ['product_id' => $product->id, 'branch_id' => $branchId],
            ['quantity' => 0, 'unit' => 'piezas', 'min_stock' => 0, 'branch_id' => $branchId]
        );
    }

    public function addStock(Product $product, float $quantity, string $notes, int $userId): InventoryMovement
    {
        $inventory = $this->getOrCreate($product);
        $before = (float) $inventory->quantity;
        $after = $before + $quantity;

        $inventory->quantity = $after;
        $inventory->save();

        return InventoryMovement::create([
            'product_id' => $product->id,
            'type' => 'entrada',
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'notes' => $notes,
            'user_id' => $userId,
        ]);
    }

    public function deductStock(Product $product, float $quantity, string $type, string $notes, int $userId): InventoryMovement
    {
        $inventory = $this->getOrCreate($product);
        $before = (float) $inventory->quantity;
        $after = max(0, $before - $quantity);

        $inventory->quantity = $after;
        $inventory->save();

        return InventoryMovement::create([
            'product_id' => $product->id,
            'type' => $type,
            'quantity' => -$quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'notes' => $notes,
            'user_id' => $userId,
        ]);
    }

    public function adjust(Product $product, float $newQuantity, string $notes, int $userId): InventoryMovement
    {
        $inventory = $this->getOrCreate($product);
        $before = (float) $inventory->quantity;

        $inventory->quantity = $newQuantity;
        $inventory->save();

        return InventoryMovement::create([
            'product_id' => $product->id,
            'type' => 'ajuste',
            'quantity' => $newQuantity - $before,
            'quantity_before' => $before,
            'quantity_after' => $newQuantity,
            'notes' => $notes,
            'user_id' => $userId,
        ]);
    }

    public function getLowStockProducts(): Collection
    {
        return Inventory::with('product.category')
            ->whereRaw('quantity <= min_stock')
            ->where('min_stock', '>', 0)
            ->where('branch_id', session('branch_id'))
            ->get();
    }

    public function updateConfig(Product $product, string $unit, float $minStock): Inventory
    {
        $inventory = $this->getOrCreate($product);
        $inventory->update(['unit' => $unit, 'min_stock' => $minStock]);

        return $inventory;
    }
}
