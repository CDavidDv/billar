<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'is_active',
        'is_main',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_main' => 'boolean',
    ];

    public function tables()
    {
        return $this->hasMany(GameTable::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function sessions()
    {
        return $this->hasMany(TableSession::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function caguamas()
    {
        return $this->hasMany(Caguama::class);
    }

    public function configurations()
    {
        return $this->hasMany(AppConfiguration::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_branches')
            ->withPivot('is_admin_branch')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    public static function getMainBranch(): ?self
    {
        return static::where('is_main', true)->first();
    }

    public static function getActiveBranches(): Collection
    {
        return static::where('is_active', true)->get();
    }
}
