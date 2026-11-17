@php
    $prefill = $check ? ['name' => $check->name, 'description' => $check->result['description'] ?? '', 'price' => $check->suggestedPrice()] : [];
@endphp
<x-layouts.app :title="$product->exists ? 'Ubah Produk' : 'Tambah Produk'">
    <x-page-header :title="$product->exists ? 'Ubah produk' : 'Tambah produk'">
        <x-slot:actions><x-button variant="ghost" :href="route('warga.shop.edit')" icon="phosphor-arrow-left">Kembali ke Lapak</x-button></x-slot:actions>
    </x-page-header>

    @if (! $product->exists && $checks->isNotEmpty())
        <details class="mb-6 max-w-2xl" @if ($check) open @endif>
            <summary class="btn btn-secondary btn-sm w-fit cursor-pointer list-none">Isi dari Cek Produk</summary>
            <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                @foreach ($checks as $item)
                    <li><a href="{{ route('warga.products.create', ['cek' => $item->id]) }}" @class(['card card-link flex items-center gap-3 p-3 sm:p-3', 'border-brand' => $check?->id === $item->id])>
                        <img src="{{ $item->photoUrl() }}" alt="" class="h-12 w-12 rounded-xl object-cover"><span class="truncate text-sm font-medium text-fg">{{ $item->name }}</span>
                    </a></li>
                @endforeach
            </ul>
        </details>
    @endif

    <form method="POST" action="{{ $product->exists ? route('warga.products.update', $product) : route('warga.products.store') }}" enctype="multipart/form-data" class="card max-w-2xl space-y-5">
        @csrf
        @if ($product->exists) @method('PATCH') @endif
        @if ($check)
            <input type="hidden" name="product_check_id" value="{{ $check->id }}">
            <div class="flex items-center gap-3 rounded-[20px] bg-info/10 p-3 text-sm text-fg">
                <img src="{{ $check->photoUrl() }}" alt="" class="h-14 w-14 rounded-xl object-cover">
                <p>Nama, deskripsi, foto, dan saran harga diisi dari Cek Produk. Harga adalah <strong>perkiraan AI</strong>, sesuaikan dengan hitungan HPP-mu.</p>
            </div>
        @endif
        <x-field label="Nama produk" name="name" required><x-text-input name="name" :value="$product->name ?? ($prefill['name'] ?? null)" maxlength="80" required /></x-field>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-field label="Harga (rupiah)" name="price" required><x-text-input name="price" type="number" min="100" step="100" inputmode="numeric" :value="$product->price ?? ($prefill['price'] ?? null)" required /></x-field>
            <x-field label="Satuan" name="unit" hint="Contoh: bungkus, kg, buah" required><x-text-input name="unit" :value="$product->unit" maxlength="30" required /></x-field>
        </div>
        <x-field label="Deskripsi" name="description"><x-textarea name="description" :value="$product->description ?? ($prefill['description'] ?? null)" rows="4" maxlength="600" /></x-field>
        <x-field label="Foto produk" :hint="$check ? 'Kosongkan untuk memakai foto dari Cek Produk.' : 'JPG, PNG, atau WEBP. Maksimal 5 MB.'" name="photo">
            @if ($product->photoUrl())<img src="{{ $product->photoUrl() }}" alt="Foto sekarang" class="mb-2 h-24 w-24 rounded-2xl object-cover">@endif
            <input id="f-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-fg-body file:mr-3 file:rounded-full file:border-0 file:bg-brand file:px-4 file:py-2 file:text-brand-fg">
        </x-field>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-field label="Urutan tampil" name="sort" hint="Angka kecil tampil lebih dulu."><x-text-input name="sort" type="number" min="0" max="999" :value="$product->sort" /></x-field>
            <label class="flex items-center gap-3 self-center text-sm text-fg-body"><input type="hidden" name="hidden" value="0"><input type="checkbox" name="hidden" value="1" class="field-check" @checked(old('hidden', $product->hidden))>Sembunyikan dari lapak</label>
        </div>
        <x-button variant="primary" loading="Menyimpan">Simpan Produk</x-button>
    </form>
</x-layouts.app>
