@props(['onDark' => false, 'size' => 'h-9'])
<span class="inline-flex items-center gap-2">
    @if ($onDark)
        <img src="{{ asset('image/logo-allobos-terang.webp') }}" alt="alloBos" width="480" height="175" class="{{ $size }} w-auto">
    @else
        <img src="{{ asset('image/logo-allobos.webp') }}" alt="alloBos" width="480" height="175" class="{{ $size }} w-auto dark:hidden">
        <img src="{{ asset('image/logo-allobos-terang.webp') }}" alt="alloBos" width="480" height="175" class="{{ $size }} hidden w-auto dark:block">
    @endif
    @if (config('features.demo'))
        <span class="badge badge-warning">Demo</span>
    @endif
</span>
