@props(['as' => 'div', 'href' => null, 'tone' => 'plain'])
@php
    $classes = 'card'.($href ? ' card-link block' : '').($tone === 'sorot' ? ' border-brand/60 bg-surface-alt' : '');
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <{{ $as }} {{ $attributes->class($classes) }}>{{ $slot }}</{{ $as }}>
@endif
