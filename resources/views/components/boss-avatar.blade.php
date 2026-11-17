@props(['clip' => 'diam'])
<figure {{ $attributes->class('relative overflow-hidden rounded-card border border-line bg-surface-alt shadow-card') }}>
    <video data-clip="{{ asset("gif/bos-allo/allo-{$clip}.mp4") }}" poster="{{ asset('gif/bos-allo/allo-transparan.webp') }}" width="400" height="226" muted loop playsinline preload="none" aria-label="Bos Allo" class="block aspect-video w-full object-cover"></video>
</figure>
