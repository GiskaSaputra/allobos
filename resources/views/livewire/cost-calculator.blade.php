@php($rp = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.'))
<div class="grid gap-6 lg:grid-cols-[1fr_360px]">
    <form wire:submit="save" class="space-y-6">
        @foreach (['bahan' => ['Biaya bahan untuk sekali produksi', 'Semua yang habis dipakai tiap kali membuat, misalnya singkong, minyak, bumbu, kemasan.'], 'tetap' => ['Biaya tetap per bulan', 'Biaya yang tetap keluar walau sedikit produksi, misalnya gas, listrik, sewa, cicilan alat.']] as $kind => [$heading, $help])
            <x-card>
                <h2 class="text-xl">{{ $heading }}</h2>
                <p class="mt-1 text-sm text-fg-muted">{{ $help }}</p>
                <ul class="mt-4 space-y-3">
                    @foreach ($rows as $i => $row)
                        @continue($row['kind'] !== $kind)
                        <li class="grid grid-cols-[1fr_auto] gap-2 sm:grid-cols-[1fr_180px_auto]" wire:key="row-{{ $i }}">
                            <div class="col-span-2 sm:col-span-1">
                                <label for="nama-{{ $i }}" class="sr-only">Nama biaya</label>
                                <input id="nama-{{ $i }}" type="text" wire:model.blur="rows.{{ $i }}.name" class="field-input" placeholder="Nama biaya" maxlength="80">
                                @error("rows.$i.name")<p class="mt-1 text-sm text-danger">Isi nama biaya.</p>@enderror
                            </div>
                            <div>
                                <label for="jumlah-{{ $i }}" class="sr-only">Jumlah rupiah untuk {{ $row['name'] ?: 'biaya ini' }}</label>
                                <input id="jumlah-{{ $i }}" type="number" min="0" step="500" inputmode="numeric" wire:model.live.debounce.400ms="rows.{{ $i }}.amount" class="field-input text-right">
                            </div>
                            <button type="button" wire:click="removeRow({{ $i }})" class="btn btn-ghost btn-icon" aria-label="Hapus baris {{ $row['name'] ?: 'biaya' }}"><x-phosphor-trash class="h-5 w-5" aria-hidden="true" /></button>
                        </li>
                    @endforeach
                </ul>
                <button type="button" wire:click="addRow('{{ $kind }}')" class="btn btn-secondary btn-sm mt-4"><x-phosphor-plus class="h-4 w-4" aria-hidden="true" />Tambah Biaya</button>
            </x-card>
        @endforeach

        <x-card>
            <h2 class="text-xl">Produksi dan harga</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div class="space-y-2">
                    <label for="units" class="block text-sm font-medium text-fg">Hasil sekali produksi</label>
                    <input id="units" type="number" min="1" inputmode="numeric" wire:model.live.debounce.400ms="units" class="field-input">
                    <p class="text-xs text-fg-muted">Jumlah bungkus atau unit</p>
                </div>
                <div class="space-y-2">
                    <label for="batches" class="block text-sm font-medium text-fg">Produksi per bulan</label>
                    <input id="batches" type="number" min="1" inputmode="numeric" wire:model.live.debounce.400ms="batches" class="field-input">
                    <p class="text-xs text-fg-muted">Berapa kali membuat</p>
                </div>
                <div class="space-y-2">
                    <label for="price" class="block text-sm font-medium text-fg">Harga jual per unit</label>
                    <input id="price" type="number" min="0" step="500" inputmode="numeric" wire:model.live.debounce.400ms="price" class="field-input">
                    <p class="text-xs text-fg-muted">Dalam rupiah</p>
                </div>
            </div>
            @error('units')<p class="mt-2 text-sm text-danger">{{ $message }}</p>@enderror
            @error('batches')<p class="mt-2 text-sm text-danger">{{ $message }}</p>@enderror
        </x-card>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Simpan Hitungan</span>
                <span wire:loading wire:target="save">Menyimpan</span>
            </button>
            @if ($saved)<p class="text-sm text-success" role="status">Hitungan tersimpan di Rencana Usaha.</p>@endif
        </div>
    </form>

    <aside class="lg:sticky lg:top-24 lg:self-start">
        <div class="panel-ink space-y-5 p-6" aria-live="polite">
            <h2 class="text-xl text-ink-fg">Hasil hitungan</h2>
            @if (! $result['valid'])
                <p class="text-ink-muted">Isi hasil produksi dan produksi per bulan dengan angka lebih dari nol.</p>
            @else
                <div>
                    <p class="text-sm text-ink-muted">HPP per unit (biaya membuat satu barang)</p>
                    <p class="font-display text-4xl font-bold text-accent">{{ $rp($result['hpp_unit']) }}</p>
                </div>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-ink-muted">Bahan per unit</dt><dd class="font-medium text-ink-fg">{{ $rp($result['variable_unit']) }}</dd></div>
                    <div><dt class="text-ink-muted">Biaya tetap per bulan</dt><dd class="font-medium text-ink-fg">{{ $rp($result['fixed']) }}</dd></div>
                    <div><dt class="text-ink-muted">Untung per unit</dt><dd @class(['font-medium', 'text-accent' => $result['margin'] >= 0, 'text-danger' => $result['margin'] < 0])>{{ $rp($result['margin']) }} ({{ str_replace('.', ',', $result['margin_pct']) }}%)</dd></div>
                    <div><dt class="text-ink-muted">Perkiraan untung sebulan</dt><dd @class(['font-medium', 'text-accent' => $result['profit_month'] >= 0, 'text-danger' => $result['profit_month'] < 0])>{{ $rp($result['profit_month']) }}</dd></div>
                </dl>
                <div class="rounded-[20px] bg-ink-raised p-4">
                    <p class="text-sm text-ink-muted">Titik impas (balik modal biaya tetap)</p>
                    @if ($result['break_even_units'] === null)
                        <p class="mt-1 font-medium text-danger">Harga jual masih di bawah biaya bahan per unit, jadi usaha selalu rugi. Naikkan harga atau tekan biaya bahan.</p>
                    @else
                        <p class="mt-1 font-medium text-ink-fg">Jual minimal {{ number_format($result['break_even_units'], 0, ',', '.') }} unit per bulan, kira-kira {{ $result['break_even_batches'] }} kali produksi.</p>
                    @endif
                </div>
                @if ($price > 0 && $result['margin_pct'] < 20 && $result['margin'] >= 0)
                    <p class="text-sm text-ink-muted">Untungmu di bawah 20 persen dari harga jual. Pertimbangkan menaikkan harga sedikit atau membeli bahan dalam jumlah lebih besar.</p>
                @endif
            @endif
        </div>
    </aside>
</div>
