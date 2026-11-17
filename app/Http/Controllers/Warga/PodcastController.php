<?php

namespace App\Http\Controllers\Warga;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Services\Podcasts\PodcastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PodcastController extends Controller
{
    public function __construct(private readonly PodcastService $podcasts) {}

    public function listen(Request $request, Podcast $podcast): RedirectResponse
    {
        abort_unless($podcast->status === ContentStatus::Terbit, 404);
        $this->podcasts->markListened($request->user(), $podcast);

        return back()->with('status', 'Episode ditandai sudah didengar.');
    }

    public function note(Request $request, Podcast $podcast): RedirectResponse
    {
        abort_unless($podcast->status === ContentStatus::Terbit, 404);
        $note = $request->validate(['note' => ['nullable', 'string', 'max:2000']])['note'] ?? null;
        $this->podcasts->saveNote($request->user(), $podcast, $note);

        return back()->with('status', 'Catatan tersimpan.');
    }
}
