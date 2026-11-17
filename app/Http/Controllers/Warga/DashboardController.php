<?php

namespace App\Http\Controllers\Warga;

use App\Enums\EnrollmentStatus;
use App\Enums\MentoringStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\MentoringSession;
use App\Models\Podcast;
use App\Models\ProgramSession;
use App\Models\ReadinessSnapshot;
use App\Services\Readiness\ReadinessCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ReadinessCalculator $readiness): View
    {
        $user = $request->user();
        $breakdown = $readiness->breakdown($user);
        $score = $readiness->score($breakdown);

        $classSessions = ProgramSession::query()
            ->with('program')
            ->where('ends_at', '>=', now())
            ->whereHas('program.enrollments', fn ($q) => $q->where('user_id', $user->id)->whereIn('status', EnrollmentStatus::participating()))
            ->orderBy('starts_at')->limit(4)->get()
            ->map(fn ($s) => ['when' => $s->starts_at, 'title' => $s->program->title.', '.$s->title, 'kind' => 'Kelas', 'url' => route('warga.programs.index')]);

        $mentoring = MentoringSession::query()
            ->with(['slot', 'boss.user'])
            ->where('user_id', $user->id)
            ->whereIn('status', [MentoringStatus::Menunggu->value, MentoringStatus::Disetujui->value])
            ->whereHas('slot', fn ($q) => $q->where('starts_at', '>=', now()))
            ->get()
            ->map(fn ($s) => ['when' => $s->slot->starts_at, 'title' => 'Temu Bos dengan '.$s->boss->user->name, 'kind' => $s->status->label(), 'url' => route('warga.mentoring.show', $s)]);

        $podcasts = Podcast::query()->published()
            ->whereIn('business_field_id', $user->profile->interests ?? [])
            ->whereDoesntHave('listens', fn ($q) => $q->where('user_id', $user->id)->whereNotNull('listened_at'))
            ->latest('published_at')->limit(3)->get();

        return view('warga.dashboard', [
            'user' => $user,
            'breakdown' => $breakdown,
            'score' => $score,
            'level' => $readiness->level($score),
            'next' => $readiness->nextAction($user, $breakdown),
            'agenda' => $classSessions->concat($mentoring)->sortBy('when')->take(5)->values(),
            'podcasts' => $podcasts,
            'announcements' => Announcement::query()->visibleTo('warga')->limit(2)->get(),
            'recent' => ActivityLog::query()->where('user_id', $user->id)->latest('id')->limit(4)->get(),
        ]);
    }

    public function journey(Request $request, ReadinessCalculator $readiness): View
    {
        $user = $request->user();
        $request->validate(['modul' => ['nullable', Rule::in(array_keys(ActivityLog::MODULES))]]);
        $breakdown = $readiness->breakdown($user);

        $snapshots = ReadinessSnapshot::query()->where('user_id', $user->id)->orderBy('id')->get(['score', 'created_at']);

        return view('warga.journey', [
            'breakdown' => $breakdown,
            'score' => $readiness->score($breakdown),
            'chart' => [
                'type' => 'line',
                'labels' => $snapshots->map(fn ($s) => $s->created_at->translatedFormat('d M'))->all(),
                'series' => [['label' => 'Skor Siap Usaha', 'data' => $snapshots->pluck('score')->all(), 'color' => 'brand']],
                'max' => 100,
            ],
            'module' => $request->query('modul'),
            'activities' => ActivityLog::query()->where('user_id', $user->id)
                ->when($request->query('modul'), fn ($q, $m) => $q->where('module', $m))
                ->latest('id')->paginate(20)->withQueryString(),
        ]);
    }
}
