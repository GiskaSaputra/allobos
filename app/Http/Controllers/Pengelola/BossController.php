<?php

namespace App\Http\Controllers\Pengelola;

use App\Enums\BossStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\BossProfile;
use App\Services\Bosses\CurationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class BossController extends Controller
{
    public function __construct(private readonly CurationService $curation) {}

    public function index(Request $request): View
    {
        $request->validate(['status' => ['nullable', Rule::enum(BossStatus::class)]]);
        $status = $request->query('status', BossStatus::Diajukan->value);

        return view('pengelola.bosses.index', [
            'status' => $status,
            'bosses' => BossProfile::query()->with(['user', 'field', 'district'])->where('status', $status)->latest('updated_at')->paginate(20)->withQueryString(),
            'counts' => BossProfile::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255'], 'role' => ['nullable', Rule::in(['bos', 'pengelola'])]]);

        try {
            $this->curation->invite($request->user(), $data['email'], RoleName::from($data['role'] ?? 'bos'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Undangan terkirim ke '.$data['email'].'. Tautan berlaku 7 hari.');
    }

    public function activate(Request $request, BossProfile $boss): RedirectResponse
    {
        try {
            $this->curation->activate($boss, (array) $request->input('checklist', []));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $boss->user->name.' sekarang Bos aktif.');
    }

    public function reject(Request $request, BossProfile $boss): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:300']])['reason'];
        $this->curation->reject($boss, $reason);

        return back()->with('status', 'Profil '.$boss->user->name.' ditolak dengan alasan.');
    }
}
