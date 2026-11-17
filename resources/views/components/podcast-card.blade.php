@props(['podcast', 'compact' => false, 'href' => null])
<a href="{{ $href ?? route('podcasts.show', $podcast) }}" class="card card-link group flex flex-col gap-3 p-3 sm:p-3">
    <span class="relative block overflow-hidden rounded-[20px]">
        <img src="{{ $podcast->thumbnail() }}" alt="" loading="lazy" width="480" height="270" class="aspect-video w-full object-cover transition duration-300 group-hover:scale-[1.03]">
        <x-logo-chip class="absolute left-2 top-2" />
        <span class="absolute bottom-2 right-2 flex h-10 w-10 items-center justify-center rounded-full bg-ink/70 text-ink-fg"><x-phosphor-play-fill class="h-5 w-5" aria-hidden="true" /></span>
    </span>
    <span class="space-y-1 px-1 pb-1">
        <span class="line-clamp-2 block font-display font-semibold leading-snug text-fg">{{ $podcast->title }}</span>
        <span class="flex flex-wrap items-center gap-2 text-xs text-fg-muted">
            @if ($podcast->field)<span>{{ $podcast->field->name }}</span>@endif
            @if ($podcast->durationLabel())<span>{{ $podcast->durationLabel() }}</span>@endif
            <x-demo-badge :show="$podcast->is_demo" />
        </span>
        @unless ($compact)
            @if ($podcast->summary)<span class="line-clamp-2 block text-sm text-fg-body">{{ $podcast->summary }}</span>@endif
        @endunless
    </span>
</a>
