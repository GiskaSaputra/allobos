<x-layouts.dashboard title="Kejadian">
    <x-page-header :title="$event->exists ? 'Ubah kejadian' : 'Tambah kejadian'" />
    <form method="POST" action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}" class="card max-w-2xl space-y-5">
        @csrf
        @if ($event->exists) @method('PUT') @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <x-field label="Hari" name="day"><x-select name="day" :options="collect(App\Services\Game\GameRules::THEMES)->map(fn ($t, $d) => $d.'. '.$t['title'])" :value="$event->day" /></x-field>
            <x-field label="Jenis warung" name="kind"><x-select name="kind" :options="collect(App\Services\Game\GameRules::KINDS)->map(fn ($k) => $k['label'])" :value="$event->kind" placeholder="Semua jenis" /></x-field>
        </div>
        <x-field label="Judul" name="title" required><x-text-input name="title" :value="$event->title" maxlength="80" required /></x-field>
        <x-field label="Cerita" name="story" required><x-textarea name="story" :value="$event->story" rows="3" maxlength="400" required /></x-field>
        <div class="grid gap-5 sm:grid-cols-3">
            <x-field label="Pengali permintaan" name="demand"><x-text-input name="demand" type="number" step="0.01" :value="$event->demand" /></x-field>
            <x-field label="Pengali biaya" name="cost"><x-text-input name="cost" type="number" step="0.01" :value="$event->cost" /></x-field>
            <x-field label="Tambahan kas (Rp)" name="cash"><x-text-input name="cash" type="number" step="1000" :value="$event->cash" /></x-field>
        </div>
        <x-button variant="primary">Simpan Kejadian</x-button>
    </form>
</x-layouts.dashboard>
