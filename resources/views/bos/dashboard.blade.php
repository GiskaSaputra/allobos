<x-layouts.dashboard title="Ringkasan Bos">
    <x-page-header title="Ringkasan" description="Permintaan sesi, jadwal, dan sertifikat yang menunggu tanda tanganmu." />
    @if (! $boss)
        <x-empty-state icon="phosphor-identification-card" title="Lengkapi profil Bos dulu" action="Isi Profil Bos" :href="route('bos.profile.edit')">Profil diperiksa Pengelola IPI dengan empat standar kurasi sebelum kamu bisa membuka slot.</x-empty-state>
    @else
        @if ($boss->status !== App\Enums\BossStatus::Aktif)
            <p class="mb-6 rounded-[20px] bg-warning/15 p-4 text-sm text-fg">Status profil: <strong>{{ $boss->status->label() }}</strong>. {{ $boss->status === App\Enums\BossStatus::Ditolak ? 'Alasan: '.$boss->reject_reason.'. Perbaiki profil untuk mengajukan ulang.' : 'Kamu bisa membuka slot setelah Pengelola IPI mengaktifkan profilmu.' }}</p>
        @endif
        @foreach ($announcements as $a)<x-card class="mb-4 border-info/40"><p class="font-medium text-fg">{{ $a->title }}</p><p class="text-sm text-fg-body">{{ $a->body }}</p></x-card>@endforeach
        <div class="grid gap-4 sm:grid-cols-3">
            <x-card><p class="text-sm text-fg-muted">Permintaan menunggu</p><p class="font-display text-3xl font-bold text-fg">{{ $pending->count() }}</p></x-card>
            <x-card><p class="text-sm text-fg-muted">Sesi selesai</p><p class="font-display text-3xl font-bold text-fg">{{ $completed }}</p></x-card>
            <x-card><p class="text-sm text-fg-muted">Penilaian rata-rata</p><p class="font-display text-3xl font-bold text-fg">{{ $boss->rating_count ? number_format($boss->rating_avg, 1, ',', '') : '-' }}</p></x-card>
        </div>
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <x-card>
                <h2 class="text-xl">Permintaan sesi</h2>
                @forelse ($pending as $s)
                    <a href="{{ route('bos.sessions.show', $s) }}" class="mt-3 block rounded-2xl border border-line p-3 hover:bg-fg/5"><span class="block font-medium text-fg">{{ $s->user->name }}</span><span class="text-sm text-fg-muted">{{ $s->slot->starts_at->translatedFormat('l, d F H:i') }} WIB</span></a>
                @empty
                    <p class="mt-3 text-sm text-fg-muted">Tidak ada permintaan baru.</p>
                @endforelse
            </x-card>
            <x-card>
                <h2 class="text-xl">Jadwal mendatang</h2>
                @forelse ($upcoming as $s)
                    <a href="{{ route('bos.sessions.show', $s) }}" class="mt-3 block rounded-2xl border border-line p-3 hover:bg-fg/5"><span class="block font-medium text-fg">{{ $s->user->name }}</span><span class="text-sm text-fg-muted">{{ $s->slot->starts_at->translatedFormat('l, d F H:i') }} WIB</span></a>
                @empty
                    <p class="mt-3 text-sm text-fg-muted">Belum ada sesi terjadwal. <a href="{{ route('bos.slots.index') }}" class="text-accent-text underline">Buka slot</a>.</p>
                @endforelse
            </x-card>
        </div>
        @if ($toSign->isNotEmpty())
            <x-card tone="sorot" class="mt-6">
                <h2 class="text-xl">Sertifikat menunggu tanda tanganmu</h2>
                <ul class="mt-3 space-y-2">@foreach ($toSign as $signer)<li><a href="{{ route('signing.show', $signer->program) }}" class="text-accent-text underline underline-offset-2">{{ $signer->program->title }}</a></li>@endforeach</ul>
            </x-card>
        @endif
    @endif
</x-layouts.dashboard>
