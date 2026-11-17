<x-layouts.dashboard title="Tambah Episode">
    <x-page-header title="Tambah episode" description="Tempel tautan YouTube. Judul dan gambar sampul diambil otomatis." />
    <form method="POST" action="{{ route('pengelola.podcasts.fetch') }}" class="card max-w-2xl space-y-4">
        @csrf
        <x-field label="Tautan video YouTube" name="url" required><x-text-input name="url" type="url" placeholder="https://www.youtube.com/watch?v=..." required /></x-field>
        <x-button variant="secondary" loading="Mengambil data">Ambil Data</x-button>
    </form>
</x-layouts.dashboard>
