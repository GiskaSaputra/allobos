@php
    $config = [
        'view' => $view,
        'urls' => [
            'buy' => route('game.buy', $game), 'price' => route('game.price', $game),
            'run' => route('game.run', $game), 'next' => route('game.next', $game),
        ],
        'gifIdle' => asset('gif/bos-allo/allo-diam.gif'),
        'gifTalk' => asset('gif/bos-allo/allo-bicara.gif'),
        'still' => asset('gif/bos-allo/allo-transparan.webp'),
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

    <div data-warung data-config="{{ json_encode($config) }}" class="relative h-full overflow-hidden">
        <div data-stage class="absolute inset-0 lg:right-[420px]">
            <canvas data-canvas class="block h-full w-full" aria-hidden="true"></canvas>
            <div data-lite class="bg-hero absolute inset-0 hidden items-center justify-center p-6">
                <div class="max-w-sm rounded-[24px] bg-ink/45 p-5 text-center text-ink-fg backdrop-blur-md">
                    <x-phosphor-storefront class="mx-auto h-16 w-16 text-accent" aria-hidden="true" />
                    <p class="mt-2 font-display text-lg font-semibold" data-lite-caption>Warung simbah</p>
                    <p class="mt-1 text-sm text-ink-muted" data-lite-note>Mode Ringan: aturan dan hasil permainan sama.</p>
                </div>
            </div>
            <p class="sr-only" data-scene-desc aria-live="polite"></p>

            <div class="pointer-events-none absolute inset-x-3 top-3 flex flex-wrap items-start justify-between gap-2">
                <div class="pointer-events-auto rounded-[20px] bg-ink/60 px-4 py-2.5 text-ink-fg backdrop-blur-md">
                    <p class="text-xs text-ink-muted" data-hud-day>Hari 1 dari 7</p>
                    <p class="font-display font-semibold" data-hud-theme>Buka pertama</p>
                </div>
                <div class="pointer-events-auto flex gap-2 rounded-[20px] bg-ink/60 px-4 py-2.5 text-ink-fg backdrop-blur-md">
                    <span><span class="block text-xs text-ink-muted">Kas</span><span class="font-display font-semibold" data-hud-cash>Rp0</span></span>
                    <span class="ml-3"><span class="block text-xs text-ink-muted">Kepuasan</span><span class="font-display font-semibold" data-hud-sat>60</span></span>
                </div>
            </div>
            <div class="pointer-events-auto absolute bottom-3 left-3 flex gap-1.5" data-camera>
                <button type="button" class="btn btn-glass btn-icon btn-sm" data-cam="left" aria-label="Putar kamera ke kiri"><x-phosphor-arrow-arc-left class="h-5 w-5" aria-hidden="true" /></button>
                <button type="button" class="btn btn-glass btn-icon btn-sm" data-cam="right" aria-label="Putar kamera ke kanan"><x-phosphor-arrow-arc-right class="h-5 w-5" aria-hidden="true" /></button>
                <button type="button" class="btn btn-glass btn-icon btn-sm" data-cam="zoom" aria-label="Perbesar atau perkecil"><x-phosphor-magnifying-glass-plus class="h-5 w-5" aria-hidden="true" /></button>
            </div>
        </div>

        <aside class="absolute inset-x-0 bottom-0 max-h-[62%] overflow-y-auto rounded-t-[28px] border-t border-line bg-surface p-4 shadow-float lg:inset-y-0 lg:left-auto lg:right-0 lg:max-h-none lg:w-[420px] lg:rounded-none lg:border-l lg:border-t-0 lg:p-5" aria-label="Panel permainan">
            <div class="mb-4 flex items-start gap-3">
                <img data-bos src="{{ $config['gifIdle'] }}" alt="Bos Allo" width="400" height="225" class="h-16 w-28 shrink-0 rounded-2xl border border-line object-cover">
                <p data-bos-line class="text-sm text-fg-body" aria-live="polite"></p>
            </div>
            <div data-panel></div>
            <p data-error class="mt-3 hidden rounded-[20px] bg-danger/10 p-3 text-sm text-danger" role="alert"></p>
        </aside>
    </div>

    @push('scripts')
        @vite('resources/js/warung-3d.js')
    @endpush
</x-layouts.stage>
