<x-layouts.app title="Catatan Konsultasi">
    @if (! $session->note)
        @push('head')<meta http-equiv="refresh" content="4">@endpush
        <div class="mx-auto max-w-2xl space-y-4" aria-busy="true">
            <x-page-header title="Catatan Konsultasi" description="Bos Allo sedang menyusun catatan dari percakapan kalian. Halaman ini memuat ulang sendiri." />
            <p class="font-medium text-fg" role="status">Menyusun catatan</p>
            <div class="skeleton h-24"></div>
            <div class="skeleton h-40"></div>
            <div class="skeleton h-32"></div>
        </div>
    @else
        @php($note = $session->note)
        <div class="mx-auto max-w-3xl">
            <x-page-header title="Catatan Konsultasi" :description="'Topik: '.$session->topic.'. '.$session->created_at->translatedFormat('d F Y, H:i').' WIB.'">
                <x-slot:actions>
                    <x-button variant="secondary" :href="route('boss-room.note.pdf', $session)" icon="phosphor-download-simple">Unduh PDF</x-button>
                    @if (Route::has('warga.mentoring.index'))
                        <x-button variant="secondary" :href="route('warga.mentoring.index', ['catatan' => $note->id])">Ajukan ke Bos Asli</x-button>
                    @endif
                </x-slot:actions>
            </x-page-header>
            @unless ($note->is_ai)
                <p class="mb-4 rounded-[20px] bg-warning/15 p-4 text-sm text-warning">Layanan AI sedang tidak tersedia, jadi catatan ini disusun otomatis dari percakapan tanpa AI.</p>
            @endunless
            <div class="space-y-4">
                <x-card>
                    <h2 class="text-xl">Inti masalah</h2>
                    <p class="mt-2 text-fg-body">{{ $note->problem }}</p>
                </x-card>
                <x-card>
                    <h2 class="text-xl">Saran utama</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($note->advice as $item)
                            <li class="flex gap-3 text-fg-body"><x-phosphor-lightbulb class="mt-0.5 h-5 w-5 shrink-0 text-accent-text" aria-hidden="true" />{{ $item }}</li>
                        @endforeach
                    </ul>
                </x-card>
                <x-card tone="sorot">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <h2 class="text-xl">Langkah lanjut</h2>
                        <form method="POST" action="{{ route('boss-room.steps', $session) }}">
                            @csrf
                            <x-button variant="primary" :disabled="(bool) $note->steps_added_at" loading="Menambahkan">{{ $note->steps_added_at ? 'Sudah di Langkah 30 Hari' : 'Tambahkan ke Langkah 30 Hari' }}</x-button>
                        </form>
                    </div>
                    <ol class="mt-3 list-decimal space-y-2 pl-5 text-fg-body">
                        @foreach ($note->next_steps as $step)<li>{{ $step }}</li>@endforeach
                    </ol>
                </x-card>
                @if ($note->modules)
                    <x-card>
                        <h2 class="text-xl">Modul yang disarankan</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (App\Services\Boss\Modules::links($note->modules) as $module)
                                @if ($module['url'])<a href="{{ $module['url'] }}" class="chip">{{ $module['label'] }}</a>@endif
                            @endforeach
                        </div>
                    </x-card>
                @endif
            </div>
            <div class="mt-8 flex flex-wrap gap-2">
                <x-button variant="ghost" :href="route('boss-room.setup')">Mulai Sesi Baru</x-button>
                <x-button variant="ghost" :href="route('boss-room.history')">Riwayat Sesi</x-button>
            </div>
        </div>
    @endif
</x-layouts.app>
