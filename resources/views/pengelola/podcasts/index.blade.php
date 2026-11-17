<x-layouts.dashboard title="Siniar">
    <x-page-header title="Siniar" description="Episode dari URL YouTube dengan ringkasan dan tiga pelajaran.">
        <x-slot:actions><x-button variant="primary" :href="route('pengelola.podcasts.create')" icon="phosphor-plus">Tambah Episode</x-button></x-slot:actions>
    </x-page-header>
    <form method="GET" class="mb-4 flex max-w-md gap-2"><label for="cari" class="sr-only">Cari episode</label><input id="cari" name="q" value="{{ request('q') }}" class="field-input" placeholder="Cari judul"><x-button variant="secondary">Cari</x-button></form>
    @if ($podcasts->isEmpty())
        <x-empty-state icon="phosphor-headphones" title="Belum ada episode" action="Tambah Episode" :href="route('pengelola.podcasts.create')" />
    @else
        <x-table>
            <thead><tr><th>Episode</th><th>Bidang</th><th>Didengar</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($podcasts as $podcast)
                    <tr>
                        <td data-label="Episode"><a href="{{ route('pengelola.podcasts.edit', $podcast) }}" class="font-medium text-fg hover:underline">{{ $podcast->title }}</a> <x-demo-badge :show="$podcast->is_demo" /></td>
                        <td data-label="Bidang">{{ $podcast->field?->name ?? '-' }}</td>
                        <td data-label="Didengar">{{ $podcast->listens_count }}</td>
                        <td data-label="Status"><x-status-badge :status="$podcast->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-6">{{ $podcasts->links() }}</div>
    @endif
</x-layouts.dashboard>
