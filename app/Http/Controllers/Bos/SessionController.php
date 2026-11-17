<?php

namespace App\Http\Controllers\Bos;

use App\Enums\MentoringStatus;
use App\Http\Controllers\Controller;
use App\Models\MentoringSession;
use App\Services\Mentoring\MentoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class SessionController extends Controller
{
    public function __construct(private readonly MentoringService $mentoring) {}

    public function index(Request $request): View
    {
        $request->validate(['status' => ['nullable', Rule::enum(MentoringStatus::class)]]);
        $boss = $request->user()->bossProfile;

        return view('bos.sessions.index', [
            'sessions' => $boss ? MentoringSession::query()->with(['slot', 'user'])->where('boss_profile_id', $boss->id)
                ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest()->paginate(20)->withQueryString() : collect(),
            'status' => $request->query('status'),
        ]);
    }

    public function show(MentoringSession $session): View
    {
        Gate::authorize('viewAsBoss', $session);

        return view('bos.sessions.show', ['session' => $session->load(['slot', 'user.profile.district', 'note'])]);
    }

    public function approve(Request $request, MentoringSession $session): RedirectResponse
    {
        Gate::authorize('viewAsBoss', $session);
        $data = $request->validate(['meeting_link' => ['nullable', 'url', 'max:255'], 'address' => ['nullable', 'string', 'max:255']]);

        return $this->act(fn () => $this->mentoring->approve($session, $data['meeting_link'] ?? null, $data['address'] ?? null), 'Sesi disetujui. Warga sudah diberi tahu.');
    }

    public function reject(Request $request, MentoringSession $session): RedirectResponse
    {
        Gate::authorize('viewAsBoss', $session);
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:300']])['reason'];

        return $this->act(fn () => $this->mentoring->reject($session, $reason), 'Permintaan ditolak dan slot terbuka lagi.');
    }

    public function complete(Request $request, MentoringSession $session): RedirectResponse
    {
        Gate::authorize('viewAsBoss', $session);
        $data = $request->validate(['notes' => ['required', 'string', 'min:10', 'max:3000'], 'steps' => ['nullable', 'string', 'max:2000']], ['notes.required' => 'Tulis catatan sesi sebelum menandai selesai.']);

        return $this->act(fn () => $this->mentoring->complete($session, $data['notes'], preg_split('/\R/', (string) ($data['steps'] ?? ''))), 'Sesi ditandai selesai. Catatanmu tampil di riwayat warga.');
    }

    public function noShow(MentoringSession $session): RedirectResponse
    {
        Gate::authorize('viewAsBoss', $session);

        return $this->act(fn () => $this->mentoring->noShow($session), 'Warga ditandai tidak hadir.');
    }

    public function cancel(Request $request, MentoringSession $session): RedirectResponse
    {
        Gate::authorize('viewAsBoss', $session);
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:300']])['reason'];

        return $this->act(fn () => $this->mentoring->cancel($session, $request->user(), $reason), 'Sesi dibatalkan dan warga sudah diberi tahu.');
    }

    private function act(callable $action, string $message): RedirectResponse
    {
        try {
            $action();
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $message);
    }
}
