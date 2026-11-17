<x-layouts.stage :title="'Kode kehadiran '.$session->title" :exit="route('pengelola.programs.show', $session->program)">
    <div class="bg-hero flex h-full flex-col items-center justify-center gap-6 p-6 text-center">
        <p class="rounded-full bg-ink/45 px-5 py-2 text-ink-fg backdrop-blur-md">{{ $session->program->title }}, {{ $session->title }}</p>
        <p class="font-display text-[clamp(56px,16vw,180px)] font-bold leading-none tracking-[0.12em] text-ink" aria-label="Kode kehadiran {{ implode(' ', str_split($code)) }}">{{ $code }}</p>
        <p class="max-w-md rounded-[20px] bg-ink/45 px-5 py-3 text-ink-fg backdrop-blur-md">Buka alloBos, masuk ke Kelas Saya, lalu ketik kode ini. Berlaku sampai {{ $session->ends_at->format('H:i') }} WIB.</p>
    </div>
</x-layouts.stage>
