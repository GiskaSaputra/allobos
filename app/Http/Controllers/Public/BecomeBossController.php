<?php

namespace App\Http\Controllers\Public;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\BusinessField;
use App\Models\District;
use App\Models\User;
use App\Services\Bosses\CurationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class BecomeBossController extends Controller
{
    public function create(): View
    {
        return view('public.become-boss', [
            'fields' => BusinessField::query()->ordered()->pluck('name', 'id'),
            'districts' => District::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request, CurationService $curation): RedirectResponse
    {
        $guest = ! $request->user();
        $data = $request->validate(array_merge($guest ? [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['required', 'regex:/^(\+?62|0)8[0-9]{7,12}$/'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'consent' => ['accepted'],
        ] : [], [
            'business_name' => ['required', 'string', 'max:160'],
            'business_desc' => ['required', 'string', 'min:20', 'max:2000'],
            'business_field_id' => ['required', 'exists:business_fields,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'years_running' => ['required', 'integer', 'min:0', 'max:80'],
            'track_record' => ['nullable', 'string', 'max:2000'],
            'trace_url' => ['nullable', 'url:http,https', 'max:255'],
        ]), ['whatsapp.regex' => 'Nomor WhatsApp harus diawali 08 atau 62, contoh 081234567890.']);

        $user = $request->user() ?? User::query()->create([
            'name' => $data['name'], 'email' => $data['email'], 'whatsapp' => User::normalizeWhatsapp($data['whatsapp']),
            'password' => $data['password'], 'active_role' => RoleName::Bos,
            'email_verified_at' => config('features.verify_email') ? null : now(),
        ]);

        $curation->apply($user, collect($data)->only(['business_name', 'business_desc', 'business_field_id', 'district_id', 'years_running', 'track_record', 'trace_url'])->all());
        $user->update(['active_role' => RoleName::Bos]);

        if ($guest) {
            Auth::login($user);
        }

        return redirect()->route('bos.dashboard')->with('status', 'Profil Bos diajukan. Pengelola IPI akan memeriksa dengan empat standar kurasi.');
    }
}
