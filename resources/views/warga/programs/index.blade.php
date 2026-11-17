<x-layouts.app title="Kelas Saya">
    <x-page-header title="Kelas Saya" description="Kelas yang kamu ikuti, kehadiran, dan sertifikat.">
        <x-slot:actions><x-button variant="secondary" :href="route('programs.index')">Lihat Semua Kelas</x-button></x-slot:actions>
    </x-page-header>

    @if ($enrollments->isEmpty())
        <x-empty-state icon="phosphor-chalkboard-teacher" title="Belum ikut kelas" action="Lihat Kelas" :href="route('programs.index')">Daftar kelas dari pengusaha asli IPI. Hadir minimal 75 persen sesi untuk mendapat sertifikat.</x-empty-state>
    @else
        <div class="grid gap-4">
            @foreach ($enrollments as $item)
                @php($program = $item['program'])
                <article class="card space-y-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <a href="{{ route('programs.show', $program) }}" class="font-display text-xl font-semibold text-fg hover:underline">{{ $program->title }}</a>
                            <p class="text-sm text-fg-muted">{{ $program->location }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2"><x-status-badge :status="$item['enrollment']->status" /><x-status-badge :status="$program->status" /></div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm"><span class="text-fg-body">{{ $item['progress']['attended'] }} dari {{ $item['progress']['total'] }} sesi dihadiri, {{ $item['progress']['materials'] }} dari {{ $item['progress']['materials_total'] }} materi dibuka</span><span class="font-medium text-fg">{{ $item['progress']['percent'] }}%</span></div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-line"><div class="h-full rounded-full bg-accent-text" style="width: {{ $item['progress']['percent'] }}%"></div></div>
                    </div>
                    <ol class="grid gap-2">
                        @foreach ($program->sessions as $session)
                            <li class="flex flex-col gap-2 rounded-[20px] border border-line p-3 sm:flex-row sm:items-center sm:justify-between">
                                <span class="text-sm">
                                    <span class="block font-medium text-fg">{{ $session->title }}</span>
                                    <span class="text-fg-muted">{{ $session->starts_at->translatedFormat('d M, H:i') }} sampai {{ $session->ends_at->format('H:i') }} WIB</span>
                                </span>
                                @if (in_array($session->id, $item['attended'], true))
                                    <span class="badge badge-success"><x-phosphor-check class="h-3.5 w-3.5" aria-hidden="true" />Hadir</span>
                                @elseif ($session->isRunning())
                                    <form method="POST" action="{{ route('warga.programs.attend', $session) }}" class="flex flex-col gap-1">
                                        @csrf
                                        <div class="flex gap-2">
                                            <label for="kode-{{ $session->id }}" class="sr-only">Kode kehadiran {{ $session->title }}</label>
                                            <input id="kode-{{ $session->id }}" name="code" maxlength="12" autocomplete="off" class="field-input w-36 uppercase tracking-widest" placeholder="KODE" required>
                                            <x-button variant="primary" loading="Mencatat">Catat Hadir</x-button>
                                        </div>
                                        @error('code_'.$session->id)<p class="text-sm text-danger">{{ $message }}</p>@enderror
                                    </form>
                                @elseif ($session->ends_at->isPast())
                                    <span class="badge badge-neutral">Tidak hadir</span>
                                @else
                                    <span class="flex flex-col items-start gap-1 sm:items-end">
                                        <button class="btn btn-primary btn-sm" disabled>Catat Hadir</button>
                                        <span class="text-xs text-fg-muted">Aktif saat sesi berlangsung</span>
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                    @if (isset($certificates[$program->id]))
                        <x-button variant="secondary" :href="route('warga.certificates.show', $certificates[$program->id])">Lihat Sertifikat</x-button>
                    @endif
                </article>
            @endforeach
        </div>
    @endif

    @if ($open->isNotEmpty())
        <section class="mt-10">
            <h2 class="mb-4 text-2xl">Kelas yang dibuka</h2>
            <div class="grid gap-4 sm:grid-cols-2">@foreach ($open as $program)<x-program-card :program="$program" />@endforeach</div>
        </section>
    @endif
</x-layouts.app>
