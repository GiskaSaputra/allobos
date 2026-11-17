@props(['product', 'shop'])
<article class="card flex h-full flex-col gap-3 p-3 sm:p-3">
    <div class="aspect-square overflow-hidden rounded-[20px] bg-surface-alt">
        @if ($product->photoUrl())
            <img src="{{ $product->photoUrl() }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover">
        @else
            <span class="flex h-full items-center justify-center text-fg-muted"><x-phosphor-image class="h-10 w-10" aria-hidden="true" /></span>
        @endif
    </div>
    <div class="flex flex-1 flex-col gap-1 px-1">
        <h3 class="text-base">{{ $product->name }}</h3>
        <p class="font-display text-lg font-bold text-fg">Rp{{ number_format($product->price, 0, ',', '.') }} <span class="text-sm font-normal text-fg-muted">/ {{ $product->unit }}</span></p>
        @if ($product->description)<p class="line-clamp-3 text-sm text-fg-body">{{ $product->description }}</p>@endif
        @unless ($product->in_stock)<p class="text-sm font-medium text-warning">Sedang habis</p>@endunless
    </div>
    @auth
        <form method="POST" action="{{ route('shops.order', [$shop, $product]) }}" class="mt-auto px-1 pb-1" target="_blank">
            @csrf
            <button type="submit" class="btn btn-whatsapp btn-lg btn-full" @disabled(! $product->in_stock)>
                <x-phosphor-whatsapp-logo class="h-5 w-5" aria-hidden="true" />Pesan lewat WhatsApp
            </button>
        </form>
    @else
        <a href="{{ route('login', ['tujuan' => '/lapak/'.$shop->slug]) }}" class="btn btn-secondary btn-lg btn-full mt-auto"><x-phosphor-sign-in class="h-5 w-5" aria-hidden="true" />Masuk untuk Memesan</a>
    @endauth
</article>
