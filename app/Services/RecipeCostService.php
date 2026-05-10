<?php

namespace App\Services;

use App\Models\Product;

class RecipeCostService
{
    public function getCostBreakdown(Product $product): array
    {
        if (! $product->has_recipe || ! $product->recipe) {
            return [
                'total_cost' => 0,
                'sale_price' => (float) $product->price,
                'margin' => (float) $product->price,
                'margin_pct' => 100.0,
                'ingredients' => [],
            ];
        }

        $ingredients = $product->recipe->ingredients->map(function ($ingredient) {
            $lineCost = (float) $ingredient->amount * (float) $ingredient->unit_cost;

            return [
                'name' => $ingredient->name,
                'amount' => (float) $ingredient->amount,
                'unit' => $ingredient->unit,
                'unit_cost' => (float) $ingredient->unit_cost,
                'line_cost' => round($lineCost, 4),
            ];
        })->toArray();

        $totalCost = array_sum(array_column($ingredients, 'line_cost'));
        $salePrice = (float) $product->price;
        $margin = $salePrice - $totalCost;
        $marginPct = $salePrice > 0 ? round(($margin / $salePrice) * 100, 1) : 0;

        return [
            'total_cost' => round($totalCost, 2),
            'sale_price' => $salePrice,
            'margin' => round($margin, 2),
            'margin_pct' => $marginPct,
            'ingredients' => $ingredients,
        ];
    }
}
