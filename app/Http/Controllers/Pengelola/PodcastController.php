<?php

namespace App\Http\Controllers\Pengelola;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SummarizePodcast;
use App\Models\BusinessField;
use App\Models\Podcast;
use App\Services\Podcasts\PodcastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PodcastController extends Controller
{
    public function __construct(private readonly PodcastService $podcasts) {}

    public function index(Request $request): View
    {
        return view('pengelola.podcasts.index', [
            'podcasts' => Podcast::query()->with('field')->withCount(['listens' => fn ($q) => $q->whereNotNull('listened_at')])
                ->when($request->query('q'), fn ($q, $term) => $q->where('title', 'like', '%'.$term.'%'))
                ->latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('pengelola.podcasts.create');
    }

    public function fetch(Request $request): RedirectResponse
    {
        $url = $request->validate(['url' => ['required', 'string', 'max:255']], ['url.required' => 'Tempel tautan video YouTube dulu.'])['url'];

        try {
            $podcast = $this->podcasts->fetch($url);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['url' => $e->getMessage()]);
        }

        return redirect()->route('pengelola.podcasts.edit', $podcast)->with('status', 'Data episode diambil. Periksa judul, isi bidang dan catatan, lalu Buat Ringkasan.');
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->fetch($request);
    }

    public function edit(Podcast $podcast): View
    {
        return view('pengelola.podcasts.edit', [
            'podcast' => $podcast,
            'fields' => BusinessField::query()->ordered()->pluck('name', 'id'),
        ]);
    }

    public function update(Request $request, Podcast $podcast): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:250'],
            'business_field_id' => ['nullable', 'exists:business_fields,id'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'source_notes' => ['nullable', 'string', 'max:5000'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'lessons' => ['nullable', 'array', 'max:3'],
            'lessons.*' => ['nullable', 'string', 'max:300'],
        ]);

        $podcast->update([
            'title' => $data['title'],
            'business_field_id' => $data['business_field_id'] ?? null,
            'duration_seconds' => isset($data['duration_minutes']) ? $data['duration_minutes'] * 60 : null,
            'source_notes' => $data['source_notes'] ?? null,
            'summary' => $data['summary'] ?? null,
            'lessons' => array_values(array_filter($data['lessons'] ?? [])),
            'is_demo' => $request->boolean('is_demo'),
        ]);

        return back()->with('status', 'Episode disimpan.');
    }

    public function summarize(Request $request, Podcast $podcast): RedirectResponse
    {
        $podcast->update(['summary_state' => 'memproses']);
        SummarizePodcast::dispatch($podcast, $request->user());

        return back()->with('status', 'Ringkasan sedang disusun. Halaman ini memuat ulang sendiri.');
    }

    public function publish(Podcast $podcast): RedirectResponse
    {
        try {
            $this->podcasts->publish($podcast);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Episode terbit dan warga dengan minat yang sama diberi tahu.');
    }

    public function destroy(Podcast $podcast): RedirectResponse
    {
        $podcast->delete();

        return redirect()->route('pengelola.podcasts.index')->with('status', 'Episode dihapus.');
    }

    public function show(Podcast $podcast): RedirectResponse
    {
        return redirect()->route($podcast->status === ContentStatus::Terbit ? 'podcasts.show' : 'pengelola.podcasts.edit', $podcast);
    }
}
