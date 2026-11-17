<x-layouts.app title="Warung Bos 3D">
    <section class="bg-hero overflow-hidden rounded-[28px] p-5 sm:rounded-panel sm:p-10">
        <div class="grid items-center gap-8 lg:grid-cols-[1.2fr_1fr]">
            <div class="max-w-xl rounded-[24px] bg-ink/45 p-6 text-ink-fg backdrop-blur-md sm:p-8">
                <h1 class="text-3xl text-ink-fg sm:text-4xl">Warung Bos 3D</h1>
                <p class="mt-3 text-ink-muted">Kamu mewarisi warung kecil milik simbah di pinggir jalan menuju pantai. Warung itu sepi dan hampir tutup. Kamu punya tujuh hari sebelum musim liburan untuk membuktikan warung itu bisa hidup.</p>
                <p class="mt-3 text-sm text-ink-muted">Masuk ke warung dari sudut pandang orang pertama: belanja ke pemasok, pajang peti di rak, buka warung, lalu layani pembeli di kasir. Bisa dimainkan dengan papan ketik dan tetikus, gamepad, atau layar sentuh. Mode Ringan tersedia untuk perangkat yang berat menjalankan 3D.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($running)
                        <x-button variant="accent" size="lg" :href="route('game.play', $running)" icon="phosphor-storefront">Lanjutkan Warung</x-button>
                        <form method="POST" action="{{ route('game.store') }}" data-confirm="Mulai permainan baru? Permainan yang sedang berjalan akan ditinggalkan." data-confirm-ok="Mulai Baru">@csrf<x-button variant="glass" size="lg">Main dari Awal</x-button></form>
                    @else
                        <form method="POST" action="{{ route('game.store') }}">@csrf<x-button variant="accent" size="lg" icon="phosphor-storefront" loading="Membuka warung">Buka Warung</x-button></form>
                    @endif
                </div>
                @if ($running)<p class="mt-3 text-sm text-ink-muted">Permainanmu tersimpan di hari {{ $running->day }}.</p>@endif
            </div>
            <x-boss-avatar class="hidden lg:block" />
        </div>
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-[1fr_340px]">
        <div>
            <h2 class="mb-3 text-xl">Tujuh babak</h2>
            <ol class="grid gap-2 sm:grid-cols-2">
                @foreach (App\Services\Game\GameRules::THEMES as $day => $theme)
                    <li class="card flex gap-3 p-4 sm:p-4"><span class="font-display text-2xl font-bold text-accent-text">{{ $day }}</span><span><span class="block font-medium text-fg">{{ $theme['title'] }}</span><span class="text-sm text-fg-muted">{{ $theme['lesson'] }}</span></span></li>
                @endforeach
            </ol>
        </div>
        <aside class="space-y-4">
            <x-card>
                <h2 class="text-xl">Nilai terbaik</h2>
                <p class="mt-1 font-display text-4xl font-bold text-fg">{{ $best ?? '-' }}<span class="text-lg text-fg-muted"> / 100</span></p>
                <p class="mt-1 text-sm text-fg-muted">Nilai terbaik yang disimpan masuk Skor Siap Usaha.</p>
            </x-card>
            @if ($finished->isNotEmpty())
                <x-card>
                    <h2 class="text-xl">Permainan selesai</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($finished as $game)
                            <li><a href="{{ route('game.report', $game) }}" class="flex items-center justify-between rounded-2xl px-2 py-2 text-sm hover:bg-fg/5"><span class="text-fg">{{ $game->updated_at->translatedFormat('d M Y') }}</span><span class="font-medium text-fg">{{ $game->score }}</span></a></li>
                        @endforeach
                    </ul>
                </x-card>
            @endif
        </aside>
    </section>
</x-layouts.app>
