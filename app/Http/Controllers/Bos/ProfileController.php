<?php

namespace App\Http\Controllers\Bos;

use App\Enums\BossStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\BossProfile;
use App\Models\BusinessField;
use App\Models\District;
use App\Models\User;
use App\Notifications\Pemberitahuan;
use App\Services\Media\ImageStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('bos.profile', [
            'boss' => $request->user()->bossProfile ?? new BossProfile,
            'fields' => BusinessField::query()->ordered()->pluck('name', 'id'),
            'districts' => District::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(Request $request, ImageStore $images): RedirectResponse
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:160'],
            'business_desc' => ['required', 'string', 'min:20', 'max:2000'],
            'business_field_id' => ['required', 'exists:business_fields,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'years_running' => ['required', 'integer', 'min:0', 'max:80'],
            'track_record' => ['nullable', 'string', 'max:2000'],
            'trace_url' => ['nullable', 'url:http,https', 'max:255'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
        ]);

        $boss = $request->user()->bossProfile ?? new BossProfile(['user_id' => $request->user()->id, 'status' => BossStatus::Diajukan]);
        if ($request->hasFile('photo')) {
            $images->delete($boss->photo_path);
            $data['photo_path'] = $images->store($request->file('photo'), 'bos', 'photo', 600);
        }
        unset($data['photo']);

        $resubmit = $boss->status === BossStatus::Ditolak;
        $boss->fill($data + ($resubmit ? ['status' => BossStatus::Diajukan, 'reject_reason' => null] : []))->save();

        if ($resubmit || $boss->wasRecentlyCreated) {
            Notification::send(User::role(RoleName::Pengelola->value)->get(), new Pemberitahuan('Profil Bos diajukan', $request->user()->name.' mengajukan profil Bos untuk dikurasi.', route('pengelola.bosses.index')));
        }

        return back()->with('status', $resubmit ? 'Profil diajukan ulang untuk dikurasi.' : 'Profil Bos tersimpan.');
    }
}
