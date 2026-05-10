<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'branches' => Branch::active()->get(['id', 'name']),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->validate(['branch_id' => 'nullable|exists:branches,id']);

        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $branchId = $request->input('branch_id');

        if ($branchId && $user->hasBranchAccess((int) $branchId)) {
            $request->session()->put('branch_id', (int) $branchId);
        } else {
            $first = $user->getActiveBranches()->first();
            if ($first) {
                $request->session()->put('branch_id', $first->id);
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
