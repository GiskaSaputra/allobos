<x-layouts.app title="Temu Bos">
    <x-page-header title="Temu Bos" description="Pesan sesi mentoring dengan pengusaha aktif yang dikurasi IPI." />
    @if ($noteId)<p class="mb-6 rounded-[20px] bg-info/10 p-4 text-sm text-fg">Catatan Konsultasimu akan dilampirkan. Pilih Bos lalu pilih slotnya.</p>@endif

    @if ($sessions->isNotEmpty())
        <section class="mb-10">
            <h2 class="mb-3 text-xl">Sesi saya</h2>
            <ul class="grid gap-2">
                @foreach ($sessions as $session)
                    <li><a href="{{ route('warga.mentoring.show', $session) }}" class="card card-link flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-4">
                        <span><span class="block font-medium text-fg">{{ $session->boss->user->name }}</span><span class="text-sm text-fg-muted">{{ $session->slot->starts_at->translatedFormat('l, d F H:i') }} WIB</span></span>
                        <x-status-badge :status="$session->status" />
                    </a></li>
                @endforeach
            </ul>
        </section>
    @endif

    <h2 class="mb-3 text-xl">Pilih Bos</h2>
    @if ($fields->isNotEmpty())
        <nav class="chip-row mb-4" aria-label="Saring bidang">
            <a href="{{ route('warga.mentoring.index', array_filter(['catatan' => $noteId])) }}" @class(['chip', 'chip-active' => ! request('bidang')])>Semua bidang</a>
            @foreach ($fields as $field)<a href="{{ route('warga.mentoring.index', array_filter(['bidang' => $field->slug, 'catatan' => $noteId])) }}" @class(['chip', 'chip-active' => request('bidang') === $field->slug])>{{ $field->name }}</a>@endforeach
        </nav>
    @endif
    @if ($bosses->isEmpty())
        <x-empty-state icon="phosphor-user-circle" title="Belum ada Bos aktif di bidang ini">Coba bidang lain, atau tanyakan dulu ke Bos Allo di Ruang Bos.</x-empty-state>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($bosses as $boss)
                <x-boss-card :boss="$boss" :href="route('warga.mentoring.boss', array_filter(['boss' => $boss, 'catatan' => $noteId]))">
                    <span class="flex items-center justify-between text-sm"><span class="text-fg-muted">{{ $boss->open_slots }} slot tersedia</span><span class="btn btn-secondary btn-sm">Lihat Profil</span></span>
                </x-boss-card>
            @endforeach
        </div>
    @endif
</x-layouts.app>
