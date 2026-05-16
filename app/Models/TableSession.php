<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableSession extends Model
{
    protected $fillable = [
        'game_table_id',
        'order_id',
        'opened_by',
        'closed_by',
        'opened_at',
        'closed_at',
        'billing_type',
        'hourly_rate',
        'time_minutes',
        'time_cost',
        'status',
        'notes',
        'paused_at',
        'paused_minutes',
        'branch_id',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'paused_at' => 'datetime',
        'hourly_rate' => 'decimal:2',
        'time_cost' => 'decimal:2',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(GameTable::class, 'game_table_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
