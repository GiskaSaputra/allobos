<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use App\Services\Bosses\CurationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use RuntimeException;

class InvitationAcceptController extends Controller
{
    public function show(Invitation $invitation): View
    {
        return view('auth.invitation', ['invitation' => $invitation, 'hasAccount' => User::query()->where('email', $invitation->email)->exists()]);
    }

    public function store(Request $request, Invitation $invitation, CurationService $curation): RedirectResponse
    {
        $exists = User::query()->where('email', $invitation->email)->exists();
        $data = $exists ? [] : $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'whatsapp' => ['required', 'regex:/^(\+?62|0)8[0-9]{7,12}$/'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($exists && $request->user()?->email !== $invitation->email) {
            return redirect()->route('login')->with('status', 'Masuk dulu dengan akun '.$invitation->email.', lalu buka lagi tautan undangan.');
        }

        try {
            $user = $curation->accept($invitation, $data);
        } catch (RuntimeException $e) {
            return redirect()->route('home')->with('error', $e->getMessage());
        }

        Auth::login($user);

        return redirect()->route($invitation->role->value === 'bos' ? 'bos.profile.edit' : 'dashboard')->with('status', 'Selamat bergabung sebagai '.$invitation->role->label().'.');
    }
}
