<x-layouts.app title="Beranda">
    <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
        <div class="space-y-6">
            <section class="bg-hero relative isolate overflow-hidden rounded-[28px] p-5 sm:rounded-panel sm:p-8">
                <img src="{{ asset('image/pose-5.webp') }}" alt="" width="720" height="965" aria-hidden="true" class="pointer-events-none absolute -bottom-10 right-4 -z-10 hidden h-[125%] w-auto object-contain object-bottom md:block lg:right-10">
                <div class="max-w-xl rounded-[24px] md:max-w-[60%] bg-ink/45 p-5 text-ink-fg backdrop-blur-md sm:p-7">
                    <p class="text-sm text-ink-muted">Halo, {{ $user->firstName() }}. Langkah berikutnya:</p>
                    <h1 class="mt-2 text-2xl text-ink-fg sm:text-3xl">{{ $next['title'] }}</h1>
                    <p class="mt-2 text-ink-muted">{{ $next['body'] }}</p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @if (Route::has($next['route']))
                            <x-button variant="accent" :href="route($next['route'])">{{ $next['button'] }}</x-button>
                        @endif
                        <x-button variant="glass" :href="route('warga.journey')">Lihat Jejak Usaha</x-button>
                    </div>
                </div>
            </section>

            @foreach ($announcements as $announcement)
                <x-card class="border-info/40">
                    <p class="flex items-center gap-2 font-medium text-fg"><x-phosphor-megaphone class="h-5 w-5 text-info" aria-hidden="true" />{{ $announcement->title }}</p>
                    <p class="mt-1 text-sm text-fg-body">{{ $announcement->body }}</p>
                </x-card>
            @endforeach

            <section>
                <h2 class="mb-3 text-xl">Agenda</h2>
                @if ($agenda->isEmpty())
                    <x-empty-state icon="phosphor-calendar-blank" title="Belum ada agenda">Daftar kelas atau ajukan sesi Temu Bos supaya jadwalnya muncul di sini.
                        <x-slot:actions>@if (Route::has('warga.programs.index'))<x-button variant="secondary" :href="route('warga.programs.index')">Lihat Kelas</x-button>@endif</x-slot:actions>
                    </x-empty-state>
                @else
                    <ul class="grid gap-2">
                        @foreach ($agenda as $item)
                            <li>
                                <a href="{{ $item['url'] }}" class="card card-link flex items-center gap-4 p-4 sm:p-4">
                                    <span class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-2xl bg-accent/30 text-fg">
                                        <span class="font-display text-lg font-bold leading-none">{{ $item['when']->format('d') }}</span>
                                        <span class="text-xs">{{ $item['when']->translatedFormat('M') }}</span>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-medium text-fg">{{ $item['title'] }}</span>
                                        <span class="block text-sm text-fg-muted">{{ $item['kind'] }}, {{ $item['when']->translatedFormat('l H:i') }} WIB</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            @if ($podcasts->isNotEmpty())
                <section>
                    <h2 class="mb-3 text-xl">Siniar untuk kamu</h2>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($podcasts as $podcast)
                            <x-podcast-card :podcast="$podcast" compact />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-6">
            <x-card>
                <h2 class="mb-4 text-center text-xl">Skor Siap Usaha</h2>
                <x-score-ring :score="$score" :level="$level" :breakdown="$breakdown" />
            </x-card>
            <x-card>
                <div class="flex items-center justify-between"><h2 class="text-xl">Terakhir dikerjakan</h2></div>
                @if ($recent->isEmpty())
                    <p class="mt-3 text-sm text-fg-muted">Belum ada aktivitas.</p>
                @else
                    <ul class="mt-3 space-y-3">
                        @foreach ($recent as $activity)
                            <li class="text-sm"><span class="block text-fg">{{ $activity->description }}</span><span class="text-fg-muted">{{ $activity->created_at->diffForHumans() }}</span></li>
                        @endforeach
                    </ul>
                @endif
                <x-button variant="secondary" :href="route('warga.journey')" full class="mt-4">Lihat Jejak Usaha</x-button>
            </x-card>
            <a href="{{ route('warga.onboarding.show', 1) }}" class="block text-center text-sm text-fg-muted underline underline-offset-2 hover:text-fg">Ubah Profil Usaha</a>
        </aside>
    </div>
</x-layouts.app>
