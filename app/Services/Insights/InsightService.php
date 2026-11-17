<?php

namespace App\Services\Insights;

use App\Models\Attendance;
use App\Models\BossSession;
use App\Models\BusinessField;
use App\Models\Program;
use App\Models\ResidentProfile;
use Illuminate\Support\Facades\DB;

/** Agregat tanpa identitas pribadi untuk Pengelola IPI (DP-02). */
class InsightService
{
    /** @return array<string,array{title:string,label:string,rows:list<array{0:string,1:int}>}> */
    public function all(): array
    {
        $fields = BusinessField::query()->pluck('name', 'id');
        $interestCounts = ResidentProfile::query()->whereNotNull('interests')->pluck('interests')
            ->flatten()->countBy()->sortDesc()->take(10)
            ->map(fn ($count, $id) => [$fields[$id] ?? 'Lainnya', $count])->values()->all();

        return [
            'topik' => [
                'title' => 'Topik konsultasi terbanyak',
                'label' => 'Topik',
                'rows' => BossSession::query()->select('topic', DB::raw('count(*) as total'))->groupBy('topic')->orderByDesc('total')->limit(10)->get()
                    ->map(fn ($r) => [$r->topic, (int) $r->total])->all(),
            ],
            'bidang' => ['title' => 'Bidang paling diminati', 'label' => 'Bidang', 'rows' => $interestCounts],
            'kapanewon' => [
                'title' => 'Sebaran warga per kapanewon',
                'label' => 'Kapanewon',
                'rows' => ResidentProfile::query()->join('districts', 'districts.id', '=', 'resident_profiles.district_id')
                    ->select('districts.name', DB::raw('count(*) as total'))->groupBy('districts.name')->orderByDesc('total')->get()
                    ->map(fn ($r) => [$r->name, (int) $r->total])->all(),
            ],
            'kehadiran' => [
                'title' => 'Kehadiran kelas',
                'label' => 'Kelas',
                'rows' => Program::query()->withCount('sessions')->latest()->limit(10)->get()
                    ->map(fn ($p) => [$p->title, Attendance::query()->whereIn('program_session_id', $p->sessions()->pluck('id'))->count()])->all(),
            ],
        ];
    }
}
