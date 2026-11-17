<x-layouts.dashboard title="Ringkasan Pengelola">
    <x-page-header title="Ringkasan" description="Yang perlu ditangani hari ini dan program yang berjalan.">
        <x-slot:actions><x-button variant="primary" :href="route('pengelola.programs.create')" icon="phosphor-plus">Buat Kelas</x-button></x-slot:actions>
    </x-page-header>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as [$label, $value, $route])
            <x-card><p class="text-sm text-fg-muted">{{ $label }}</p><p class="font-display text-3xl font-bold text-fg">{{ number_format($value, 0, ',', '.') }}</p></x-card>
        @endforeach
    </div>
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-card>
            <h2 class="text-xl">Perlu ditangani</h2>
            @if (empty($todo))
                <p class="mt-3 text-sm text-fg-muted">Tidak ada yang menunggu. Semua beres.</p>
            @else
                <ul class="mt-3 space-y-2">
                    @foreach ($todo as [$label, $count, $url])
                        <li><a href="{{ $url }}" class="flex items-center justify-between rounded-2xl border border-line p-3 hover:bg-fg/5"><span class="text-fg">{{ $label }}</span><span class="badge badge-warning">{{ $count }}</span></a></li>
                    @endforeach
                </ul>
            @endif
        </x-card>
        <x-card>
            <h2 class="text-xl">Kelas berjalan</h2>
            @forelse ($programs as $program)
                <a href="{{ route('pengelola.programs.show', $program) }}" class="mt-3 flex items-center justify-between rounded-2xl border border-line p-3 hover:bg-fg/5"><span class="truncate text-fg">{{ $program->title }}</span><x-status-badge :status="$program->status" /></a>
            @empty
                <p class="mt-3 text-sm text-fg-muted">Belum ada kelas yang dibuka.</p>
            @endforelse
        </x-card>
    </div>
</x-layouts.dashboard>
