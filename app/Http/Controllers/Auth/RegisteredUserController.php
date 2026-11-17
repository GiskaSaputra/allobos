<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        // Hanya jalur internal satu tingkat seperti /ruang-bos, supaya tidak bisa dipakai mengalihkan ke situs lain.
        $target = $request->string('tujuan')->toString();
        $safe = preg_match('#^/[a-z0-9][a-z0-9/_-]*$#i', $target) === 1;
        if ($safe) {
            session(['url.intended' => url($target)]);
        }

        return view('auth.register', ['purpose' => $safe ? $target : '']);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'whatsapp' => ['required', 'regex:/^(\+?62|0)8[0-9]{7,12}$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'consent' => ['accepted'],
        ], [
            'whatsapp.regex' => 'Nomor WhatsApp harus diawali 08 atau 62, contoh 081234567890.',
            'consent.accepted' => 'Centang persetujuan privasi untuk membuat akun.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'whatsapp' => User::normalizeWhatsapp($data['whatsapp']),
            'password' => $data['password'],
            'active_role' => RoleName::Warga,
            'email_verified_at' => config('features.verify_email') ? null : now(),
        ]);
        $user->assignRole(RoleName::Warga->value);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('warga.onboarding.show', ['step' => 1]);
    }
}
