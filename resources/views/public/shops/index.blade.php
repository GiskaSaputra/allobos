<x-layouts.public title="Direktori Lapak" description="Lapak usaha warga Gunungkidul. Pesan langsung ke penjual lewat WhatsApp.">
    <section class="container-page pt-6">
        <h1 class="text-4xl sm:text-5xl">Lapak warga</h1>
        <p class="mt-3 max-w-xl text-fg-muted">Produk buatan warga Gunungkidul. Pesan langsung ke penjual lewat WhatsApp, pembayaran diatur bersama penjual.</p>

        <form method="GET" class="mt-8 grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
            <x-field label="Kapanewon" name="kapanewon">
                <x-select name="kapanewon" :options="$districts->pluck('name', 'slug')" :value="request('kapanewon')" placeholder="Semua kapanewon" />
            </x-field>
            <x-field label="Bidang" name="bidang">
                <x-select name="bidang" :options="$fields->pluck('name', 'slug')" :value="request('bidang')" placeholder="Semua bidang" />
            </x-field>
            <x-button variant="secondary" icon="phosphor-funnel">Saring</x-button>
        </form>

        @if ($shops->isEmpty())
            <x-empty-state icon="phosphor-storefront" title="Belum ada lapak di pilihan ini" class="mt-8">Coba pilih kapanewon atau bidang lain.</x-empty-state>
        @else
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($shops as $shop)<x-shop-card :shop="$shop" />@endforeach
            </div>
            <div class="mt-8">{{ $shops->links() }}</div>
        @endif
    </section>
</x-layouts.public>
