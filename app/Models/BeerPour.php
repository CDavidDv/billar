<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BeerPour extends Model
{
    protected $fillable = [
        'caguama_id',
        'michelada_recipe_id',
        'volume_ml',
        'poured_by',
        'order_item_id',
        'notes',
    ];

    public function caguama(): BelongsTo
    {
        return $this->belongsTo(Caguama::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(MicheladaRecipe::class, 'michelada_recipe_id');
    }

    public function pouredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'poured_by');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function addIns(): BelongsToMany
    {
        return $this->belongsToMany(AddIn::class, 'beer_pour_add_ins')
            ->withPivot('volume_ml');
    }
}
