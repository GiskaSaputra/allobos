<x-layouts.dashboard title="Halaman">
    <x-page-header title="Halaman publik" description="Isi ditulis dengan Markdown sederhana." />
    <x-table>
        <thead><tr><th>Halaman</th><th>Alamat</th><th>Diubah</th><th><span class="sr-only">Aksi</span></th></tr></thead>
        <tbody>
            @foreach ($pages as $page)
                <tr>
                    <td data-label="Halaman" class="font-medium text-fg">{{ $page->title }}</td>
                    <td data-label="Alamat"><a href="{{ url($page->slug) }}" target="_blank" class="underline">/{{ $page->slug }}</a></td>
                    <td data-label="Diubah">{{ $page->updated_at->translatedFormat('d M Y') }}</td>
                    <td data-label="Aksi"><x-button variant="secondary" size="sm" :href="route('admin.pages.edit', $page)">Ubah</x-button></td>
                </tr>
            @endforeach
        </tbody>
    </x-table>
</x-layouts.dashboard>
