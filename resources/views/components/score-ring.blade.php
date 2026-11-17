@props(['score', 'level', 'breakdown' => null, 'size' => 'lg'])
@php($c = 2 * M_PI * 52)
<div {{ $attributes->class('flex flex-col items-center gap-3') }}>
    <div @class(['relative', 'h-44 w-44' => $size === 'lg', 'h-28 w-28' => $size !== 'lg'])>
        <svg viewBox="0 0 120 120" class="h-full w-full -rotate-90" aria-hidden="true">
            <circle cx="60" cy="60" r="52" fill="none" stroke-width="10" class="stroke-line" />
            <circle cx="60" cy="60" r="52" fill="none" stroke-width="10" stroke-linecap="round" class="stroke-accent-text transition-[stroke-dashoffset] duration-700 ease-out"
                    stroke-dasharray="{{ $c }}" stroke-dashoffset="{{ $c * (1 - $score / 100) }}" />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center" role="img" aria-label="Skor Siap Usaha {{ $score }} dari 100, tingkat {{ $level }}">
            <span @class(['font-display font-bold leading-none text-fg', 'text-5xl' => $size === 'lg', 'text-3xl' => $size !== 'lg'])>{{ $score }}</span>
            <span class="mt-1 text-xs text-fg-muted">dari 100</span>
        </div>
    </div>
    <span class="badge badge-brand">{{ $level }}</span>
    @if ($breakdown)
        <details class="w-full">
            <summary class="btn btn-ghost btn-sm mx-auto w-fit cursor-pointer list-none">Lihat Rincian Skor</summary>
            <ul class="mt-3 space-y-3">
                @foreach ($breakdown as $part)
                    <li>
                        <div class="flex justify-between gap-2 text-sm"><span class="font-medium text-fg">{{ $part['label'] }}</span><span class="text-fg-muted">{{ rtrim(rtrim(number_format($part['points'], 1, ',', ''), '0'), ',') }} / {{ $part['weight'] }}</span></div>
                        <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-line"><div class="h-full rounded-full bg-accent-text" style="width: {{ round($part['fraction'] * 100) }}%"></div></div>
                        <p class="mt-1 text-xs text-fg-muted">{{ $part['detail'] }}</p>
                    </li>
                @endforeach
            </ul>
        </details>
    @endif
</div>
