<x-layouts.dashboard title="Kelas">
    <x-page-header title="Kelas" description="Program pelatihan IPI: seminar, workshop, bootcamp, dan mentoring.">
        <x-slot:actions><x-button variant="primary" :href="route('pengelola.programs.create')" icon="phosphor-plus">Buat Kelas</x-button></x-slot:actions>
    </x-page-header>
    <form method="GET" class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <nav class="chip-row" aria-label="Saring status">
            <a href="{{ route('pengelola.programs.index') }}" @class(['chip', 'chip-active' => ! $status])>Semua</a>
            @foreach (App\Enums\ProgramStatus::cases() as $case)
                <a href="{{ route('pengelola.programs.index', ['status' => $case->value]) }}" @class(['chip', 'chip-active' => $status === $case->value])>{{ $case->label() }}</a>
            @endforeach
        </nav>
        <div class="flex gap-2"><label for="cari" class="sr-only">Cari kelas</label><input id="cari" name="q" value="{{ request('q') }}" class="field-input" placeholder="Cari judul kelas"><x-button variant="secondary">Cari</x-button></div>
    </form>
    @if ($programs->isEmpty())
        <x-empty-state icon="phosphor-chalkboard-teacher" title="Belum ada kelas" action="Buat Kelas" :href="route('pengelola.programs.create')" />
    @else
        <x-table>
            <thead><tr><th>Kelas</th><th>Jenis</th><th>Mulai</th><th>Peserta</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($programs as $program)
                    <tr>
                        <td data-label="Kelas"><a href="{{ route('pengelola.programs.show', $program) }}" class="font-medium text-fg hover:underline">{{ $program->title }}</a> <x-demo-badge :show="$program->is_demo" /></td>
                        <td data-label="Jenis">{{ $program->kind->label() }}</td>
                        <td data-label="Mulai">{{ $program->startsAt()?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td data-label="Peserta">{{ $program->enrollments_count }} / {{ $program->quota }}</td>
                        <td data-label="Status"><x-status-badge :status="$program->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-6">{{ $programs->links() }}</div>
    @endif
</x-layouts.dashboard>
