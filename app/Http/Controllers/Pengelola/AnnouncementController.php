<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('pengelola.announcements.index', ['announcements' => Announcement::query()->latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('pengelola.announcements.form', ['announcement' => new Announcement(['audience' => 'semua'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Announcement::query()->create($this->data($request) + ['created_by' => $request->user()->id]);

        return redirect()->route('pengelola.announcements.index')->with('status', 'Pengumuman disimpan.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('pengelola.announcements.form', ['announcement' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($this->data($request));

        return redirect()->route('pengelola.announcements.index')->with('status', 'Pengumuman diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('status', 'Pengumuman dihapus.');
    }

    private function data(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:1000'],
            'audience' => ['required', Rule::in(array_keys(Announcement::AUDIENCES))],
        ]);

        return $data + ['published_at' => $request->boolean('publish') ? now() : null];
    }
}
