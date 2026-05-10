<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MicheladaRecipe extends Model
{
    protected $fillable = [
        'name',
        'beer_volume_ml',
        'container_volume_ml',
        'other_volume_ml',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'container_volume_ml' => 'integer',
    ];

    public function pours(): HasMany
    {
        return $this->hasMany(BeerPour::class);
    }
}
