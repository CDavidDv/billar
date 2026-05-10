<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_branches')
            ->withPivot('is_admin_branch')
            ->withTimestamps();
    }

    public function getActiveBranches(): Collection
    {
        if ($this->hasRole('admin')) {
            return Branch::where('is_active', true)->orderByDesc('is_main')->get();
        }

        return $this->branches()->where('branches.is_active', true)->orderByPivot('is_admin_branch', 'desc')->orderBy('branches.is_main', 'desc')->get();
    }

    public function isAdminOfBranch(int $branchId): bool
    {
        return $this->branches()
            ->where('branches.id', $branchId)
            ->where('user_branches.is_admin_branch', true)
            ->exists();
    }

    public function hasBranchAccess(int $branchId): bool
    {
        if ($this->hasRole('admin')) {
            return Branch::where('id', $branchId)->where('is_active', true)->exists();
        }

        return $this->branches()
            ->where('branches.id', $branchId)
            ->where('branches.is_active', true)
            ->exists();
    }
}
