<x-layouts.dashboard title="Spesimen Tanda Tangan">
    <x-page-header title="Spesimen tanda tangan" description="Disimpan di penyimpanan privat dan hanya dipakai untuk sertifikat yang kamu tanda tangani." />
    <div class="grid max-w-3xl gap-6">
        @if ($preview)
            <x-card><p class="text-sm text-fg-muted">Spesimen sekarang</p><img src="{{ $preview }}" alt="Spesimen tanda tanganmu" class="mt-2 h-24 rounded-xl bg-surface-raised object-contain"></x-card>
        @endif
        <form method="POST" action="{{ route('signing.specimen.store') }}" enctype="multipart/form-data" class="card space-y-4">
            @csrf
            <h2 class="text-xl">Gambar di layar</h2>
            <x-signature-pad />
            @error('drawing')<p class="text-sm text-danger">{{ $message }}</p>@enderror
            <div class="space-y-2">
                <label for="ttd-file" class="block text-sm font-medium text-fg">Atau unggah berkas PNG transparan (maksimal 1 MB)</label>
                <input id="ttd-file" type="file" name="file" accept="image/png" class="block w-full text-sm text-fg-body file:mr-3 file:rounded-full file:border-0 file:bg-surface-alt file:px-4 file:py-2 file:text-fg">
                @error('file')<p class="text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <x-button variant="primary" data-signature-save disabled loading="Menyimpan">Simpan Tanda Tangan</x-button>
            <p class="text-sm text-fg-muted">Tombol aktif setelah kamu menggambar atau memilih berkas.</p>
        </form>
    </div>
</x-layouts.dashboard>
