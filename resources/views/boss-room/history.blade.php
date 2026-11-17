<x-layouts.app title="Riwayat Ruang Bos">
    <x-page-header title="Riwayat Ruang Bos" description="Semua sesi konsultasi dengan Bos Allo.">
        <x-slot:actions><x-button variant="primary" :href="route('boss-room.setup')">Mulai Sesi Baru</x-button></x-slot:actions>
    </x-page-header>
    @if ($sessions->isEmpty())
        <x-empty-state icon="phosphor-chat-circle-dots" title="Belum ada sesi" action="Bicara dengan Bos Allo" :href="route('boss-room.setup')">Sesi pertamamu akan tercatat di sini beserta catatannya.</x-empty-state>
    @else
        <x-table>
            <thead><tr><th>Topik</th><th>Tanggal</th><th>Giliran</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
                @foreach ($sessions as $item)
                    <tr>
                        <td data-label="Topik" class="font-medium text-fg">{{ $item->topic }}</td>
                        <td data-label="Tanggal">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</td>
                        <td data-label="Giliran">{{ $item->turns_count }}</td>
                        <td data-label="Status"><x-status-badge :status="$item->status" /></td>
                        <td data-label="Aksi"><a href="{{ $item->note ? route('boss-room.note', $item) : route('boss-room.show', $item) }}" class="btn btn-secondary btn-sm">{{ $item->note ? 'Buka Catatan' : 'Lanjutkan Sesi' }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-6">{{ $sessions->links() }}</div>
    @endif
</x-layouts.app>
