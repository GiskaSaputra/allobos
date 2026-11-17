<x-layouts.dashboard title="Pengaturan">
    <x-page-header title="Pengaturan" description="Bobot Skor Siap Usaha dan batas sesi Ruang Bos." />
    <form method="POST" action="{{ route('admin.settings.update') }}" class="card max-w-xl space-y-5" x-data="{ w: @js(old('weights', $weights)) }">
        @csrf @method('PATCH')
        <fieldset class="space-y-3">
            <legend class="font-display text-lg font-semibold text-fg">Bobot skor (jumlah harus 100)</legend>
            @foreach ($labels as $key => $label)
                <div class="flex items-center justify-between gap-4">
                    <label for="w-{{ $key }}" class="text-sm text-fg-body">{{ $label }}</label>
                    <input id="w-{{ $key }}" type="number" min="0" max="100" name="weights[{{ $key }}]" x-model.number="w.{{ $key }}" class="field-input w-28 text-right">
                </div>
            @endforeach
            <p class="text-sm" :class="Object.values(w).reduce((a, b) => a + Number(b), 0) === 100 ? 'text-success' : 'text-danger'">Jumlah: <span x-text="Object.values(w).reduce((a, b) => a + Number(b), 0)"></span></p>
        </fieldset>
        <x-field label="Sesi Ruang Bos per warga per hari" name="sessions"><x-text-input name="sessions" type="number" min="1" max="50" :value="$sessions" /></x-field>
        <x-button variant="primary">Simpan Pengaturan</x-button>
    </form>
</x-layouts.dashboard>
