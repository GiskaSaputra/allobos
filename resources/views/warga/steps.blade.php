<x-layouts.app title="Langkah 30 Hari">
    <x-page-header title="Langkah 30 Hari" description="Centang langkah yang sudah kamu kerjakan. Persentase selesai ikut menaikkan Skor Siap Usaha." />

    <x-card class="mb-6">
        <div class="flex items-center justify-between text-sm"><span class="font-medium text-fg">{{ $progress['done'] }} dari {{ $progress['total'] }} langkah selesai</span><span class="font-display text-2xl font-bold text-fg">{{ $progress['percent'] }}%</span></div>
        <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-line" role="progressbar" aria-valuenow="{{ $progress['percent'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="Persentase langkah selesai"><div class="h-full rounded-full bg-accent-text transition-all duration-500" style="width: {{ $progress['percent'] }}%"></div></div>
    </x-card>

    @if ($weeks->isEmpty())
        <x-empty-state icon="phosphor-list-checks" title="Belum ada langkah" action="Susun Rencana Usaha" :href="route('warga.plan.show')">Langkah datang dari Rencana Usaha, Catatan Konsultasi, atau Temu Bos. Kamu juga bisa menambah sendiri di bawah.</x-empty-state>
    @else
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($weeks as $week => $steps)
                <section class="card">
                    <h2 class="text-xl">Minggu {{ $week }}</h2>
                    <ul class="mt-3 space-y-1">
                        @foreach ($steps as $step)
                            <li id="langkah-{{ $step->id }}" class="group rounded-2xl px-2 py-2 hover:bg-fg/5" x-data="{ edit: false }">
                                <div class="flex items-start gap-3" x-show="!edit">
                                    <form method="POST" action="{{ route('warga.steps.update', $step) }}" class="pt-0.5">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="done" value="{{ $step->done_at ? 0 : 1 }}">
                                        <input type="checkbox" id="cek-{{ $step->id }}" class="field-check" @checked($step->done_at) onchange="this.form.requestSubmit()">
                                    </form>
                                    <label for="cek-{{ $step->id }}" class="min-w-0 flex-1 cursor-pointer">
                                        <span @class(['block', 'text-fg-muted line-through' => $step->done_at, 'text-fg' => ! $step->done_at])>{{ $step->title }}</span>
                                        <span class="text-xs text-fg-muted">{{ $step->origin->label() }}</span>
                                    </label>
                                    <button type="button" class="btn btn-ghost btn-icon btn-sm" @click="edit = true" aria-label="Ubah langkah {{ $step->title }}"><x-phosphor-pencil-simple class="h-4 w-4" aria-hidden="true" /></button>
                                    <form method="POST" action="{{ route('warga.steps.destroy', $step) }}" data-confirm="Hapus langkah ini?" data-confirm-ok="Hapus">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-icon btn-sm" aria-label="Hapus langkah {{ $step->title }}"><x-phosphor-trash class="h-4 w-4" aria-hidden="true" /></button>
                                    </form>
                                </div>
                                <form x-show="edit" x-cloak method="POST" action="{{ route('warga.steps.update', $step) }}" class="grid gap-2 sm:grid-cols-[1fr_120px_auto]">
                                    @csrf @method('PATCH')
                                    <label class="sr-only" for="judul-{{ $step->id }}">Isi langkah</label>
                                    <input id="judul-{{ $step->id }}" name="title" value="{{ $step->title }}" maxlength="250" class="field-input" required>
                                    <label class="sr-only" for="minggu-{{ $step->id }}">Minggu</label>
                                    <select id="minggu-{{ $step->id }}" name="week" class="field-input">@for ($w = 1; $w <= 4; $w++)<option value="{{ $w }}" @selected($w === $step->week)>Minggu {{ $w }}</option>@endfor</select>
                                    <div class="flex gap-1"><x-button variant="primary" size="sm">Simpan</x-button><button type="button" class="btn btn-ghost btn-sm" @click="edit = false">Batal</button></div>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('warga.steps.store') }}" class="card mt-6 grid gap-3 sm:grid-cols-[1fr_160px_auto] sm:items-end">
        @csrf
        <x-field label="Langkah baru" name="title"><x-text-input name="title" maxlength="250" required placeholder="Contoh: tanya harga kemasan di toko plastik" /></x-field>
        <x-field label="Minggu" name="week"><x-select name="week" :options="[1 => 'Minggu 1', 2 => 'Minggu 2', 3 => 'Minggu 3', 4 => 'Minggu 4']" value="1" /></x-field>
        <x-button variant="secondary" icon="phosphor-plus">Tambah Langkah</x-button>
    </form>
</x-layouts.app>
