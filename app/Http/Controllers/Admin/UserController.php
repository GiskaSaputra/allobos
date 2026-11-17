<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\Bosses\CurationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['peran' => ['nullable', Rule::enum(RoleName::class)]]);

        return view('admin.users.index', [
            'users' => User::query()->with('roles')
                ->when($request->query('peran'), fn ($q, $r) => $q->role($r))
                ->when($request->query('q'), fn ($q, $t) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$t.'%')->orWhere('email', 'like', '%'.$t.'%')))
                ->latest()->paginate(25)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.invite');
    }

    public function store(Request $request, CurationService $curation): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'role' => ['required', Rule::in(['bos', 'pengelola'])]]);

        try {
            $curation->invite($request->user(), $data['email'], RoleName::from($data['role']));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.users.index')->with('status', 'Undangan terkirim ke '.$data['email'].'.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [Rule::enum(RoleName::class)],
            'is_active' => ['boolean'],
        ], ['roles.required' => 'Pilih minimal satu peran.']);

        if ($user->is($request->user()) && ! in_array('admin', $data['roles'], true)) {
            return back()->with('error', 'Kamu tidak bisa mencabut peran Admin dari akunmu sendiri.');
        }

        $user->syncRoles($data['roles']);
        $active = $user->active_role->value;
        $user->update([
            'is_active' => $user->is($request->user()) ? true : $request->boolean('is_active'),
            'active_role' => in_array($active, $data['roles'], true) ? $active : $data['roles'][0],
        ]);
        AuditLog::record('pengguna.ubah', 'user:'.$user->id, ['peran' => $data['roles'], 'aktif' => $user->is_active]);

        return redirect()->route('admin.users.index')->with('status', 'Data '.$user->name.' diperbarui.');
    }

    public function sendReset(User $user): RedirectResponse
    {
        Password::sendResetLink(['email' => $user->email]);
        AuditLog::record('pengguna.reset_sandi', 'user:'.$user->id);

        return back()->with('status', 'Tautan atur ulang sandi dikirim ke '.$user->email.'.');
    }
}
