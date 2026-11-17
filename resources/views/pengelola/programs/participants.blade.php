<x-layouts.dashboard :title="'Peserta '.$program->title">
    <x-page-header :title="'Peserta: '.$program->title" :description="$program->sessions->count().' sesi. Lulus hadir minimal 75 persen.'">
        <x-slot:actions>
            <x-button variant="secondary" :href="route('pengelola.programs.export', $program)">Unduh Peserta (XLSX)</x-button>
            <x-button variant="ghost" :href="route('pengelola.programs.show', $program)">Kembali</x-button>
        </x-slot:actions>
    </x-page-header>
    @if ($rows->isEmpty())
        <x-empty-state icon="phosphor-users" title="Belum ada peserta" />
    @else
        <x-table>
            <thead><tr><th>Nama</th><th>WhatsApp</th><th>Hadir</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td data-label="Nama" class="font-medium text-fg">{{ $row['enrollment']->user->name }}</td>
                        <td data-label="WhatsApp">{{ $row['enrollment']->user->whatsapp }}</td>
                        <td data-label="Hadir">{{ $row['attended'] }} / {{ $program->sessions->count() }}</td>
                        <td data-label="Status"><x-status-badge :status="$row['enrollment']->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
    @endif
</x-layouts.dashboard>
