@php
    $config = [
        'startUrl' => route('boss-room.start', $session),
        'sendUrl' => route('boss-room.send', $session),
        'status' => $session->status->value,
        'mode' => $session->mode,
        'turns' => $turns,
        'turnsLeft' => $turnsLeft,
        'gifIdle' => asset('gif/bos-allo/allo-diam.gif'),
        'gifTalk' => asset('gif/bos-allo/allo-bicara.gif'),
        'still' => asset('gif/bos-allo/allo-transparan.webp'),
        'mentoringUrl' => Route::has('warga.mentoring.index') ? route('warga.mentoring.index') : null,
    ];
@endphp
<x-layouts.stage title="Ruang Bos: {{ $session->topic }}" :exit="route('warga.dashboard')" confirm-exit="Sesi masih berjalan. Keluar sekarang? Kamu bisa kembali ke sesi ini dari Riwayat, atau tekan Akhiri Sesi untuk mendapat catatan.">
    <div data-boss-room data-config="{{ json_encode($config) }}" data-state="idle" class="grid h-full grid-rows-[auto_1fr] lg:grid-cols-[minmax(340px,40%)_1fr] lg:grid-rows-1">
        <section class="flex flex-col gap-3 border-b border-line p-3 sm:p-5 lg:border-b-0 lg:border-r">
            <div class="relative mx-auto w-full max-w-[520px]">
                <figure class="overflow-hidden rounded-card border border-line bg-surface-alt shadow-card">
                    <img data-avatar src="{{ $config['gifIdle'] }}" alt="Bos Allo" width="400" height="225" class="block aspect-video max-h-[30dvh] w-full object-cover lg:max-h-none">
                </figure>
                <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between gap-2">
                    <p data-status class="rounded-full bg-ink/65 px-3 py-1.5 text-xs font-medium text-ink-fg backdrop-blur-md" role="status" aria-live="polite">Bos Allo siap mendengar</p>
                    <div class="flex gap-1.5">
                        <button type="button" data-replay class="btn btn-glass btn-sm" disabled>Ulangi Jawaban</button>
                        <button type="button" data-mute class="btn btn-glass btn-icon btn-sm" aria-pressed="false" aria-label="Bisukan suara Bos Allo">
                            <x-phosphor-speaker-high class="h-5 w-5" aria-hidden="true" data-icon-on />
                            <x-phosphor-speaker-slash class="hidden h-5 w-5" aria-hidden="true" data-icon-off />
                        </button>
                    </div>
                </div>
            </div>
            <div class="hidden space-y-2 lg:block">
                <p class="label-caps">Topik</p>
                <p class="font-display text-lg font-semibold text-fg">{{ $session->topic }}</p>
            </div>
            <p data-notice class="hidden rounded-[20px] bg-info/15 p-3 text-sm text-info" role="note"></p>
        </section>

        <section class="flex min-h-0 flex-col">
            <ol data-log class="flex-1 space-y-4 overflow-y-auto p-4 sm:p-6" aria-live="polite" aria-label="Percakapan dengan Bos Allo"></ol>

            <div data-error class="mx-4 mb-3 hidden rounded-[20px] border border-danger/40 bg-danger/10 p-4 sm:mx-6" role="alert">
                <p data-error-text class="text-sm text-fg"></p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" data-retry class="btn btn-secondary btn-sm">Coba Lagi</button>
                    @if ($config['mentoringUrl'])<a href="{{ $config['mentoringUrl'] }}" class="btn btn-ghost btn-sm">Ajukan ke Bos Asli</a>@endif
                </div>
            </div>

            <form data-composer class="pb-safe border-t border-line bg-surface p-3 sm:p-4">
                <label for="pesan" class="sr-only">Pesan untuk Bos Allo</label>
                <div class="flex items-end gap-2">
                    <button type="button" data-talk class="btn btn-accent btn-icon btn-xl shrink-0" aria-label="Tahan untuk Bicara">
                        <x-phosphor-microphone class="h-7 w-7" aria-hidden="true" />
                    </button>
                    <div class="min-w-0 flex-1">
                        <textarea id="pesan" data-input rows="2" maxlength="1000" class="field-input min-h-[64px] resize-none" placeholder="Tulis pertanyaanmu di sini"></textarea>
                    </div>
                    <button type="submit" data-send class="btn btn-primary shrink-0" disabled>Kirim</button>
                </div>
                <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-fg-muted">
                    <div class="flex items-center gap-2">
                        <button type="button" data-clear class="btn btn-ghost btn-sm hidden">Ulangi Rekaman</button>
                        <span data-hint>Tahan tombol mikrofon untuk bicara, lepas untuk berhenti.</span>
                    </div>
                    <span data-left>@if ($turnsLeft !== null)Sisa {{ $turnsLeft }} giliran hari ini @endif</span>
                </div>
            </form>
        </section>
    </div>

    <x-slot:actions>
        <form method="POST" action="{{ route('boss-room.finish', $session) }}" data-confirm="Akhiri sesi ini? Bos Allo akan menyusun Catatan Konsultasi dari percakapan kalian." data-confirm-ok="Akhiri Sesi" data-finish>
            @csrf
            <x-button variant="secondary" size="sm" loading="Menyusun catatan">Akhiri Sesi</x-button>
        </form>
    </x-slot:actions>

    @push('scripts')
        @vite('resources/js/boss-room.js')
    @endpush
</x-layouts.stage>
