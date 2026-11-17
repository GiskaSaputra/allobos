<x-layouts.dashboard :title="$program->exists ? 'Ubah Kelas' : 'Buat Kelas'">
    <x-page-header :title="$program->exists ? 'Ubah kelas' : 'Buat kelas'">
        <x-slot:actions><x-button variant="ghost" :href="$program->exists ? route('pengelola.programs.show', $program) : route('pengelola.programs.index')" icon="phosphor-arrow-left">Kembali</x-button></x-slot:actions>
    </x-page-header>
    <form method="POST" action="{{ $program->exists ? route('pengelola.programs.update', $program) : route('pengelola.programs.store') }}" class="grid max-w-3xl gap-6">
        @csrf
        @if ($program->exists) @method('PUT') @endif
        <x-card class="space-y-5">
            <x-field label="Judul kelas" name="title" required><x-text-input name="title" :value="$program->title" maxlength="160" required /></x-field>
            <div class="grid gap-5 sm:grid-cols-3">
                <x-field label="Jenis" name="kind" required><x-select name="kind" :options="collect(App\Enums\ProgramKind::cases())->mapWithKeys(fn ($k) => [$k->value => $k->label()])" :value="$program->kind" /></x-field>
                <x-field label="Bidang" name="business_field_id"><x-select name="business_field_id" :options="$fields" :value="$program->business_field_id" placeholder="Pilih bidang" /></x-field>
                <x-field label="Kuota" name="quota" required><x-text-input name="quota" type="number" min="1" :value="$program->quota" required /></x-field>
            </div>
            <x-field label="Lokasi" name="location" hint="Contoh: Resort Villa Satu Indonesia, Gunungkidul, atau Daring lewat Zoom" required><x-text-input name="location" :value="$program->location" maxlength="200" required /></x-field>
            <x-field label="Deskripsi" name="description" required><x-textarea name="description" :value="$program->description" rows="6" maxlength="3000" required /></x-field>
            <x-field label="Materi" name="materials" hint="Satu baris satu materi, format: Judul | https://alamat-materi">
                <x-textarea name="materials" :value="collect($program->materials ?? [])->map(fn ($m) => $m['title'].' | '.$m['url'])->implode(PHP_EOL)" rows="3" />
            </x-field>
            <div class="flex flex-wrap gap-6">
                <label class="flex items-center gap-2 text-sm text-fg-body"><input type="hidden" name="is_official" value="0"><input type="checkbox" name="is_official" value="1" class="field-check" @checked(old('is_official', $program->is_official))>Program resmi IPI</label>
                <label class="flex items-center gap-2 text-sm text-fg-body"><input type="hidden" name="is_demo" value="0"><input type="checkbox" name="is_demo" value="1" class="field-check" @checked(old('is_demo', $program->is_demo))>Data contoh (lencana Demo)</label>
            </div>
        </x-card>

        <x-card class="space-y-3">
            <h2 class="text-xl">Pembicara</h2>
            @if ($speakers->isEmpty())
                <p class="text-sm text-fg-muted">Belum ada pembicara. <a href="{{ route('pengelola.speakers.create') }}" class="text-accent-text underline">Tambah Pembicara</a> dulu.</p>
            @else
                <div class="grid gap-2 sm:grid-cols-2">
                    @php($chosen = array_map('intval', old('speakers', $program->speakers->pluck('id')->all())))
                    @foreach ($speakers as $speaker)
                        <label class="flex items-center gap-3 rounded-2xl border border-line p-3 text-sm"><input type="checkbox" name="speakers[]" value="{{ $speaker->id }}" class="field-check" @checked(in_array($speaker->id, $chosen, true))><span><span class="block font-medium text-fg">{{ $speaker->name }}</span><span class="text-fg-muted">{{ $speaker->business }}</span></span></label>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card class="space-y-4" x-data="{ rows: @js(array_values($sessions)) }">
            <h2 class="text-xl">Jadwal sesi</h2>
            @error('sessions')<p class="text-sm text-danger">{{ $message }}</p>@enderror
            <template x-for="(row, i) in rows" :key="i">
                <div class="grid gap-2 rounded-[20px] border border-line p-3 sm:grid-cols-[1fr_150px_100px_100px_auto] sm:items-end">
                    <div><label class="text-xs text-fg-muted" :for="'s-title-' + i">Nama sesi</label><input :id="'s-title-' + i" :name="'sessions[' + i + '][title]'" x-model="row.title" class="field-input" required></div>
                    <div><label class="text-xs text-fg-muted" :for="'s-date-' + i">Tanggal</label><input type="date" :id="'s-date-' + i" :name="'sessions[' + i + '][date]'" x-model="row.date" class="field-input" required></div>
                    <div><label class="text-xs text-fg-muted" :for="'s-start-' + i">Mulai</label><input type="time" :id="'s-start-' + i" :name="'sessions[' + i + '][start]'" x-model="row.start" class="field-input" required></div>
                    <div><label class="text-xs text-fg-muted" :for="'s-end-' + i">Selesai</label><input type="time" :id="'s-end-' + i" :name="'sessions[' + i + '][end]'" x-model="row.end" class="field-input" required></div>
                    <button type="button" class="btn btn-ghost btn-icon" @click="rows.splice(i, 1)" x-show="rows.length > 1" aria-label="Hapus sesi"><x-phosphor-trash class="h-5 w-5" aria-hidden="true" /></button>
                </div>
            </template>
            @foreach ($errors->get('sessions.*') as $messages)<p class="text-sm text-danger">{{ $messages[0] }}</p>@endforeach
            <button type="button" class="btn btn-secondary btn-sm" @click="rows.push({ title: 'Sesi ' + (rows.length + 1), date: rows[rows.length - 1]?.date ?? '', start: '09:00', end: '12:00' })"><x-phosphor-plus class="h-4 w-4" aria-hidden="true" />Tambah Sesi</button>
            <p class="text-sm text-fg-muted">Jadwal tidak bisa diubah lagi setelah ada peserta yang tercatat hadir.</p>
        </x-card>

        <div><x-button variant="primary" loading="Menyimpan">Simpan Kelas</x-button></div>
    </form>
</x-layouts.dashboard>
