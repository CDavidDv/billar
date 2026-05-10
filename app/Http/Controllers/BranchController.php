<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function switch(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);

        $user = $request->user();

        if (! $user->hasBranchAccess($data['branch_id'])) {
            return back()->withErrors(['branch' => 'No tienes acceso a esta sucursal.']);
        }

        $request->session()->put('branch_id', $data['branch_id']);

        return redirect()->back();
    }
}
