<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActiveRoleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', Rule::enum(RoleName::class)]]);
        $role = RoleName::from($data['role']);

        abort_unless($request->user()->hasRole($role->value), 403);

        $request->user()->update(['active_role' => $role]);

        return redirect()->route($role->homeRoute())->with('status', 'Peran aktif sekarang '.$role->label().'.');
    }
}
