<?php

namespace App\Http\Controllers\Warga;

use App\Enums\BossStatus;
use App\Http\Controllers\Controller;
use App\Models\BossProfile;
use App\Models\BossSlot;
use App\Models\BusinessField;
use App\Models\ConsultNote;
use App\Models\MentoringSession;
use App\Services\Mentoring\MentoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use RuntimeException;

class MentoringController extends Controller
{
    public function __construct(private readonly MentoringService $mentoring) {}

    public function index(Request $request): View
    {
        return view('warga.mentoring.index', [
            'bosses' => BossProfile::query()->with(['user', 'field', 'district'])->where('status', BossStatus::Aktif->value)
                ->when($request->query('bidang'), fn ($q, $slug) => $q->whereHas('field', fn ($f) => $f->where('slug', $slug)))
                ->withCount(['slots as open_slots' => fn ($q) => $q->bookable()])
                ->orderByDesc('rating_avg')->get(),
            'fields' => BusinessField::query()->ordered()->whereHas('bossProfiles', fn ($q) => $q->where('status', BossStatus::Aktif->value))->get(['name', 'slug']),
            'sessions' => MentoringSession::query()->with(['slot', 'boss.user'])->where('user_id', $request->user()->id)->latest()->limit(10)->get(),
            'noteId' => $request->integer('catatan') ?: null,
        ]);
    }

    public function boss(Request $request, BossProfile $boss): View
    {
        abort_unless($boss->status === BossStatus::Aktif, 404);

        return view('warga.mentoring.boss', [
            'boss' => $boss->load(['user', 'field', 'district']),
            'slots' => $boss->slots()->where('starts_at', '>=', now())->orderBy('starts_at')->limit(30)->get(),
            'bookable' => $boss->slots()->bookable()->pluck('id')->all(),
            'notes' => ConsultNote::query()->with('session')->where('user_id', $request->user()->id)->latest()->limit(10)->get(),
            'noteId' => $request->integer('catatan') ?: null,
            'ratings' => $boss->sessions()->with('user')->whereNotNull('rating')->latest()->limit(5)->get(),
        ]);
    }

    public function book(Request $request, BossSlot $slot): RedirectResponse
    {
        $data = $request->validate([
            'topic' => ['required', 'string', 'min:10', 'max:1000'],
            'consult_note_id' => ['nullable', 'integer'],
        ], ['topic.min' => 'Ceritakan topikmu minimal 10 karakter supaya Bos bisa bersiap.']);

        try {
            $session = $this->mentoring->book($request->user(), $slot, $data['topic'], $data['consult_note_id'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('warga.mentoring.show', $session)->with('status', 'Permintaan sesi terkirim. Bos akan menjawab paling lambat 48 jam.');
    }

    public function show(MentoringSession $session): View
    {
        Gate::authorize('viewAsWarga', $session);

        return view('warga.mentoring.show', ['session' => $session->load(['slot', 'boss.user', 'note'])]);
    }

    public function cancel(Request $request, MentoringSession $session): RedirectResponse
    {
        Gate::authorize('viewAsWarga', $session);

        return $this->act(fn () => $this->mentoring->cancel($session, $request->user(), $request->input('reason')), 'Sesi dibatalkan dan Bos sudah diberi tahu.');
    }

    public function rate(Request $request, MentoringSession $session): RedirectResponse
    {
        Gate::authorize('viewAsWarga', $session);
        $data = $request->validate(['rating' => ['required', 'integer', 'between:1,5'], 'comment' => ['nullable', 'string', 'max:500']]);

        return $this->act(fn () => $this->mentoring->rate($session, (int) $data['rating'], $data['comment'] ?? null), 'Terima kasih, penilaianmu tersimpan.');
    }

    public function steps(MentoringSession $session): RedirectResponse
    {
        Gate::authorize('viewAsWarga', $session);
        $count = $this->mentoring->addSteps($session);

        return back()->with('status', $count.' langkah dari Bos ditambahkan ke Langkah 30 Hari.');
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
