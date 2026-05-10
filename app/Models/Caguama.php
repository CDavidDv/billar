<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caguama extends Model
{
    protected $fillable = [
        'total_volume_ml',
        'remaining_volume_ml',
        'opened_at',
        'opened_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
    ];

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function pours(): HasMany
    {
        return $this->hasMany(BeerPour::class);
    }

    public function getTarrosPosiblesAttribute(): int
    {
        return 0; // requires recipe context — calculated in service
    }
}
