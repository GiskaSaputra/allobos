<x-layouts.app title="Cek Produk">
    <x-page-header title="Cek Produk" description="Foto produkmu. AI menilai foto dan kemasan, menyarankan kisaran harga, dan menulis deskripsi jualan." />
    <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
        <form method="POST" action="{{ route('warga.checks.store') }}" enctype="multipart/form-data" class="card space-y-5"
              x-data="{ preview: null, camera: window.matchMedia('(pointer: coarse)').matches, pick(e) { const f = e.target.files[0]; if (!f) return; this.preview = URL.createObjectURL(f); ['kamera', 'galeri'].forEach(id => { if (id !== e.target.id) document.getElementById(id).value = '' }); } }">
            @csrf
            <div class="flex aspect-[4/3] items-center justify-center overflow-hidden rounded-[24px] border border-dashed border-line bg-surface-alt">
                <template x-if="preview"><img :src="preview" alt="Pratinjau foto produk" class="h-full w-full object-cover"></template>
                <p x-show="!preview" class="px-6 text-center text-sm text-fg-muted">Foto produk di tempat terang dengan latar polos supaya hasilnya lebih tepat.</p>
            </div>
            <input type="file" id="kamera" name="photo" accept="image/*" capture="environment" class="sr-only" @change="pick">
            <input type="file" id="galeri" name="photo" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="pick">
            <div class="flex flex-wrap gap-2">
                <label for="kamera" class="btn btn-primary cursor-pointer" x-show="camera" x-cloak><x-phosphor-camera class="h-5 w-5" aria-hidden="true" />Ambil Foto</label>
                <label for="galeri" class="btn btn-secondary cursor-pointer"><x-phosphor-images class="h-5 w-5" aria-hidden="true" />Pilih dari Galeri</label>
            </div>
            @error('photo')<p class="text-sm text-danger">{{ $message }}</p>@enderror
            <p class="text-sm text-fg-muted">JPG, PNG, atau WEBP. Maksimal 15 MB.{{ $quotaLeft !== null ? ' Sisa '.$quotaLeft.' pemeriksaan hari ini.' : '' }}</p>
            <x-button variant="primary" loading="Memeriksa foto" x-bind:disabled="!preview || {{ $quotaLeft === 0 ? 'true' : 'false' }}" icon="phosphor-magnifying-glass">Periksa Produk</x-button>
        </form>

        <section>
            <h2 class="mb-3 text-xl">Riwayat</h2>
            @if ($checks->isEmpty())
                <x-empty-state icon="phosphor-camera" title="Belum ada pemeriksaan">Hasil tiap foto tersimpan di sini supaya kamu bisa membandingkan sebelum dan sesudah perbaikan.</x-empty-state>
            @else
                <ul class="grid gap-2">
                    @foreach ($checks as $item)
                        <li>
                            <a href="{{ route('warga.checks.show', $item) }}" class="card card-link flex items-center gap-3 p-3 sm:p-3">
                                <img src="{{ $item->photoUrl() }}" alt="" class="h-16 w-16 shrink-0 rounded-2xl object-cover">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-medium text-fg">{{ $item->name ?? 'Sedang diperiksa' }}</span>
                                    <span class="block text-sm text-fg-muted">{{ $item->created_at->translatedFormat('d M Y, H:i') }}
                                        @if ($item->result) . Foto {{ $item->result['photo_score'] }}/4, kemasan {{ $item->result['pack_score'] }}/4 @endif
                                    </span>
                                </span>
                                <x-status-badge :status="$item->status" />
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4">{{ $checks->links() }}</div>
            @endif
        </section>
    </div>
</x-layouts.app>
