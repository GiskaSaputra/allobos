<x-layouts.shop :title="$shop->name" :description="Str::limit($shop->description, 150)">
    @if ($preview)
        <p class="mb-4 rounded-[20px] bg-warning/15 p-4 text-sm text-warning">Pratinjau. Lapak ini berstatus {{ $shop->status->label() }} dan belum bisa dilihat pembeli.</p>
    @endif
    @if (session('status'))<p class="mb-4 rounded-[20px] bg-success/15 p-4 text-sm text-success" role="status">{{ session('status') }}</p>@endif
    @if (session('error'))<p class="mb-4 rounded-[20px] bg-danger/15 p-4 text-sm text-danger" role="alert">{{ session('error') }}</p>@endif

    <x-back-link :href="route('shops.index')" class="mb-5">Semua Lapak</x-back-link>

    <header class="overflow-hidden rounded-[28px] border border-line bg-surface-raised">
        @if ($shop->coverUrl())
            <img src="{{ $shop->coverUrl() }}" alt="" class="aspect-[3/1] w-full object-cover" fetchpriority="high">
        @endif
        <div class="space-y-3 p-5 sm:p-8">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-3xl sm:text-4xl">{{ $shop->name }}</h1>
                <x-demo-badge :show="$shop->is_demo" />
            </div>
            <p class="text-sm text-fg-muted">{{ collect([$shop->district?->name ? 'Kapanewon '.$shop->district->name : null, $shop->field?->name])->filter()->implode(', ') }}</p>
            @if ($badges->isNotEmpty())
                <ul class="flex flex-wrap gap-2" aria-label="Sertifikat terverifikasi pemilik">
                    @foreach ($badges->take(3) as $badge)
                        <li><x-verified-badge :certificate="$badge" /></li>
                    @endforeach
                    @if ($badges->count() > 3)
                        <li><a href="{{ route('certificates.verify.form') }}" class="text-sm text-accent-text underline underline-offset-2">Lihat Semua</a></li>
                    @endif
                </ul>
            @endif
            <p class="max-w-[68ch] whitespace-pre-line text-fg-body">{{ $shop->description }}</p>
            <a href="https://wa.me/{{ $shop->whatsapp }}" class="btn btn-secondary btn-sm w-fit" rel="noopener" target="_blank"><x-phosphor-whatsapp-logo class="h-4 w-4" aria-hidden="true" />Tanya Penjual</a>
        </div>
    </header>

    <section class="mt-8">
        <h2 class="text-2xl">Produk</h2>
        @if ($products->isEmpty())
            <p class="mt-3 text-fg-muted">Belum ada produk yang ditampilkan.</p>
        @else
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)<x-product-card :product="$product" :shop="$shop" />@endforeach
            </div>
        @endif
    </section>

    <x-slot:footer>
        <details class="max-w-md">
            <summary class="cursor-pointer text-sm text-fg-muted underline underline-offset-2">Laporkan Lapak</summary>
            @guest
                <p class="mt-3 text-sm text-fg-body">Masuk dulu untuk melaporkan lapak supaya pengelola bisa menghubungimu bila perlu. <a href="{{ route('login', ['tujuan' => '/lapak/'.$shop->slug]) }}" class="font-medium text-accent-text underline underline-offset-2">Masuk</a></p>
            @else
            <form method="POST" action="{{ route('shops.report', $shop) }}" class="mt-3 space-y-3">
                @csrf
                <label for="alasan-lapor" class="block text-sm font-medium text-fg">Apa yang tidak pantas atau keliru?</label>
                <textarea id="alasan-lapor" name="reason" rows="3" minlength="10" maxlength="500" required class="field-input"></textarea>
                @error('reason')<p class="text-sm text-danger">{{ $message }}</p>@enderror
                <button type="submit" class="btn btn-secondary btn-sm">Kirim Laporan</button>
            </form>
            @endguest
        </details>
    </x-slot:footer>
</x-layouts.shop>
