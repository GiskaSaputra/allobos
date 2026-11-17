@php($current = $features[$feature])
<x-layouts.dashboard title="Pengaturan AI">
    <x-page-header title="Pengaturan AI" description="Aktif, model, suhu, kuota harian, dan prompt berversi tiap fitur. Berlaku tanpa mengubah kode." />
    @unless ($configured)<p class="mb-6 rounded-[20px] bg-warning/15 p-4 text-sm text-fg">GEMINI_API_KEY belum diisi di server. Uji Prompt akan gagal sampai kunci diisi.</p>@endunless
    <nav class="chip-row mb-6" aria-label="Pilih fitur">
        @foreach ($features as $key => $f)<a href="{{ route('admin.ai.index', ['fitur' => $key]) }}" @class(['chip', 'chip-active' => $key === $feature])>{{ $f['label'] }}</a>@endforeach
    </nav>
    <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
        <form method="POST" action="{{ route('admin.ai.update', $feature) }}" class="card space-y-4 self-start">
            @csrf @method('PATCH')
            <h2 class="text-xl">{{ $current['label'] }}</h2>
            <p class="text-sm text-fg-muted">Hari ini: {{ $usage['ok'] ?? 0 }} berhasil, {{ $usage['gagal'] ?? 0 }} gagal.</p>
            <label class="flex items-center justify-between gap-3 text-sm text-fg-body">Fitur aktif<input type="checkbox" name="enabled" value="1" class="field-check" @checked($current['enabled'])></label>
            <x-field label="Model" name="model" :hint="'Kosongkan untuk memakai bawaan '.config('ai.model')"><x-text-input name="model" :value="$current['model'] !== config('ai.model') ? $current['model'] : null" /></x-field>
            <x-field label="Suhu" name="temperature"><x-text-input name="temperature" type="number" step="0.1" min="0" max="2" :value="$current['temperature']" /></x-field>
            <x-field label="Kuota harian per pengguna" name="quota" hint="Kosongkan untuk tanpa batas."><x-text-input name="quota" type="number" min="0" :value="$current['quota']" /></x-field>
            @if ($feature === 'product_check')
                <label class="flex items-center justify-between gap-3 text-sm text-fg-body">Penelusuran web untuk Sinyal Pasar<input type="checkbox" name="search" value="1" class="field-check" @checked($current['search'])></label>
            @endif
            <x-button variant="primary">Simpan Pengaturan AI</x-button>
        </form>
        <div class="space-y-6">
            <form method="POST" class="card space-y-4" x-data>
                @csrf
                <div class="flex flex-wrap items-center justify-between gap-2"><h2 class="text-xl">Prompt</h2><span class="text-sm text-fg-muted">{{ $version ? 'Versi '.$version : 'Bawaan dari config/ai.php' }}</span></div>
                <x-field label="Instruksi sistem" name="prompt"><x-textarea name="prompt" :value="old('prompt', $prompt)" rows="10" /></x-field>
                <x-field label="Masukan uji" name="input" hint="Dipakai tombol Uji Prompt. Tidak memakan kuota warga."><x-textarea name="input" :value="old('input')" rows="3" placeholder="Contoh: Profil warga Ponjong, modal di bawah Rp1 juta, ingin menjual keripik." /></x-field>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" formaction="{{ route('admin.ai.prompts.test', $feature) }}" class="btn btn-secondary" data-loading="Menguji"><span data-label>Uji Prompt</span></button>
                    <button type="submit" formaction="{{ route('admin.ai.prompts.store', $feature) }}" class="btn btn-primary" data-loading="Menyimpan"><span data-label>Simpan sebagai Versi Baru</span></button>
                </div>
                @if ($test)
                    <div @class(['rounded-[20px] p-4 text-sm', 'bg-success/10' => $test['ok'], 'bg-danger/10' => ! $test['ok']])>
                        <p class="font-medium text-fg">{{ $test['ok'] ? 'Jawaban sesuai skema' : 'Gagal' }}, {{ $test['ms'] }} ms</p>
                        <pre class="mt-2 max-h-80 overflow-auto whitespace-pre-wrap font-mono text-xs text-fg-body">{{ $test['output'] }}</pre>
                    </div>
                @endif
            </form>
            @if ($versions->isNotEmpty())
                <x-card>
                    <h2 class="text-xl">Riwayat versi</h2>
                    <ul class="mt-3 space-y-1 text-sm">@foreach ($versions as $v)<li class="flex justify-between"><span class="text-fg">Versi {{ $v->version }} @if ($v->is_active)<x-badge tone="success">aktif</x-badge>@endif</span><span class="text-fg-muted">{{ $v->created_at->translatedFormat('d M Y H:i') }}</span></li>@endforeach</ul>
                </x-card>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
