@props(['config', 'label'])
<div {{ $attributes->class('relative h-64') }}>
    <canvas data-chart="{{ json_encode($config) }}" role="img" aria-label="{{ $label }}"></canvas>
</div>
