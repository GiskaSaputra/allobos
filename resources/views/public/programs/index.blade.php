<x-layouts.public title="Kelas Bos" description="Seminar, workshop, bootcamp, dan mentoring dari Institusi Pengusaha Indonesia untuk warga Gunungkidul.">
    <section class="container-page pt-6">
        <h1 class="text-4xl sm:text-5xl">Kelas Bos</h1>
        <p class="mt-3 max-w-xl text-fg-muted">Pelatihan langsung dari pengusaha aktif. Hadir minimal 75 persen sesi untuk mendapat Sertifikat Terverifikasi.</p>

        <div class="mt-8 space-y-3">
            <nav class="chip-row" aria-label="Arsip">
                <a href="{{ route('programs.index', request()->except('arsip')) }}" @class(['chip', 'chip-active' => ! $archive])>Akan datang</a>
                <a href="{{ route('programs.index', ['arsip' => 1] + request()->except('arsip')) }}" @class(['chip', 'chip-active' => $archive])>Arsip</a>
            </nav>
            <nav class="chip-row" aria-label="Saring jenis">
                <a href="{{ route('programs.index', request()->except('jenis')) }}" @class(['chip', 'chip-active' => ! request('jenis')])>Semua jenis</a>
                @foreach (App\Enums\ProgramKind::cases() as $kind)
                    <a href="{{ route('programs.index', ['jenis' => $kind->value] + request()->except('jenis')) }}" @class(['chip', 'chip-active' => request('jenis') === $kind->value])>{{ $kind->label() }}</a>
                @endforeach
            </nav>
            @if ($fields->isNotEmpty())
                <nav class="chip-row" aria-label="Saring bidang">
                    <a href="{{ route('programs.index', request()->except('bidang')) }}" @class(['chip', 'chip-active' => ! request('bidang')])>Semua bidang</a>
                    @foreach ($fields as $field)
                        <a href="{{ route('programs.index', ['bidang' => $field->slug] + request()->except('bidang')) }}" @class(['chip', 'chip-active' => request('bidang') === $field->slug])>{{ $field->name }}</a>
                    @endforeach
                </nav>
            @endif
        </div>

        @if ($programs->isEmpty())
            <x-empty-state icon="phosphor-chalkboard-teacher" title="{{ $archive ? 'Belum ada kelas di arsip' : 'Belum ada kelas yang dibuka' }}" class="mt-8">Coba saringan lain, atau kembali lagi nanti.</x-empty-state>
        @else
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($programs as $program)<x-program-card :program="$program" />@endforeach
            </div>
        @endif
    </section>
</x-layouts.public>
