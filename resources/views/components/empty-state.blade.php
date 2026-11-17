@props(['icon' => 'phosphor-leaf', 'title', 'action' => null, 'href' => null])
<div {{ $attributes->class('flex flex-col items-start gap-4 rounded-card border border-dashed border-line p-6 sm:p-8') }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-accent/30 text-fg"><x-dynamic-component :component="$icon" class="h-6 w-6" aria-hidden="true" /></span>
    <div class="space-y-1">
        <p class="font-display text-lg font-semibold text-fg">{{ $title }}</p>
        @if (! $slot->isEmpty())<p class="max-w-prose text-fg-muted">{{ $slot }}</p>@endif
    </div>
    @if ($action && $href)<x-button :href="$href" variant="primary">{{ $action }}</x-button>@endif
    {{ $actions ?? '' }}
</div>
