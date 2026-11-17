<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Services\Readiness\ReadinessCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(ReadinessCalculator $readiness): View
    {
        return view('admin.settings', [
            'weights' => $readiness->weights(),
            'labels' => config('readiness.labels'),
            'sessions' => (int) Setting::read('boss_sessions_per_day', config('features.boss_sessions_per_day')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $keys = array_keys(config('readiness.weights'));
        $data = $request->validate([
            'weights' => ['required', 'array'],
            ...collect($keys)->mapWithKeys(fn ($k) => ["weights.$k" => ['required', 'integer', 'between:0,100']])->all(),
            'sessions' => ['required', 'integer', 'between:1,50'],
        ]);
        $weights = collect($keys)->mapWithKeys(fn ($k) => [$k => (int) $data['weights'][$k]])->all();

        if (array_sum($weights) !== 100) {
            return back()->withInput()->with('error', 'Jumlah bobot harus tepat 100. Sekarang '.array_sum($weights).'.');
        }

        Setting::write('readiness.weights', $weights);
        Setting::write('boss_sessions_per_day', (int) $data['sessions']);
        AuditLog::record('pengaturan.ubah', null, ['bobot' => $weights, 'sesi_ruang_bos' => (int) $data['sessions']]);

        return back()->with('status', 'Pengaturan disimpan. Skor dihitung ulang saat ada bukti baru dari tiap warga.');
    }
}
