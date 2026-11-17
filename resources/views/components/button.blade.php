@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'icon' => null, 'iconRight' => null, 'full' => false, 'loading' => null])
@php
    $classes = trim('btn btn-'.$variant.' '.match ($size) { 'sm' => 'btn-sm', 'lg' => 'btn-lg', 'xl' => 'btn-xl', default => '' }.($full ? ' btn-full' : ''));
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-dynamic-component :component="$icon" class="h-5 w-5 shrink-0" aria-hidden="true" />@endif
        {{ $slot }}
        @if ($iconRight)<x-dynamic-component :component="$iconRight" class="h-5 w-5 shrink-0" aria-hidden="true" />@endif
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit'])->class($classes) }} @if ($loading) data-loading="{{ $loading }}" @endif>
        @if ($icon)<x-dynamic-component :component="$icon" class="h-5 w-5 shrink-0" aria-hidden="true" />@endif
        <span data-label>{{ $slot }}</span>
        @if ($iconRight)<x-dynamic-component :component="$iconRight" class="h-5 w-5 shrink-0" aria-hidden="true" />@endif
    </button>
@endif
