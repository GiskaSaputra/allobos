<x-layouts.dashboard title="Pengumuman">
    <x-page-header title="Pengumuman" description="Tampil di dashboard warga atau Bos.">
        <x-slot:actions><x-button variant="primary" :href="route('pengelola.announcements.create')" icon="phosphor-plus">Buat Pengumuman</x-button></x-slot:actions>
    </x-page-header>
    @if ($announcements->isEmpty())
        <x-empty-state icon="phosphor-megaphone" title="Belum ada pengumuman" />
    @else
        <x-table>
            <thead><tr><th>Judul</th><th>Untuk</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
                @foreach ($announcements as $a)
                    <tr>
                        <td data-label="Judul" class="font-medium text-fg">{{ $a->title }}</td>
                        <td data-label="Untuk">{{ App\Models\Announcement::AUDIENCES[$a->audience] }}</td>
                        <td data-label="Status">@if ($a->published_at)<x-badge tone="success">Terbit</x-badge>@else<x-badge>Draf</x-badge>@endif</td>
                        <td data-label="Aksi"><div class="flex justify-end gap-2">
                            <x-button variant="secondary" size="sm" :href="route('pengelola.announcements.edit', $a)">Ubah</x-button>
                            <form method="POST" action="{{ route('pengelola.announcements.destroy', $a) }}" data-confirm="Hapus pengumuman ini?" data-confirm-ok="Hapus">@csrf @method('DELETE')<x-button variant="danger" size="sm">Hapus</x-button></form>
                        </div></td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
    @endif
</x-layouts.dashboard>
