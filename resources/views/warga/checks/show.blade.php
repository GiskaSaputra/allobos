@php($r = $check->result)
@php($rp = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.'))
<x-layouts.app title="Hasil Cek Produk">
    @if ($check->status === App\Enums\CheckStatus::Diperiksa)
        @push('head')<meta http-equiv="refresh" content="4">@endpush
    @endif
    <x-page-header :title="$check->name ?? 'Cek Produk'" :description="'Diunggah '.$check->created_at->translatedFormat('d F Y, H:i').' WIB.'">
        <x-slot:actions><x-button variant="ghost" :href="route('warga.checks.index')" icon="phosphor-arrow-left">Semua Cek Produk</x-button></x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,420px)_1fr]">
        <div class="space-y-4">
            <img src="{{ $check->photoUrl() }}" alt="Foto produk yang diperiksa" class="w-full rounded-card border border-line object-cover">
            @if ($check->status === App\Enums\CheckStatus::Selesai)
                <form method="POST" action="{{ route('warga.checks.update', $check) }}" class="card space-y-3" x-data="{ edit: false }">
                    @csrf @method('PATCH')
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm text-fg-muted">Dikenali sebagai <strong class="text-fg">{{ $check->name }}</strong>, kategori {{ $r['category'] }}</p>
                        <button type="button" class="btn btn-ghost btn-sm" @click="edit = !edit" x-show="!edit">Perbaiki Nama Produk</button>
                    </div>
                    <div x-show="edit" x-cloak class="flex gap-2">
                        <label for="nama-produk" class="sr-only">Nama produk yang benar</label>
                        <input id="nama-produk" name="name" value="{{ $check->name }}" maxlength="120" class="field-input" required>
                        <x-button variant="primary" size="sm">Simpan</x-button>
                    </div>
                </form>
            @endif
        </div>

        <div class="space-y-4">
            @if ($check->status === App\Enums\CheckStatus::Diperiksa)
                <p class="font-medium text-fg" role="status">Foto sedang diperiksa. Halaman ini memuat ulang sendiri.</p>
                <div class="skeleton h-28"></div><div class="skeleton h-40"></div><div class="skeleton h-32"></div>
            @elseif ($check->status === App\Enums\CheckStatus::Gagal)
                <x-card class="border-danger/40">
                    <h2 class="text-xl">Foto belum bisa diperiksa</h2>
                    <p class="mt-2 text-fg-body">{{ $check->error }}</p>
                    <x-button variant="primary" :href="route('warga.checks.index')" class="mt-4">Unggah Foto Lagi</x-button>
                </x-card>
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach (['photo_score' => 'Mutu foto', 'pack_score' => 'Kemasan'] as $key => $label)
                        <x-card>
                            <p class="text-sm text-fg-muted">{{ $label }}</p>
                            <p class="font-display text-3xl font-bold text-fg">{{ $r[$key] }}<span class="text-lg text-fg-muted"> dari 4</span></p>
                        </x-card>
                    @endforeach
                </div>
                <x-card>
                    <h2 class="text-xl">Yang sudah baik</h2>
                    <ul class="mt-2 space-y-1.5">@foreach ($r['good'] as $item)<li class="flex gap-2 text-fg-body"><x-phosphor-check-circle class="mt-0.5 h-5 w-5 shrink-0 text-success" aria-hidden="true" />{{ $item }}</li>@endforeach</ul>
                    <h2 class="mt-5 text-xl">Perbaikan</h2>
                    <ul class="mt-2 space-y-1.5">@foreach ($r['fixes'] as $item)<li class="flex gap-2 text-fg-body"><x-phosphor-wrench class="mt-0.5 h-5 w-5 shrink-0 text-warning" aria-hidden="true" />{{ $item }}</li>@endforeach</ul>
                </x-card>
                <x-card>
                    <div class="flex flex-wrap items-center justify-between gap-2"><h2 class="text-xl">Saran harga jual</h2><x-ai-badge /></div>
                    <p class="mt-2 font-display text-3xl font-bold text-fg">{{ $rp($r['price_min']) }} sampai {{ $rp($r['price_max']) }}</p>
                    <p class="mt-1 text-fg-body">{{ $r['price_reason'] }}</p>
                    @if ($reference)
                        <p class="mt-3 rounded-[20px] bg-surface-alt p-3 text-sm text-fg-body">Pembanding dari Rencana Usaha: HPP {{ $rp($reference['hpp']) }}, harga rencanamu {{ $rp($reference['price']) }}.
                            @if ($r['price_min'] < $reference['hpp']) Batas bawah saran AI di bawah HPP-mu, jadi jangan menjual di bawah {{ $rp($reference['hpp']) }}. @endif
                        </p>
                    @endif
                </x-card>
                <x-card>
                    @php($signal = App\Enums\MarketSignal::from($r['signal']))
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-xl">Sinyal Pasar</h2><x-ai-badge />
                    </div>
                    <p class="mt-2 flex items-center gap-2 font-display text-2xl font-bold text-fg"><x-dynamic-component :component="$signal->icon()" class="h-7 w-7" aria-hidden="true" />{{ $signal->label() }}</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-fg-body">@foreach ($r['reasons'] as $reason)<li>{{ $reason }}</li>@endforeach</ul>
                    @if (! empty($r['sources']))
                        <p class="mt-3 text-sm font-medium text-fg">Sumber</p>
                        <ul class="mt-1 space-y-1 text-sm">@foreach ($r['sources'] as $source)<li><a href="{{ $source['url'] }}" target="_blank" rel="noopener nofollow" class="text-accent-text underline underline-offset-2">{{ $source['title'] ?: $source['url'] }}</a></li>@endforeach</ul>
                    @else
                        <p class="mt-3 text-sm text-fg-muted">Perkiraan dari pengetahuan umum AI, bukan data penjualan. Penelusuran web sedang tidak aktif.</p>
                    @endif
                </x-card>
                <x-card tone="sorot">
                    <h2 class="text-xl">Deskripsi siap pakai</h2>
                    <p class="mt-2 text-fg-body">{{ $r['description'] }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-copy="{{ $r['description'] }}"><x-phosphor-copy class="h-4 w-4" aria-hidden="true" /><span data-copy-label>Salin Deskripsi</span></button>
                        @if ($hasShop)
                            <x-button variant="primary" :href="route('warga.products.create', ['cek' => $check->id])">Tambahkan ke Lapak</x-button>
                        @else
                            <x-button variant="primary" :href="route('warga.shop.edit')">Buat Lapak Dulu</x-button>
                        @endif
                    </div>
                </x-card>
            @endif
        </div>
    </div>
</x-layouts.app>
