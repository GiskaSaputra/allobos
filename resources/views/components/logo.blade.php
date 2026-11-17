@props(['onDark' => false])
<span class="inline-flex items-center gap-2">
    <span @class(['font-display text-xl font-bold tracking-tight', 'text-ink-fg' => $onDark, 'text-fg' => ! $onDark])>allo<span @class(['text-accent' => $onDark, 'text-accent-text' => ! $onDark])>Bos</span></span>
    @if (config('features.demo'))
        <span class="badge badge-warning">Demo</span>
    @endif
</span>
