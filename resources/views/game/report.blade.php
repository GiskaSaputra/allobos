@php($rp = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.'))
<x-layouts.app title="Rapor Warung">
    <section @class(['overflow-hidden rounded-[28px] p-5 sm:rounded-panel sm:p-10', 'bg-hero' => $game->ending !== 'belum', 'bg-mint' => $game->ending === 'belum'])>
        <div class="grid items-center gap-6 lg:grid-cols-[1.3fr_1fr]">
            <div class="rounded-[24px] bg-ink/50 p-6 text-ink-fg backdrop-blur-md sm:p-8">
                <p class="text-sm text-ink-muted">{{ $kind }}, tujuh hari selesai</p>
                <h1 class="mt-2 text-3xl text-ink-fg sm:text-4xl">{{ $ending['title'] }}</h1>
                <p class="mt-3 text-ink-muted">{{ $ending['body'] }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($game->saved_at)
                        <button class="btn btn-accent" disabled><x-phosphor-check class="h-5 w-5" aria-hidden="true" />Tersimpan di Jejak Usaha</button>
                    @else
                        <form method="POST" action="{{ route('game.save', $game) }}">@csrf<x-button variant="accent" loading="Menyimpan">Simpan Pelajaran ke Jejak Usaha</x-button></form>
                    @endif
                    <form method="POST" action="{{ route('game.store') }}">@csrf<x-button variant="glass">Main Lagi</x-button></form>
                </div>
            </div>
            <div class="rounded-[24px] bg-ink/50 p-6 text-center text-ink-fg backdrop-blur-md">
                <p class="text-sm text-ink-muted">Nilai akhir</p>
                <p class="font-display text-7xl font-bold text-accent">{{ $game->score }}</p>
                <p class="text-sm text-ink-muted">dari 100</p>
            </div>
        </div>
    </section>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="space-y-4">
            <x-card>
                <div class="flex flex-wrap items-center justify-between gap-2"><h2 class="text-xl">Catatan Bos Allo</h2>@if ($game->debrief['is_ai'] ?? false)<x-ai-badge />@endif</div>
                <dl class="mt-3 space-y-3">
                    <div><dt class="font-medium text-fg">Keputusan terbaik</dt><dd class="text-fg-body">{{ $game->debrief['best'] }}</dd></div>
                    <div><dt class="font-medium text-fg">Keputusan yang perlu diperbaiki</dt><dd class="text-fg-body">{{ $game->debrief['worst'] }}</dd></div>
                </dl>
                <h3 class="mt-5">Tiga pelajaran</h3>
                <ol class="mt-2 space-y-2">
                    @foreach ($game->debrief['lessons'] as $i => $lesson)<li class="flex gap-3 text-fg-body"><span class="font-display text-xl font-bold text-accent-text">{{ $i + 1 }}</span>{{ $lesson }}</li>@endforeach
                </ol>
            </x-card>
            <x-card>
                <h2 class="text-xl">Hari demi hari</h2>
                <x-table class="mt-3">
                    <thead><tr><th>Hari</th><th>Terjual</th><th>Tidak kebagian</th><th>Terbuang</th><th>Laba</th></tr></thead>
                    <tbody>
                        @foreach ($game->days as $day)
                            <tr>
                                <td data-label="Hari">{{ $day->day }}. {{ App\Services\Game\GameRules::THEMES[$day->day]['title'] }}</td>
                                <td data-label="Terjual">{{ $day->result['sold_total'] }}</td>
                                <td data-label="Tidak kebagian">{{ $day->result['lost_total'] }}</td>
                                <td data-label="Terbuang">{{ $rp($day->result['waste_value']) }}</td>
                                <td data-label="Laba" @class(['font-medium', 'text-danger' => $day->result['profit'] < 0, 'text-fg' => $day->result['profit'] >= 0])>{{ $rp($day->result['profit']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-table>
            </x-card>
        </div>
        <aside>
            <x-card>
                <h2 class="text-xl">Rincian nilai</h2>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-fg-muted">Laba total</dt><dd class="font-medium text-fg">{{ $rp($game->state['total_profit']) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-fg-muted">Kas akhir</dt><dd class="font-medium text-fg">{{ $rp($game->state['cash']) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-fg-muted">Kepuasan pelanggan</dt><dd class="font-medium text-fg">{{ $game->state['satisfaction'] }}</dd></div>
                    @foreach ($game->debrief['parts'] ?? [] as $label => $value)
                        <div class="flex justify-between border-t border-line pt-2"><dt class="text-fg-muted">{{ ucfirst(str_replace('_', ' ', $label)) }}</dt><dd class="text-fg">{{ $value > 0 ? '+' : '' }}{{ str_replace('.', ',', $value) }}</dd></div>
                    @endforeach
                </dl>
            </x-card>
        </aside>
    </div>
</x-layouts.app>
