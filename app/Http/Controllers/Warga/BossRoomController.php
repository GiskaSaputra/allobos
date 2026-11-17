<?php

namespace App\Http\Controllers\Warga;

use App\Enums\SessionStatus;
use App\Enums\StepOrigin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Warga\BossSessionRequest;
use App\Jobs\SummarizeBossSession;
use App\Models\BossSession;
use App\Services\Ai\AiException;
use App\Services\Boss\BossEngine;
use App\Services\Boss\Modules;
use App\Services\Plans\StepService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class BossRoomController extends Controller
{
    public function __construct(private readonly BossEngine $engine) {}

    public function setup(Request $request): View
    {
        $user = $request->user();

        return view('boss-room.setup', [
            'topics' => BossSession::TOPICS,
            'sessionsLeft' => $this->engine->sessionsLeft($user),
            'turnsLeft' => $this->engine->turnsLeft($user),
            'resetsAt' => $this->engine->resetsAt(),
            'recent' => BossSession::query()->where('user_id', $user->id)->with('note')->latest()->limit(3)->get(),
        ]);
    }

    public function store(BossSessionRequest $request): RedirectResponse
    {
        if ($this->engine->sessionsLeft($request->user()) === 0) {
            return back()->with('error', 'Jatah sesi Ruang Bos hari ini sudah habis. Bisa dipakai lagi besok pukul 00.00 WIB.');
        }

        $session = $this->engine->create($request->user(), $request->topic(), $request->string('mode'));

        return redirect()->route('boss-room.show', $session);
    }

    public function show(BossSession $session): View|RedirectResponse
    {
        Gate::authorize('view', $session);

        if (in_array($session->status, [SessionStatus::Dirangkum, SessionStatus::Selesai], true)) {
            return redirect()->route('boss-room.note', $session);
        }

        return view('boss-room.show', [
            'session' => $session,
            'turns' => $session->turns()->get()->map(fn ($t) => [
                'role' => $t->role,
                'text' => $t->content,
                'follow_up' => $t->meta['follow_up'] ?? null,
                'modules' => Modules::links($t->meta['modules'] ?? []),
            ]),
            'turnsLeft' => $this->engine->turnsLeft($session->user),
        ]);
    }

    public function start(BossSession $session): JsonResponse
    {
        Gate::authorize('view', $session);

        try {
            $greeting = $this->engine->start($session);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_TOO_MANY_REQUESTS);
        }

        return response()->json(['greeting' => $greeting, 'status' => $session->fresh()->status->value]);
    }

    public function send(Request $request, BossSession $session): JsonResponse
    {
        Gate::authorize('view', $session);
        $data = $request->validate(['message' => ['required', 'string', 'max:1000']], ['message.required' => 'Tulis atau ucapkan pertanyaanmu dulu.']);

        if ($session->status !== SessionStatus::Berjalan) {
            return response()->json(['message' => 'Sesi ini sudah selesai. Mulai sesi baru untuk bertanya lagi.'], Response::HTTP_CONFLICT);
        }

        try {
            $result = $this->engine->reply($session, trim($data['message']));
        } catch (AiException $e) {
            return response()->json([
                'message' => $e->isQuota() ? $e->getMessage() : 'Bos Allo belum bisa menjawab sekarang. Coba kirim lagi, atau ajukan pertanyaan ke bos asli lewat Temu Bos.',
                'quota' => $e->isQuota(),
            ], $e->isQuota() ? Response::HTTP_TOO_MANY_REQUESTS : Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return response()->json($result + ['turns_left' => $this->engine->turnsLeft($session->user)]);
    }

    public function finish(BossSession $session): RedirectResponse
    {
        Gate::authorize('view', $session);

        if ($session->turns()->where('role', 'warga')->doesntExist()) {
            $session->delete();

            return redirect()->route('boss-room.setup')->with('status', 'Sesi diakhiri sebelum ada percakapan, jadi tidak ada catatan yang disimpan.');
        }

        if ($session->status === SessionStatus::Berjalan) {
            $session->update(['status' => SessionStatus::Dirangkum, 'ended_at' => now()]);
            SummarizeBossSession::dispatch($session);
        }

        return redirect()->route('boss-room.note', $session);
    }

    public function note(BossSession $session): View
    {
        Gate::authorize('view', $session);

        return view('boss-room.note', ['session' => $session->load('note')]);
    }

    public function pdf(BossSession $session): Response
    {
        Gate::authorize('view', $session);
        abort_unless($session->note, 404);

        return Pdf::loadView('pdf.consult-note', ['session' => $session->load('note', 'user')])
            ->download('catatan-konsultasi-'.$session->id.'.pdf');
    }

    public function addSteps(BossSession $session, StepService $steps): RedirectResponse
    {
        Gate::authorize('view', $session);
        $note = $session->note ?? abort(404);

        if ($note->steps_added_at) {
            return back()->with('status', 'Langkah dari catatan ini sudah ada di Langkah 30 Hari.');
        }

        $steps->addMany($session->user, $note->next_steps, StepOrigin::RuangBos);
        $note->update(['steps_added_at' => now()]);

        return back()->with('status', count($note->next_steps).' langkah ditambahkan ke Langkah 30 Hari.');
    }

    public function history(Request $request): View
    {
        return view('boss-room.history', [
            'sessions' => BossSession::query()->where('user_id', $request->user()->id)->with('note')->latest()->paginate(15),
        ]);
    }
}
