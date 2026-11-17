<x-layouts.dashboard title="Moderasi Lapak">
    <x-page-header title="Moderasi Lapak" description="Periksa lapak sebelum terbit, dan turunkan lapak yang melanggar." />
    <form method="GET" class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <nav class="chip-row" aria-label="Saring status">
            @foreach (App\Enums\ShopStatus::cases() as $case)
                <a href="{{ route('pengelola.shops.index', ['status' => $case->value]) }}" @class(['chip', 'chip-active' => $status === $case->value])>{{ $case->label() }} ({{ $counts[$case->value] ?? 0 }})</a>
            @endforeach
        </nav>
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="flex gap-2"><label for="cari" class="sr-only">Cari nama lapak</label><input id="cari" name="q" value="{{ request('q') }}" class="field-input" placeholder="Cari nama lapak"><x-button variant="secondary">Cari</x-button></div>
    </form>
    @if ($shops->isEmpty())
        <x-empty-state icon="phosphor-storefront" title="Tidak ada lapak dengan status ini" />
    @else
        <x-table>
            <thead><tr><th>Lapak</th><th>Pemilik</th><th>Kapanewon</th><th>Produk</th><th>Laporan</th><th><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
                @foreach ($shops as $shop)
                    <tr>
                        <td data-label="Lapak"><a href="{{ route('shops.show', $shop) }}" target="_blank" class="font-medium text-fg underline-offset-2 hover:underline">{{ $shop->name }}</a> <x-demo-badge :show="$shop->is_demo" /></td>
                        <td data-label="Pemilik">{{ $shop->user->name }}</td>
                        <td data-label="Kapanewon">{{ $shop->district?->name }}</td>
                        <td data-label="Produk">{{ $shop->products_count }}</td>
                        <td data-label="Laporan">{{ $reports[$shop->id] ?? 0 }}</td>
                        <td data-label="Aksi">
                            <div class="flex flex-wrap justify-end gap-2">
                                @if ($shop->status === App\Enums\ShopStatus::Menunggu)
                                    <form method="POST" action="{{ route('pengelola.shops.approve', $shop) }}" data-confirm="Terbitkan lapak {{ $shop->name }}? Lapak langsung bisa dilihat pembeli." data-confirm-ok="Setujui">@csrf<x-button variant="primary" size="sm">Setujui Lapak</x-button></form>
                                    <form method="POST" action="{{ route('pengelola.shops.reject', $shop) }}" data-confirm="Kembalikan lapak ke pemilik untuk diperbaiki." data-confirm-reason="reason" data-confirm-ok="Tolak">@csrf<x-button variant="secondary" size="sm">Tolak</x-button></form>
                                @elseif ($shop->status === App\Enums\ShopStatus::Terbit)
                                    <form method="POST" action="{{ route('pengelola.shops.takedown', $shop) }}" data-confirm="Turunkan lapak {{ $shop->name }}? Pemilik menerima alasanmu." data-confirm-reason="reason" data-confirm-ok="Turunkan">@csrf<x-button variant="danger" size="sm">Turunkan</x-button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-6">{{ $shops->links() }}</div>
    @endif
</x-layouts.dashboard>
