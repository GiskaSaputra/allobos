<x-layouts.public>
    <section class="container-page pt-4">
        <div class="bg-hero relative overflow-hidden rounded-[28px] px-5 pb-6 pt-8 sm:rounded-panel sm:px-10 sm:pb-10 sm:pt-12">
            <div class="grid items-end gap-8 lg:grid-cols-[1.25fr_1fr]">
                <div class="space-y-6">
                    <h1 class="w-fit rounded-[24px] bg-ink/40 px-5 py-4 font-display text-[40px] font-semibold leading-[1.05] tracking-tight text-ink-fg backdrop-blur-md sm:text-6xl lg:text-[68px]" aria-label="Tanya bos, belajar, lalu buka usaha.">
                        <span aria-hidden="true">@foreach (['Tanya', 'bos,', 'belajar,', 'lalu'] as $i => $word)<span data-word style="--i: {{ $i }}">{{ $word }}</span> @endforeach<em class="font-bold"><span data-word style="--i: 4">buka</span> <span data-word style="--i: 5">usaha.</span></em></span>
                    </h1>
                    <p class="max-w-md text-base text-ink sm:text-lg">Bos Allo menjawab pertanyaan usahamu dengan suara, pengusaha asli IPI mendampingi, dan lapakmu bisa langsung dipesan.</p>
                    <div class="flex flex-wrap gap-3">
                        <x-button variant="accent" size="lg" :href="auth()->check() ? (Route::has('boss-room.setup') ? route('boss-room.setup') : route('dashboard')) : route('register', ['tujuan' => '/ruang-bos'])" icon="phosphor-chat-circle-dots">Tanya Bos Allo</x-button>
                        <x-button variant="glass" size="lg" :href="Route::has('programs.index') ? route('programs.index') : '#'">Lihat Kelas</x-button>
                    </div>
                </div>
                <div class="overflow-hidden rounded-card border border-ink-fg/25 shadow-float">
                    <img src="{{ asset('gif/bos-allo/allo-diam.gif') }}" alt="Bos Allo tersenyum di meja kerja dengan laptop" width="400" height="225" class="block aspect-video w-full object-cover">
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        @vite('resources/js/home-scroll.js')
    @endpush
</x-layouts.public>
