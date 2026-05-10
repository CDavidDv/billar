<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FloorPlanConfig extends Model
{
    protected $table = 'floor_plan_config';

    protected $fillable = [
        'branch_id',
        'background_image_path',
        'canvas_width',
        'canvas_height',
        'updated_by',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(int $branchId): static
    {
        return static::firstOrCreate(
            ['branch_id' => $branchId],
            ['canvas_width' => 1200, 'canvas_height' => 700]
        );
    }
}
