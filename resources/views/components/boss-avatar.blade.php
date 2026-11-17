@props(['state' => 'idle', 'size' => 'md'])
<figure {{ $attributes->class('relative overflow-hidden rounded-card border border-line bg-surface-alt shadow-card') }}>
    <img src="{{ asset('gif/bos-allo/allo-diam.gif') }}" alt="Bos Allo" width="400" height="225" class="block aspect-video w-full object-cover motion-reduce:hidden" data-avatar>
    <img src="{{ asset('gif/bos-allo/allo-transparan.webp') }}" alt="Bos Allo" width="400" height="225" class="hidden aspect-video w-full object-cover motion-reduce:block">
</figure>
