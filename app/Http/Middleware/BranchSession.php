<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BranchSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $branchId = $request->session()->get('branch_id');

        // If no branch in session or invalid, set default
        if (! $branchId || ! $user->hasBranchAccess($branchId)) {
            $activeBranches = $user->getActiveBranches();
            if ($activeBranches->isNotEmpty()) {
                $request->session()->put('branch_id', $activeBranches->first()->id);
            }
        }

        return $next($request);
    }
}
