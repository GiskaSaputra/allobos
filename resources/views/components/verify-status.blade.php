@props(['status'])
@php
    $map = [
        'asli' => ['success', 'phosphor-seal-check', 'Asli', 'Sertifikat ini asli, belum diubah, dan belum dicabut.'],
        'dicabut' => ['danger', 'phosphor-prohibit', 'Dicabut', 'Sertifikat ini pernah terbit tetapi sudah dicabut penyelenggara.'],
        'belum_tercatat' => ['warning', 'phosphor-hourglass-medium', 'Belum Tercatat', 'Sertifikat sudah ditandatangani dan sedang dicatat ke blockchain.'],
        'tidak_cocok' => ['danger', 'phosphor-warning-octagon', 'Tidak Cocok', 'Data di server berbeda dengan catatan blockchain. Jangan percaya sertifikat ini sebelum dikonfirmasi penyelenggara.'],
        'tidak_ditemukan' => ['neutral', 'phosphor-magnifying-glass', 'Tidak Ditemukan', 'Kode ini tidak dikenal. Periksa ulang kode di sertifikat, huruf dan angkanya.'],
    ];
    [$tone, $icon, $label, $text] = $map[$status];
@endphp
<div {{ $attributes->class(['flex flex-col gap-4 rounded-[28px] border p-6 sm:flex-row sm:items-center sm:p-8',
    'border-success/40 bg-success/10' => $tone === 'success',
    'border-danger/40 bg-danger/10' => $tone === 'danger',
    'border-warning/40 bg-warning/10' => $tone === 'warning',
    'border-line bg-surface-raised' => $tone === 'neutral']) }} role="status">
    <span @class(['flex h-16 w-16 shrink-0 items-center justify-center rounded-full',
        'bg-success text-status-fg' => $tone === 'success',
        'bg-danger text-status-fg' => $tone === 'danger',
        'bg-warning text-status-fg' => $tone === 'warning',
        'bg-fg/10 text-fg' => $tone === 'neutral'])>
        <x-dynamic-component :component="$icon" class="h-8 w-8" aria-hidden="true" />
    </span>
    <div>
        <p class="font-display text-3xl font-bold text-fg">{{ $label }}</p>
        <p class="mt-1 text-fg-body">{{ $text }}</p>
    </div>
</div>
