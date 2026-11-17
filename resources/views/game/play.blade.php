@php
    $config = [
        'view' => $view,
        'gameId' => $game->id,
        'urls' => [
            'buy' => route('game.buy', $game), 'price' => route('game.price', $game),
            'run' => route('game.run', $game), 'next' => route('game.next', $game),
            'stock' => route('game.stock', $game), 'open' => route('game.open', $game),
            'serve' => route('game.serve', $game), 'miss' => route('game.miss', $game),
            'close' => route('game.close', $game), 'exit' => route('game.index'),
        ],
        'clipIdle' => asset('gif/bos-allo/allo-diam.mp4'),
        'clipTalk' => asset('gif/bos-allo/allo-bicara.mp4'),
    ];
@endphp
<x-layouts.stage title="Warung Bos 3D" :exit="route('game.index')">
    <x-slot:actions>
        <button type="button" class="btn btn-ghost btn-sm" data-lite-toggle>Mode Ringan</button>
        <button type="button" class="btn btn-ghost btn-icon btn-sm" data-sound-toggle aria-pressed="false" aria-label="Nyalakan efek suara">
            <x-phosphor-speaker-slash class="h-5 w-5" aria-hidden="true" data-sound-off />
            <x-phosphor-speaker-high class="hidden h-5 w-5" aria-hidden="true" data-sound-on />
        </button>
    </x-slot:actions>

    <div data-warung data-config="{{ json_encode($config) }}" class="relative h-full overflow-hidden bg-ink">
        <div data-stage class="absolute inset-0">
            <canvas data-canvas class="block h-full w-full touch-none" aria-label="Warung tiga dimensi. Pakai Mode Ringan bila sulit dimainkan."></canvas>
            <div data-lite class="bg-hero absolute inset-0 hidden items-center justify-center p-6">
                <div class="max-w-sm rounded-[24px] bg-ink/45 p-5 text-center text-ink-fg backdrop-blur-md">
                    <x-phosphor-storefront class="mx-auto h-16 w-16 text-accent" aria-hidden="true" />
                    <p class="mt-2 font-display text-lg font-semibold" data-lite-caption>Warung simbah</p>
                    <p class="mt-1 text-sm text-ink-muted">Mode Ringan: aturan dan hasil permainan sama.</p>
                </div>
            </div>
            <p class="sr-only" data-scene-desc aria-live="polite"></p>

            {{-- HUD minimal: hari, kas, kepuasan, tujuan, bidik, petunjuk --}}
            <div class="pointer-events-none absolute inset-x-3 top-3 flex items-start justify-between gap-2">
                <div class="rounded-[20px] bg-ink/60 px-4 py-2.5 text-ink-fg backdrop-blur-md">
                    <p class="text-xs text-ink-muted" data-hud-day>Hari 1 dari 7</p>
                    <p class="font-display text-sm font-semibold sm:text-base" data-hud-theme>Buka pertama</p>
                </div>
                <div class="flex gap-4 rounded-[20px] bg-ink/60 px-4 py-2.5 text-ink-fg backdrop-blur-md">
                    <span><span class="block text-xs text-ink-muted">Kas</span><span class="font-display text-sm font-semibold sm:text-base" data-hud-cash>Rp0</span></span>
                    <span><span class="block text-xs text-ink-muted">Kepuasan</span><span class="font-display text-sm font-semibold sm:text-base" data-hud-sat>60</span></span>
                </div>
            </div>
            <p data-objective class="pointer-events-none absolute left-1/2 top-[76px] max-w-[calc(100%-24px)] -translate-x-1/2 rounded-full bg-accent px-4 py-1.5 text-center text-xs font-semibold text-accent-fg shadow-float sm:top-3 sm:max-w-md sm:text-sm" aria-live="polite"></p>
            <div data-crosshair class="pointer-events-none absolute left-1/2 top-1/2 h-2 w-2 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-ink-fg bg-ink/40" aria-hidden="true"></div>
            <p data-prompt class="pointer-events-none absolute left-1/2 top-[calc(50%+22px)] hidden max-w-[80%] -translate-x-1/2 rounded-full bg-ink/70 px-3 py-1.5 text-center text-sm font-medium text-ink-fg backdrop-blur-md"></p>

            <div data-bos-toast class="pointer-events-none absolute bottom-3 left-3 flex max-w-[min(420px,calc(100%-24px))] items-center gap-3 rounded-[20px] bg-ink/70 p-2 pr-4 text-ink-fg backdrop-blur-md">
                <video data-bos poster="{{ asset('gif/bos-allo/allo-transparan.webp') }}" width="400" height="226" muted loop playsinline preload="none" aria-hidden="true" class="h-12 w-20 shrink-0 rounded-xl object-cover"></video>
                <p data-bos-line class="text-sm" aria-live="polite"></p>
            </div>

            {{-- Kontrol sentuh, hanya tampil di layar sentuh saat mode 3D --}}
            <div data-touch class="absolute inset-0 hidden" aria-hidden="true">
                <div data-touch-look class="absolute inset-y-0 right-0 w-1/2"></div>
                <div data-touch-stick class="absolute bottom-24 left-6 grid h-28 w-28 place-items-center rounded-full border border-ink-fg/30 bg-ink/30 backdrop-blur-sm">
                    <span data-touch-knob class="h-12 w-12 rounded-full bg-ink-fg/80 transition-transform duration-75"></span>
                </div>
                <div class="absolute bottom-24 right-5 flex flex-col items-end gap-3">
                    <button type="button" data-touch-action="INTERACT" class="grid h-16 w-16 place-items-center rounded-full bg-accent text-sm font-semibold text-accent-fg shadow-float">Pakai</button>
                    <button type="button" data-touch-action="PICK_UP" class="grid h-12 w-12 place-items-center rounded-full bg-ink/60 text-xs font-semibold text-ink-fg backdrop-blur-md">Ambil</button>
                </div>
                <button type="button" data-touch-action="PAUSE" class="absolute right-3 top-20 grid h-10 w-10 place-items-center rounded-full bg-ink/60 text-ink-fg backdrop-blur-md" aria-label="Jeda"><x-phosphor-pause class="h-5 w-5" /></button>
            </div>

            <div data-start class="absolute inset-0 hidden items-center justify-center bg-ink/55 p-4 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-[28px] bg-surface-raised p-6 text-fg shadow-float">
                    <p class="text-xs font-medium uppercase tracking-[0.12em] text-fg-muted">Warung Bos 3D</p>
                    <h2 class="mt-1 text-2xl">Selamat datang di warung simbah</h2>
                    <p class="mt-2 text-sm text-fg-body">Jalan di dalam warung, belanja ke pemasok, pajang barang di rak, buka warung, lalu layani pembeli di kasir.</p>
                    <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 text-sm" data-controls></dl>
                    <button type="button" class="btn btn-primary btn-lg btn-full mt-5" data-start-btn>Masuk ke Warung</button>
                </div>
            </div>
        </div>

        <section data-sheet aria-label="Panel permainan" role="dialog" aria-modal="false">
            <div class="mb-3 flex items-start justify-between gap-3">
                <p class="font-display text-xs font-semibold uppercase tracking-[0.12em] text-fg-muted" data-sheet-title>Panel</p>
                <button type="button" class="btn btn-ghost btn-icon btn-sm -mr-2 -mt-1" data-sheet-close aria-label="Tutup panel"><x-phosphor-x class="h-5 w-5" aria-hidden="true" /></button>
            </div>
            <div data-panel></div>
            <p data-error class="mt-3 hidden rounded-[20px] bg-danger/10 p-3 text-sm text-danger" role="alert"></p>
        </section>
    </div>

    @push('scripts')
        @vite('resources/js/warung-3d.js')
    @endpush
</x-layouts.stage>
