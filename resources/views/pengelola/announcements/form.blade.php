<x-layouts.dashboard title="Pengumuman">
    <x-page-header :title="$announcement->exists ? 'Ubah pengumuman' : 'Buat pengumuman'" />
    <form method="POST" action="{{ $announcement->exists ? route('pengelola.announcements.update', $announcement) : route('pengelola.announcements.store') }}" class="card max-w-2xl space-y-5">
        @csrf
        @if ($announcement->exists) @method('PUT') @endif
        <x-field label="Judul" name="title" required><x-text-input name="title" :value="$announcement->title" required /></x-field>
        <x-field label="Isi" name="body" required><x-textarea name="body" :value="$announcement->body" rows="4" required /></x-field>
        <x-field label="Untuk" name="audience"><x-select name="audience" :options="App\Models\Announcement::AUDIENCES" :value="$announcement->audience" /></x-field>
        <label class="flex items-center gap-2 text-sm text-fg-body"><input type="checkbox" name="publish" value="1" class="field-check" @checked($announcement->published_at || ! $announcement->exists)>Terbitkan sekarang</label>
        <x-button variant="primary">Simpan Pengumuman</x-button>
    </form>
</x-layouts.dashboard>
