@props(['onDark' => false, 'size' => 'h-9', 'compact' => false])
{{-- compact: di ponsel hanya ikon, untuk kepala halaman yang sempit --}}
<span class="inline-flex shrink-0 items-center gap-2">
    @if ($compact)
        <img src="{{ asset('image/ikon-allobos.webp') }}" alt="alloBos" width="160" height="160" class="h-8 w-8 sm:hidden">
    @endif
    @if ($onDark)
        <img src="{{ asset('image/logo-allobos-terang.webp') }}" alt="alloBos" width="480" height="175" @class([$size, 'w-auto', 'hidden sm:block' => $compact])>
    @else
        <img src="{{ asset('image/logo-allobos.webp') }}" alt="alloBos" width="480" height="175" @class([$size, 'w-auto dark:hidden', 'hidden sm:block' => $compact])>
        <img src="{{ asset('image/logo-allobos-terang.webp') }}" alt="alloBos" width="480" height="175" @class([$size, 'hidden w-auto', 'dark:block' => ! $compact, 'sm:dark:block' => $compact])>
    @endif
    @if (config('features.demo'))
        <span @class(['badge badge-warning', 'hidden sm:inline-flex' => $compact])>Demo</span>
    @endif
</span>
