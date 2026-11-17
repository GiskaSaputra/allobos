@props(['shop'])
<a href="{{ route('shops.show', $shop) }}" class="card card-link group flex flex-col gap-3 overflow-hidden p-0 sm:p-0">
    <span class="block aspect-[16/9] overflow-hidden bg-surface-alt">
        @if ($shop->coverUrl())
            <img src="{{ $shop->coverUrl() }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
        @else
            <span class="bg-hero flex h-full w-full items-center justify-center"><x-phosphor-storefront class="h-12 w-12 text-ink/60" aria-hidden="true" /></span>
        @endif
    </span>
    <span class="space-y-1 px-5 pb-5">
        <span class="block font-display text-lg font-semibold text-fg">{{ $shop->name }}</span>
        <span class="flex flex-wrap items-center gap-2 text-sm text-fg-muted">
            @if ($shop->district)<span>{{ $shop->district->name }}</span>@endif
            @if ($shop->field)<span>{{ $shop->field->name }}</span>@endif
            <span>{{ $shop->products_count }} produk</span>
            <x-demo-badge :show="$shop->is_demo" />
        </span>
    </span>
</a>
