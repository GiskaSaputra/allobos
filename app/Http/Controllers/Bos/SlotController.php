<?php

namespace App\Http\Controllers\Bos;

use App\Http\Controllers\Controller;
use App\Models\BossSlot;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SlotController extends Controller
{
    public function index(Request $request): View
    {
        $boss = $request->user()->bossProfile;

        return view('bos.slots', [
            'boss' => $boss,
            'slots' => $boss->slots()->with(['sessions' => fn ($q) => $q->latest()])->where('starts_at', '>=', now()->subDay())->orderBy('starts_at')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', Rule::in([30, 45, 60, 90, 120])],
            'mode' => ['required', Rule::in(array_keys(BossSlot::MODES))],
        ]);
        $start = Carbon::parse($data['date'].' '.$data['time']);
        if ($start->isPast()) {
            return back()->withInput()->with('error', 'Waktu slot sudah lewat. Pilih waktu yang akan datang.');
        }

        $boss = $request->user()->bossProfile;
        $overlap = $boss->slots()->where('starts_at', '<', $start->copy()->addMinutes($data['duration_minutes']))
            ->whereRaw('starts_at >= ?', [$start->copy()->subMinutes(120)])->get()
            ->contains(fn ($s) => $s->starts_at->copy()->addMinutes($s->duration_minutes)->gt($start));
        if ($overlap) {
            return back()->withInput()->with('error', 'Slot ini bertabrakan dengan slot lain milikmu.');
        }

        $boss->slots()->create(['starts_at' => $start, 'duration_minutes' => $data['duration_minutes'], 'mode' => $data['mode']]);

        return back()->with('status', 'Slot '.$start->translatedFormat('l, d F H:i').' WIB dibuka.');
    }

    public function destroy(Request $request, BossSlot $slot): RedirectResponse
    {
        abort_unless($slot->boss_profile_id === $request->user()->bossProfile?->id, 403);
        if (! BossSlot::query()->free()->whereKey($slot->id)->exists()) {
            return back()->with('error', 'Slot ini sudah dipesan. Batalkan sesinya dulu dari halaman sesi.');
        }
        $slot->delete();

        return back()->with('status', 'Slot dihapus.');
    }
}
