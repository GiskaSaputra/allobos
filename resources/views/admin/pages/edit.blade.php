<x-layouts.dashboard :title="$page->title">
    <x-page-header :title="'Ubah '.$page->title" />
    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="card max-w-3xl space-y-5">
        @csrf @method('PUT')
        <x-field label="Judul" name="title" required><x-text-input name="title" :value="$page->title" required /></x-field>
        <x-field label="Isi (Markdown)" name="body" hint="## untuk subjudul, **tebal**, 1. untuk daftar bernomor." required><x-textarea name="body" :value="$page->body" rows="18" class="font-mono text-sm" required /></x-field>
        <x-button variant="primary">Simpan Halaman</x-button>
    </form>
</x-layouts.dashboard>
