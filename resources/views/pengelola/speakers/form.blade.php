<x-layouts.dashboard :title="$speaker->exists ? 'Ubah Pembicara' : 'Tambah Pembicara'">
    <x-page-header :title="$speaker->exists ? 'Ubah pembicara' : 'Tambah pembicara'" />
    <form method="POST" action="{{ $speaker->exists ? route('pengelola.speakers.update', $speaker) : route('pengelola.speakers.store') }}" enctype="multipart/form-data" class="card max-w-2xl space-y-5">
        @csrf
        @if ($speaker->exists) @method('PUT') @endif
        <x-field label="Nama" name="name" required><x-text-input name="name" :value="$speaker->name" required /></x-field>
        <x-field label="Usaha atau jabatan" name="business" required><x-text-input name="business" :value="$speaker->business" required /></x-field>
        <x-field label="Profil singkat" name="bio"><x-textarea name="bio" :value="$speaker->bio" rows="3" /></x-field>
        <x-field label="Akun Bos (bila pembicara juga Bos di alloBos)" name="user_id" hint="Dibutuhkan bila pembicara akan menandatangani sertifikat."><x-select name="user_id" :options="$bosses" :value="$speaker->user_id" placeholder="Tidak ada" /></x-field>
        <x-field label="Foto" name="photo"><input id="f-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-fg-body file:mr-3 file:rounded-full file:border-0 file:bg-brand file:px-4 file:py-2 file:text-brand-fg"></x-field>
        <label class="flex items-center gap-2 text-sm text-fg-body"><input type="hidden" name="is_demo" value="0"><input type="checkbox" name="is_demo" value="1" class="field-check" @checked(old('is_demo', $speaker->is_demo))>Data contoh (lencana Demo)</label>
        <x-button variant="primary">Simpan Pembicara</x-button>
    </form>
</x-layouts.dashboard>
