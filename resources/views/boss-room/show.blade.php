@php
    $config = [
        'startUrl' => route('boss-room.start', $session),
        'sendUrl' => route('boss-room.send', $session),
        'voiceUrl' => route('boss-room.voice', $session),
        'status' => $session->status->value,
        'mode' => $session->mode,
        'turns' => $turns,
        'turnsLeft' => $turnsLeft,
        'clipIdle' => asset('gif/bos-allo/allo-diam.mp4'),
        'clipTalk' => asset('gif/bos-allo/allo-bicara.mp4'),
        'mentoringUrl' => Route::has('warga.mentoring.index') ? route('warga.mentoring.index') : null,
    ];
@endphp
<x-layouts.stage title="Ruang Bos: {{ $session->topic }}" :exit="route('warga.dashboard')" confirm-exit="Sesi masih berjalan. Keluar sekarang? Kamu bisa kembali ke sesi ini dari Riwayat, atau tekan Akhiri Sesi untuk mendapat catatan.">
    <div data-boss-room data-config="{{ json_encode($config) }}" data-state="idle" class="grid h-full grid-rows-[auto_1fr] lg:grid-cols-[1.35fr_1fr] lg:grid-rows-1">
        {{-- Bos Allo tampil besar seperti panggilan video, percakapan di sampingnya --}}
        <section class="flex min-h-0 flex-col gap-3 border-b border-line p-3 sm:p-4 lg:border-b-0 lg:border-r">
            <figure data-avatar-frame class="avatar-frame relative h-[40dvh] overflow-hidden rounded-card border border-line bg-ink shadow-card lg:h-auto lg:min-h-0 lg:flex-1">
                <video data-avatar poster="{{ asset('gif/bos-allo/allo-transparan.webp') }}" width="1280" height="720" muted loop playsinline preload="none" aria-label="Bos Allo" class="absolute inset-0 h-full w-full object-cover"></video>
                {{-- Peramban menahan suara sebelum ada ketukan pertama, jadi sapaan diputar lewat tombol ini --}}
                <button type="button" data-unlock class="btn btn-accent btn-lg absolute left-1/2 top-1/2 z-10 hidden -translate-x-1/2 -translate-y-1/2 shadow-float">
                    <x-phosphor-speaker-high class="h-5 w-5" aria-hidden="true" />Dengarkan Bos Allo
                </button>
                <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-2 bg-gradient-to-b from-ink/70 to-transparent p-3 sm:p-4">
                    <div class="min-w-0">
                        <p class="flex items-center gap-2 text-sm font-semibold text-ink-fg"><span class="h-2 w-2 rounded-full bg-success" aria-hidden="true"></span>Bos Allo</p>
                        <p class="truncate text-xs text-ink-muted">{{ $session->topic }}</p>
                    </div>
                    <span data-wave class="voice-wave" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></span>
                </div>
                <div class="absolute inset-x-0 bottom-0 flex flex-wrap items-center justify-between gap-2 bg-gradient-to-t from-ink/75 to-transparent p-3 sm:p-4">
                    <p data-status class="rounded-full bg-ink/65 px-3 py-1.5 text-xs font-medium text-ink-fg backdrop-blur-md" role="status" aria-live="polite">Bos Allo siap mendengar</p>
                    <div class="flex gap-1.5">
                        <button type="button" data-replay class="btn btn-glass btn-sm" disabled>Ulangi Jawaban</button>
                        <button type="button" data-mute class="btn btn-glass btn-icon btn-sm" aria-pressed="false" aria-label="Bisukan suara Bos Allo">
                            <x-phosphor-speaker-high class="h-5 w-5" aria-hidden="true" data-icon-on />
                            <x-phosphor-speaker-slash class="hidden h-5 w-5" aria-hidden="true" data-icon-off />
                        </button>
                    </div>
                </div>
            </figure>
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
                        <span data-hint>Tahan tombol mikrofon untuk bicara, lepas untuk langsung mengirim.</span>
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
