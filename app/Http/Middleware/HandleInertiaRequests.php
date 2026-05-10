<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $branches = [];
        $currentBranch = null;

        if ($user) {
            $branches = $user->getActiveBranches()
                ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name])
                ->toArray();

            $currentBranchId = $request->session()->get('branch_id');
            if ($currentBranchId && $user->hasBranchAccess($currentBranchId)) {
                $currentBranch = ['id' => $currentBranchId, 'name' => Branch::find($currentBranchId)?->name];
            } elseif (! empty($branches)) {
                $currentBranch = $branches[0];
                // Auto-init session so controllers always get a valid branch_id
                $request->session()->put('branch_id', $branches[0]['id']);
            }
        }

        $lowStockCount = 0;
        if ($user && $currentBranch) {
            $lowStockCount = Inventory::whereRaw('quantity <= min_stock')
                ->where('min_stock', '>', 0)
                ->where('branch_id', $currentBranch['id'])
                ->count();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? array_merge(
                    $user->only(['id', 'name', 'email']),
                    ['roles' => $user->getRoleNames()]
                ) : null,
                'branches' => $branches,
                'currentBranch' => $currentBranch,
                'lowStockCount' => $lowStockCount,
            ],
        ];
    }
}
