<x-layouts.dashboard :title="$podcast->title">
    @if ($podcast->summary_state === 'memproses')@push('head')<meta http-equiv="refresh" content="5">@endpush @endif
    <x-page-header :title="$podcast->title" description="Periksa dan ubah ringkasan AI sebelum terbit.">
        <x-slot:actions>
            @if ($podcast->status === App\Enums\ContentStatus::Terbit)
                <x-button variant="ghost" :href="route('podcasts.show', $podcast)" target="_blank">Lihat Episode</x-button>
            @else
                <form method="POST" action="{{ route('pengelola.podcasts.publish', $podcast) }}" data-confirm="Terbitkan episode ini? Warga dengan minat bidang yang sama akan diberi tahu." data-confirm-ok="Terbitkan">@csrf<x-button variant="primary">Terbitkan</x-button></form>
            @endif
        </x-slot:actions>
    </x-page-header>
    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <form method="POST" action="{{ route('pengelola.podcasts.update', $podcast) }}" class="card space-y-5">
            @csrf @method('PUT')
            <x-field label="Judul" name="title" required><x-text-input name="title" :value="$podcast->title" required /></x-field>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="Bidang" name="business_field_id"><x-select name="business_field_id" :options="$fields" :value="$podcast->business_field_id" placeholder="Pilih bidang" /></x-field>
                <x-field label="Durasi (menit)" name="duration_minutes"><x-text-input name="duration_minutes" type="number" min="1" :value="$podcast->duration_seconds ? (int) ceil($podcast->duration_seconds / 60) : null" /></x-field>
            </div>
            <x-field label="Catatan pengelola" name="source_notes" hint="Dipakai AI bila video tidak bisa dibaca. Tulis poin penting dari episode."><x-textarea name="source_notes" :value="$podcast->source_notes" rows="4" /></x-field>
            <x-field label="Ringkasan" name="summary"><x-textarea name="summary" :value="$podcast->summary" rows="4" /></x-field>
            @for ($i = 0; $i < 3; $i++)
                <x-field :label="'Pelajaran '.($i + 1)" :name="'lessons['.$i.']'"><x-text-input :name="'lessons['.$i.']'" :value="$podcast->lessons[$i] ?? null" maxlength="300" /></x-field>
            @endfor
            <label class="flex items-center gap-2 text-sm text-fg-body"><input type="hidden" name="is_demo" value="0"><input type="checkbox" name="is_demo" value="1" class="field-check" @checked($podcast->is_demo)>Data contoh (lencana Demo)</label>
            <x-button variant="primary">Simpan</x-button>
        </form>
        <aside class="space-y-4">
            <img src="{{ $podcast->thumbnail() }}" alt="" class="w-full rounded-card border border-line">
            <x-card class="space-y-3">
                <h2 class="text-xl">Ringkasan AI</h2>
                <p class="text-sm text-fg-muted">
                    @switch($podcast->summary_state)
                        @case('memproses') Sedang meringkas. Halaman memuat ulang sendiri. @break
                        @case('draf') Hasil AI tersedia. Periksa lalu terbitkan. @break
                        @case('gagal') Ringkasan gagal disusun. Isi catatan pengelola lalu coba lagi, atau tulis ringkasan sendiri. @break
                        @case('disetujui') Ringkasan sudah disetujui dan terbit. @break
                        @default Belum ada ringkasan.
                    @endswitch
                </p>
                <form method="POST" action="{{ route('pengelola.podcasts.summarize', $podcast) }}">@csrf<x-button variant="secondary" loading="Meringkas" :disabled="$podcast->summary_state === 'memproses'">Buat Ringkasan</x-button></form>
            </x-card>
            <form method="POST" action="{{ route('pengelola.podcasts.destroy', $podcast) }}" data-confirm="Hapus episode ini beserta tanda dengar warga?" data-confirm-ok="Hapus">@csrf @method('DELETE')<x-button variant="danger" size="sm">Hapus Episode</x-button></form>
        </aside>
    </div>
</x-layouts.dashboard>
