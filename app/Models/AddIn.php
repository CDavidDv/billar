<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AddIn extends Model
{
    protected $fillable = [
        'name',
        'volume_ml',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'volume_ml' => 'integer',
        'sort_order' => 'integer',
    ];

    public function pours(): BelongsToMany
    {
        return $this->belongsToMany(BeerPour::class, 'beer_pour_add_ins')
            ->withPivot('volume_ml');
    }
}
