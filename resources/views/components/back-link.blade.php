@props(['href'])
{{-- Tombol kembali ke daftar induk. Tujuannya tetap, tidak bergantung riwayat peramban yang bisa berasal dari situs lain. --}}
<a href="{{ $href }}" {{ $attributes->class('group inline-flex min-h-[40px] items-center gap-2 rounded-full border border-line bg-surface-raised py-1.5 pl-2 pr-4 text-sm font-medium text-fg transition hover:border-brand/50') }}>
    <span class="grid h-7 w-7 place-items-center rounded-full bg-surface-alt transition group-hover:-translate-x-0.5"><x-phosphor-arrow-left class="h-4 w-4" aria-hidden="true" /></span>
    {{ $slot }}
</a>
