<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    protected $fillable = [
        'product_recipe_id',
        'name',
        'amount',
        'unit',
        'unit_cost',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'unit_cost' => 'decimal:4',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(ProductRecipe::class, 'product_recipe_id');
    }

    public function totalCost(): float
    {
        return (float) $this->amount * (float) $this->unit_cost;
    }
}
