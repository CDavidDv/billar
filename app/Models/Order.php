<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'game_table_id',
        'branch_id',
        'created_by',
        'closed_by',
        'status',
        'subtotal',
        'time_cost',
        'discount',
        'total',
        'payment_method',
        'notes',
        'closed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'time_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(GameTable::class, 'game_table_id');
    }

    public function session(): HasOne
    {
        return $this->hasOne(TableSession::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recalculateTotal(): void
    {
        $this->subtotal = $this->items()->sum('subtotal');
        $this->total = $this->subtotal + $this->time_cost - $this->discount;
        $this->save();
    }
}
