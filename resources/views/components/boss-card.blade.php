@props(['boss', 'href'])
<a href="{{ $href }}" class="card card-link group relative isolate flex h-full flex-col gap-4 overflow-hidden">
    <x-logo-mark class="-right-5 -top-5 h-24 w-24" />
    <span class="flex items-center gap-4">
        @if ($boss->photo_path)
            <img src="{{ Storage::disk('public')->url($boss->photo_path) }}" alt="" class="h-16 w-16 rounded-full object-cover">
        @else
            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-brand font-display text-2xl font-bold text-brand-fg">{{ mb_substr($boss->user->name, 0, 1) }}</span>
        @endif
        <span class="min-w-0">
            <span class="block font-display text-lg font-semibold text-fg">{{ $boss->user->name }}</span>
            <span class="block truncate text-sm text-fg-muted">{{ $boss->business_name }}</span>
        </span>
    </span>
    <span class="flex flex-wrap items-center gap-2 text-sm text-fg-body">
        @if ($boss->field)<x-badge>{{ $boss->field->name }}</x-badge>@endif
        @if ($boss->district)<span>{{ $boss->district->name }}</span>@endif
        <span class="flex items-center gap-1"><x-phosphor-star-fill class="h-4 w-4 text-warning" aria-hidden="true" />{{ $boss->rating_count ? number_format($boss->rating_avg, 1, ',', '') : 'Baru' }}<span class="sr-only"> dari 5</span></span>
        <x-demo-badge :show="$boss->is_demo" />
    </span>
    {{ $slot }}
</a>
