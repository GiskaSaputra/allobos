<x-layouts.dashboard title="Pembicara">
    <x-page-header title="Pembicara" description="Hanya Pengelola IPI yang mengisi data pembicara.">
        <x-slot:actions><x-button variant="primary" :href="route('pengelola.speakers.create')" icon="phosphor-plus">Tambah Pembicara</x-button></x-slot:actions>
    </x-page-header>
    <form method="GET" class="mb-4 flex max-w-md gap-2"><label for="cari" class="sr-only">Cari pembicara</label><input id="cari" name="q" value="{{ request('q') }}" class="field-input" placeholder="Cari nama"><x-button variant="secondary">Cari</x-button></form>
    @if ($speakers->isEmpty())
        <x-empty-state icon="phosphor-microphone-stage" title="Belum ada pembicara" action="Tambah Pembicara" :href="route('pengelola.speakers.create')" />
    @else
        <x-table>
            <thead><tr><th>Nama</th><th>Usaha</th><th>Akun Bos</th><th>Kelas</th><th><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
                @foreach ($speakers as $speaker)
                    <tr>
                        <td data-label="Nama" class="font-medium text-fg">{{ $speaker->name }} <x-demo-badge :show="$speaker->is_demo" /></td>
                        <td data-label="Usaha">{{ $speaker->business }}</td>
                        <td data-label="Akun Bos">{{ $speaker->user?->name ?? '-' }}</td>
                        <td data-label="Kelas">{{ $speaker->programs_count }}</td>
                        <td data-label="Aksi"><div class="flex justify-end gap-2">
                            <x-button variant="secondary" size="sm" :href="route('pengelola.speakers.edit', $speaker)">Ubah</x-button>
                            <form method="POST" action="{{ route('pengelola.speakers.destroy', $speaker) }}" data-confirm="Hapus pembicara {{ $speaker->name }}?" data-confirm-ok="Hapus">@csrf @method('DELETE')<x-button variant="danger" size="sm">Hapus</x-button></form>
                        </div></td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-6">{{ $speakers->links() }}</div>
    @endif
</x-layouts.dashboard>
