<x-layouts.dashboard title="Bank Kejadian">
    <x-page-header title="Bank kejadian Warung Bos" description="Dipakai bila AI tidak tersedia. Efek dijepit ke rentang tema tiap babak.">
        <x-slot:actions><x-button variant="primary" :href="route('admin.events.create')" icon="phosphor-plus">Tambah Kejadian</x-button></x-slot:actions>
    </x-page-header>
    <nav class="chip-row mb-4" aria-label="Saring hari">
        <a href="{{ route('admin.events.index') }}" @class(['chip', 'chip-active' => ! request('hari')])>Semua</a>
        @foreach (App\Services\Game\GameRules::THEMES as $day => $theme)<a href="{{ route('admin.events.index', ['hari' => $day]) }}" @class(['chip', 'chip-active' => (int) request('hari') === $day])>{{ $day }}. {{ $theme['title'] }}</a>@endforeach
    </nav>
    <x-table>
        <thead><tr><th>Hari</th><th>Kejadian</th><th>Jenis</th><th>Efek</th><th><span class="sr-only">Aksi</span></th></tr></thead>
        <tbody>
            @foreach ($events as $event)
                <tr>
                    <td data-label="Hari">{{ $event->day }}</td>
                    <td data-label="Kejadian"><span class="block font-medium text-fg">{{ $event->title }}</span><span class="text-xs text-fg-muted">{{ Str::limit($event->story, 80) }}</span></td>
                    <td data-label="Jenis">{{ $event->kind ? App\Services\Game\GameRules::KINDS[$event->kind]['label'] : 'Semua' }}</td>
                    <td data-label="Efek" class="whitespace-nowrap text-xs">permintaan x{{ $event->demand }}, biaya x{{ $event->cost }}, kas {{ $event->cash }}</td>
                    <td data-label="Aksi"><div class="flex justify-end gap-2">
                        <x-button variant="secondary" size="sm" :href="route('admin.events.edit', $event)">Ubah</x-button>
                        <form method="POST" action="{{ route('admin.events.destroy', $event) }}" data-confirm="Hapus kejadian ini?" data-confirm-ok="Hapus">@csrf @method('DELETE')<x-button variant="danger" size="sm">Hapus</x-button></form>
                    </div></td>
                </tr>
            @endforeach
        </tbody>
    </x-table>
    <div class="mt-6">{{ $events->links() }}</div>
</x-layouts.dashboard>
