<?php

namespace App\Http\Controllers\Pengelola;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\Speaker;
use App\Models\User;
use App\Services\Media\ImageStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpeakerController extends Controller
{
    public function __construct(private readonly ImageStore $images) {}

    public function index(Request $request): View
    {
        return view('pengelola.speakers.index', [
            'speakers' => Speaker::query()->with('user')->withCount('programs')
                ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', '%'.$term.'%'))
                ->orderBy('name')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Speaker);
    }

    public function store(Request $request): RedirectResponse
    {
        $speaker = Speaker::query()->create($this->data($request));

        return redirect()->route('pengelola.speakers.index')->with('status', 'Pembicara '.$speaker->name.' ditambahkan.');
    }

    public function edit(Speaker $speaker): View
    {
        return $this->form($speaker);
    }

    public function update(Request $request, Speaker $speaker): RedirectResponse
    {
        $speaker->update($this->data($request, $speaker));

        return redirect()->route('pengelola.speakers.index')->with('status', 'Data pembicara diperbarui.');
    }

    public function destroy(Speaker $speaker): RedirectResponse
    {
        if ($speaker->programs()->exists()) {
            return back()->with('error', 'Pembicara ini masih tercantum di kelas. Lepaskan dari kelas dulu.');
        }
        $this->images->delete($speaker->photo_path);
        $speaker->delete();

        return back()->with('status', 'Pembicara dihapus.');
    }

    private function form(Speaker $speaker): View
    {
        return view('pengelola.speakers.form', [
            'speaker' => $speaker,
            'bosses' => User::role(RoleName::Bos->value)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    private function data(Request $request, ?Speaker $speaker = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'business' => ['required', 'string', 'max:160'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'user_id' => ['nullable', 'exists:users,id'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $data['is_demo'] = $request->boolean('is_demo');

        if ($request->hasFile('photo')) {
            $this->images->delete($speaker?->photo_path);
            $data['photo_path'] = $this->images->store($request->file('photo'), 'pembicara', 'photo', 600);
        }
        unset($data['photo']);

        return $data;
    }
}
