<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductModifierOption extends Model
{
    protected $fillable = [
        'product_modifier_id',
        'name',
        'extra_cost',
        'sort_order',
    ];

    protected $casts = [
        'extra_cost' => 'decimal:2',
    ];

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(ProductModifier::class, 'product_modifier_id');
    }
}
