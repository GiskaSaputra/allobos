<x-layouts.dashboard title="Jadwal dan Slot">
    <x-page-header title="Jadwal dan Slot" description="Buka slot waktu yang bisa dipesan warga. Warga memesan paling lambat 12 jam sebelumnya." />
    <form method="POST" action="{{ route('bos.slots.store') }}" class="card mb-6 grid gap-4 sm:grid-cols-[1fr_1fr_1fr_1fr_auto] sm:items-end">
        @csrf
        <x-field label="Tanggal" name="date"><x-text-input name="date" type="date" :value="now()->addDays(2)->format('Y-m-d')" required /></x-field>
        <x-field label="Jam" name="time"><x-text-input name="time" type="time" value="13:00" required /></x-field>
        <x-field label="Durasi" name="duration_minutes"><x-select name="duration_minutes" :options="[30 => '30 menit', 45 => '45 menit', 60 => '60 menit', 90 => '90 menit', 120 => '120 menit']" value="60" /></x-field>
        <x-field label="Cara" name="mode"><x-select name="mode" :options="App\Models\BossSlot::MODES" value="daring" /></x-field>
        <x-button variant="primary" icon="phosphor-plus" :disabled="$boss->status !== App\Enums\BossStatus::Aktif">Tambah Slot</x-button>
    </form>
    @if ($slots->isEmpty())
        <x-empty-state icon="phosphor-calendar" title="Belum ada slot">Tambahkan slot di atas supaya kamu tampil di direktori Temu Bos.</x-empty-state>
    @else
        <x-table>
            <thead><tr><th>Waktu</th><th>Durasi</th><th>Cara</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
                @foreach ($slots as $slot)
                    @php($holding = $slot->sessions->first(fn ($s) => $s->status->holdsSlot()))
                    <tr>
                        <td data-label="Waktu" class="font-medium text-fg">{{ $slot->starts_at->translatedFormat('l, d M Y H:i') }}</td>
                        <td data-label="Durasi">{{ $slot->duration_minutes }} menit</td>
                        <td data-label="Cara">{{ App\Models\BossSlot::MODES[$slot->mode] }}</td>
                        <td data-label="Status">@if ($holding)<a href="{{ route('bos.sessions.show', $holding) }}"><x-status-badge :status="$holding->status" /></a>@else<x-badge tone="success">Terbuka</x-badge>@endif</td>
                        <td data-label="Aksi">@unless ($holding)<form method="POST" action="{{ route('bos.slots.destroy', $slot) }}" data-confirm="Hapus slot ini?" data-confirm-ok="Hapus">@csrf @method('DELETE')<x-button variant="danger" size="sm">Hapus</x-button></form>@endunless</td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
    @endif
</x-layouts.dashboard>
