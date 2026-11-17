@props(['title', 'description' => null])
<div {{ $attributes->class('mb-6 flex flex-col gap-4 sm:mb-8 sm:flex-row sm:items-end sm:justify-between') }}>
    <div class="space-y-1">
        <h1>{{ $title }}</h1>
        @if ($description)<p class="max-w-[68ch] text-fg-muted">{{ $description }}</p>@endif
    </div>
    @if (isset($actions))<div class="flex flex-wrap gap-2">{{ $actions }}</div>@endif
</div>
