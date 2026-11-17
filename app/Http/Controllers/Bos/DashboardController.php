<?php

namespace App\Http\Controllers\Bos;

use App\Enums\CertificateStatus;
use App\Enums\MentoringStatus;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\MentoringSession;
use App\Models\ProgramSigner;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $boss = $user->bossProfile;
        $sessions = $boss ? MentoringSession::query()->with(['slot', 'user'])->where('boss_profile_id', $boss->id) : null;

        return view('bos.dashboard', [
            'boss' => $boss,
            'pending' => $sessions ? (clone $sessions)->where('status', MentoringStatus::Menunggu->value)->latest()->get() : collect(),
            'upcoming' => $sessions ? (clone $sessions)->where('status', MentoringStatus::Disetujui->value)->whereHas('slot', fn ($q) => $q->where('starts_at', '>=', now()->subHours(3)))->get()->sortBy('slot.starts_at') : collect(),
            'completed' => $sessions ? (clone $sessions)->where('status', MentoringStatus::Selesai->value)->count() : 0,
            'toSign' => ProgramSigner::query()->with('program')->where('user_id', $user->id)->whereNull('signed_at')
                ->whereHas('program.certificates', fn ($q) => $q->where('status', CertificateStatus::MenungguTandaTangan->value))->get(),
            'announcements' => Announcement::query()->visibleTo('bos')->limit(2)->get(),
        ]);
    }
}
