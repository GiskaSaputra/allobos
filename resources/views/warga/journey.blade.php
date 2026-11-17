<x-layouts.app title="Jejak Usaha">
    <x-page-header title="Jejak Usaha" description="Perkembangan Skor Siap Usaha dan semua aktivitasmu di alloBos." />
    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <x-card>
            <h2 class="text-xl">Perkembangan skor</h2>
            @if (count($chart['labels']) < 2)
                <p class="mt-3 text-fg-muted">Grafik muncul setelah skormu berubah minimal satu kali. Skor sekarang {{ $score }}.</p>
            @else
                <x-chart :config="$chart" label="Grafik Skor Siap Usaha dari waktu ke waktu" class="mt-4" />
            @endif
        </x-card>
        <x-card>
            <h2 class="text-xl">Rincian skor</h2>
            <ul class="mt-4 space-y-3">
                @foreach ($breakdown as $part)
                    <li>
                        <div class="flex justify-between text-sm"><span class="font-medium text-fg">{{ $part['label'] }}</span><span class="text-fg-muted">{{ round($part['points']) }} / {{ $part['weight'] }}</span></div>
                        <p class="text-xs text-fg-muted">{{ $part['detail'] }}</p>
                    </li>
                @endforeach
            </ul>
        </x-card>
    </div>

    <section class="mt-8">
        <h2 class="text-xl">Riwayat aktivitas</h2>
        <nav class="chip-row mt-3" aria-label="Saring modul">
            <a href="{{ route('warga.journey') }}" @class(['chip', 'chip-active' => ! $module])>Semua</a>
            @foreach (App\Models\ActivityLog::MODULES as $key => $label)
                <a href="{{ route('warga.journey', ['modul' => $key]) }}" @class(['chip', 'chip-active' => $module === $key])>{{ $label }}</a>
            @endforeach
        </nav>
        @if ($activities->isEmpty())
            <x-empty-state icon="phosphor-footprints" title="Belum ada aktivitas" class="mt-4">Aktivitas di modul ini akan tercatat otomatis.</x-empty-state>
        @else
            <ol class="mt-4 space-y-2">
                @foreach ($activities as $activity)
                    <li class="card flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-4">
                        <div>
                            <span class="badge badge-neutral">{{ App\Models\ActivityLog::MODULES[$activity->module] ?? $activity->module }}</span>
                            @if ($activity->url)
                                <a href="{{ $activity->url }}" class="ml-2 font-medium text-fg hover:underline">{{ $activity->description }}</a>
                            @else
                                <span class="ml-2 font-medium text-fg">{{ $activity->description }}</span>
                            @endif
                        </div>
                        <time class="text-sm text-fg-muted" datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->translatedFormat('d M Y, H:i') }}</time>
                    </li>
                @endforeach
            </ol>
            <div class="mt-6">{{ $activities->links() }}</div>
        @endif
    </section>
</x-layouts.app>
