<x-layouts.app title="Lapak Saya">
    <x-page-header title="Lapak Saya" :description="$shop ? 'Status: '.$shop->status->label().'.' : 'Buat katalog online gratis. Pembeli memesan langsung lewat WhatsApp-mu.'">
        <x-slot:actions>
            @if ($shop)
                @if ($shop->status === App\Enums\ShopStatus::Terbit)
                    <button type="button" class="btn btn-secondary btn-sm" data-copy="{{ route('shops.show', $shop) }}"><x-phosphor-link class="h-4 w-4" aria-hidden="true" /><span data-copy-label>Salin Tautan Lapak</span></button>
                @endif
                <x-button variant="ghost" size="sm" :href="route('shops.show', $shop)" target="_blank">Lihat Lapak</x-button>
            @endif
            <x-button variant="ghost" size="sm" :href="route('shops.index')" icon="phosphor-storefront">Jelajahi Lapak Warga</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($shop?->reason && in_array($shop->status, [App\Enums\ShopStatus::Draf, App\Enums\ShopStatus::Diturunkan], true))
        <p class="mb-6 rounded-[20px] bg-danger/10 p-4 text-sm text-fg" role="alert"><strong>Catatan Pengelola IPI:</strong> {{ $shop->reason }}</p>
    @endif

    @if ($shop)
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <x-card><p class="text-sm text-fg-muted">Status</p><p class="mt-1"><x-status-badge :status="$shop->status" /></p></x-card>
            <x-card><p class="text-sm text-fg-muted">Kunjungan</p><p class="font-display text-3xl font-bold text-fg">{{ number_format($stats['visits'], 0, ',', '.') }}</p></x-card>
            <x-card><p class="text-sm text-fg-muted">Klik pesan WhatsApp</p><p class="font-display text-3xl font-bold text-fg">{{ number_format($stats['orders'], 0, ',', '.') }}</p></x-card>
        </div>

        <section class="mb-8">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-2xl">Produk</h2>
                <x-button variant="primary" :href="route('warga.products.create')" icon="phosphor-plus">Tambah Produk</x-button>
            </div>
            @if ($shop->products->isEmpty())
                <x-empty-state icon="phosphor-package" title="Belum ada produk">Tambah produk berfoto supaya lapak bisa diajukan terbit. Kamu juga bisa mengisi dari hasil Cek Produk.</x-empty-state>
            @else
                <ul class="grid gap-3">
                    @foreach ($shop->products as $product)
                        <li class="card flex flex-col gap-3 p-3 sm:flex-row sm:items-center sm:p-3">
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                @if ($product->photoUrl())
                                    <img src="{{ $product->photoUrl() }}" alt="" class="h-16 w-16 shrink-0 rounded-2xl object-cover">
                                @else
                                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-surface-alt text-fg-muted"><x-phosphor-image class="h-6 w-6" aria-hidden="true" /></span>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-fg">{{ $product->name }}</p>
                                    <p class="text-sm text-fg-muted">Rp{{ number_format($product->price, 0, ',', '.') }} / {{ $product->unit }}
                                        @unless ($product->in_stock)<span class="badge badge-warning ml-1">Habis</span>@endunless
                                        @if ($product->hidden)<span class="badge badge-neutral ml-1">Disembunyikan</span>@endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('warga.products.update', $product) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="in_stock" value="{{ $product->in_stock ? 0 : 1 }}">
                                    <x-button variant="secondary" size="sm">{{ $product->in_stock ? 'Tandai Habis' : 'Tandai Tersedia' }}</x-button>
                                </form>
                                <x-button variant="ghost" size="sm" :href="route('warga.products.edit', $product)">Ubah</x-button>
                                <form method="POST" action="{{ route('warga.products.destroy', $product) }}" data-confirm="Hapus {{ $product->name }} dari lapak?" data-confirm-ok="Hapus Produk">
                                    @csrf @method('DELETE')
                                    <x-button variant="danger" size="sm">Hapus Produk</x-button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="mb-8 flex flex-wrap items-start gap-3">
            @if ($shop->status === App\Enums\ShopStatus::Draf)
                @php($ready = $shop->products->whereNotNull('photo_path')->isNotEmpty())
                <div>
                    <form method="POST" action="{{ route('warga.shop.submit') }}">
                        @csrf
                        <x-button variant="primary" :disabled="! $ready" loading="Mengajukan">Ajukan Terbit</x-button>
                    </form>
                    @unless ($ready)<p class="mt-2 text-sm text-fg-muted">Tambahkan minimal satu produk berfoto untuk mengajukan.</p>@endunless
                </div>
            @elseif ($shop->status === App\Enums\ShopStatus::Menunggu)
                <p class="text-sm text-fg-muted">Lapak sedang diperiksa Pengelola IPI. Kamu akan diberi tahu lewat notifikasi.</p>
            @elseif (in_array($shop->status, [App\Enums\ShopStatus::Terbit, App\Enums\ShopStatus::Tutup], true))
                <form method="POST" action="{{ route('warga.shop.toggle') }}">
                    @csrf
                    <x-button variant="secondary">{{ $shop->status === App\Enums\ShopStatus::Terbit ? 'Tutup Sementara' : 'Buka Kembali' }}</x-button>
                </form>
            @endif
        </section>
    @endif

    <section class="max-w-2xl">
        <h2 class="mb-4 text-2xl">{{ $shop ? 'Data lapak' : 'Buat lapak' }}</h2>
        <form method="POST" action="{{ $shop ? route('warga.shop.update') : route('warga.shop.store') }}" enctype="multipart/form-data" class="card space-y-5">
            @csrf
            @if ($shop) @method('PATCH') @endif
            <x-field label="Nama lapak" name="name" required><x-text-input name="name" :value="$shop?->name" maxlength="80" required placeholder="Contoh: Keripik Mbok Sri" /></x-field>
            <x-field label="Deskripsi" name="description" hint="Ceritakan produkmu, asal bahan, dan cara pesan." required><x-textarea name="description" :value="$shop?->description" rows="4" maxlength="600" required /></x-field>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="Kapanewon" name="district_id" required><x-select name="district_id" :options="$districts" :value="$shop?->district_id ?? $defaults['district_id']" placeholder="Pilih kapanewon" required /></x-field>
                <x-field label="Bidang usaha" name="business_field_id"><x-select name="business_field_id" :options="$fields" :value="$shop?->business_field_id ?? $defaults['business_field_id']" placeholder="Pilih bidang" /></x-field>
            </div>
            <x-field label="Nomor WhatsApp penjual" name="whatsapp" hint="Pesanan pembeli masuk ke nomor ini." required><x-text-input name="whatsapp" type="tel" :value="$shop?->whatsapp ?? $defaults['whatsapp']" required /></x-field>
            <x-field label="Foto sampul" name="cover" hint="JPG, PNG, atau WEBP. Maksimal 15 MB. Foto dikecilkan otomatis.">
                <input id="f-cover" type="file" name="cover" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-fg-body file:mr-3 file:rounded-full file:border-0 file:bg-brand file:px-4 file:py-2 file:text-brand-fg">
            </x-field>
            <x-button variant="primary" loading="Menyimpan">Simpan Lapak</x-button>
        </form>
    </section>

    @if ($others->isNotEmpty())
        <section class="mt-12">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl">Lapak warga lain</h2>
                    <p class="mt-1 text-sm text-fg-muted">Lihat cara warga lain menata lapak dan produknya. Pesan langsung lewat WhatsApp penjual.</p>
                </div>
                <x-button variant="secondary" size="sm" :href="route('shops.index')" icon-right="phosphor-arrow-right">Lihat Semua Lapak</x-button>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($others as $other)<x-shop-card :shop="$other" />@endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
