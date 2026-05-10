<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GameTable extends Model
{
    protected $fillable = [
        'name',
        'type',
        'billing_type',
        'hourly_rate',
        'is_active',
        'map_x',
        'map_y',
        'map_width',
        'map_height',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(TableSession::class);
    }

    public function activeSession(): HasOne
    {
        return $this->hasOne(TableSession::class)->where('status', 'active');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'billar_comun' => 'Billar',
            'billar_privado' => 'Privado',
            'futbolito' => 'Futbolito',
            'maquina' => 'Máquina',
            default => 'Otro',
        };
    }
}
