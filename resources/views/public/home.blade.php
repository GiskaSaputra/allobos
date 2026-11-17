<x-layouts.public>
    @php
        $tanyaUrl = auth()->check() ? route('boss-room.setup') : route('register', ['tujuan' => '/ruang-bos']);
    @endphp
    <section class="container-page pt-4">
        <div class="bg-hero relative overflow-hidden rounded-[28px] px-5 pb-6 pt-8 sm:rounded-panel sm:px-10 sm:pb-10 sm:pt-12">
            <div class="grid items-end gap-8 lg:grid-cols-[1.25fr_1fr]">
                <div class="space-y-6">
                    <h1 class="w-fit rounded-[24px] bg-ink/40 px-5 py-4 font-display text-[40px] font-semibold leading-[1.05] tracking-tight text-ink-fg backdrop-blur-md sm:text-6xl lg:text-[68px]" aria-label="Tanya bos, belajar, lalu buka usaha.">
                        <span aria-hidden="true">@foreach (['Tanya', 'bos,', 'belajar,', 'lalu'] as $i => $word)<span data-word style="--i: {{ $i }}">{{ $word }}</span> @endforeach<em class="font-bold"><span data-word style="--i: 4">buka</span> <span data-word style="--i: 5">usaha.</span></em></span>
                    </h1>
                    <p class="max-w-md text-base text-ink sm:text-lg">Bos Allo menjawab pertanyaan usahamu dengan suara, pengusaha asli IPI mendampingi, dan lapakmu bisa langsung dipesan.</p>
                    <div class="flex flex-wrap gap-3">
                        <x-button variant="accent" size="lg" :href="$tanyaUrl" icon="phosphor-chat-circle-dots">Tanya Bos Allo</x-button>
                        <x-button variant="glass" size="lg" :href="route('programs.index')">Lihat Kelas</x-button>
                    </div>
                </div>
                <div class="overflow-hidden rounded-card border border-ink-fg/25 shadow-float">
                    <img src="{{ asset('gif/bos-allo/allo-diam.gif') }}" alt="Bos Allo tersenyum di meja kerja dengan laptop" width="400" height="225" class="block aspect-video w-full object-cover motion-reduce:hidden" fetchpriority="high">
                    <img src="{{ asset('gif/bos-allo/allo-transparan.webp') }}" alt="Bos Allo di meja kerja" width="400" height="225" class="hidden aspect-video w-full object-cover motion-reduce:block">
                </div>
            </div>
        </div>
    </section>

    @if (array_sum($stats) > 0)
        <section class="container-page mt-6" aria-label="Angka alloBos">
            <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach (['kelas' => 'Kelas Bos', 'lapak' => 'Lapak terbit', 'bos' => 'Bos pendamping', 'sertifikat' => 'Sertifikat tercatat'] as $key => $label)
                    <div class="rounded-card border border-line bg-surface-raised px-5 py-4"><dt class="text-sm text-fg-muted">{{ $label }}</dt><dd class="font-display text-3xl font-bold text-fg">{{ number_format($stats[$key], 0, ',', '.') }}</dd></div>
                @endforeach
            </dl>
        </section>
    @endif

    <section class="mt-16 overflow-hidden border-y border-line py-5" aria-label="Bidang usaha yang didampingi">
        <div class="marquee gap-3" tabindex="0">
            @foreach ([...$fields, ...$fields] as $i => $field)
                <span class="shrink-0 rounded-full border border-line bg-surface-raised px-5 py-2 font-display text-lg font-semibold text-fg" @if ($i >= count($fields)) aria-hidden="true" @endif>{{ $field }}</span>
            @endforeach
        </div>
    </section>

    <section class="container-page mt-20">
        <h2 class="max-w-2xl text-3xl sm:text-5xl" data-reveal>Satu perjalanan, dari bertanya sampai <em class="font-bold">membuka lapak</em>.</h2>
        @php
            $stages = [
                ['Kenali', 'Jawab lima pertanyaan singkat: kapanewon, modal, waktu luang, bidang minat, dan keahlian. Semua modul memakai profil ini.', 'phosphor-identification-card', 'bg-surface-raised', 'Profil Usaha'],
                ['Tanya', 'Bicara dengan Bos Allo lewat suara atau teks. Bila perlu, pesan sesi dengan pengusaha asli yang dikurasi IPI.', 'phosphor-chat-circle-dots', 'bg-hero', 'Ruang Bos, Temu Bos'],
                ['Belajar', 'Ikut kelas nyata, dengarkan siniar, dan latihan menjalankan warung tujuh hari di simulasi 3D.', 'phosphor-book-open', 'panel-ink', 'Kelas Bos, Siniar Bos, Warung Bos 3D'],
                ['Rancang', 'Temukan tiga peluang usaha, susun rencana satu halaman, hitung HPP, dan periksa foto produk.', 'phosphor-compass', 'bg-mint', 'Peluang, Rencana, Cek Produk'],
                ['Buka', 'Pajang produk di lapak gratis. Pembeli memesan langsung lewat WhatsApp-mu.', 'phosphor-storefront', 'bg-surface-alt', 'Lapak'],
            ];
        @endphp
        <ol class="mt-10 space-y-6">
            @foreach ($stages as $i => [$title, $body, $icon, $bg, $modules])
                <li data-stack-card class="sticky" style="top: {{ 88 + $i * 16 }}px">
                    <article @class(['grid min-h-[260px] gap-6 rounded-[28px] border border-line p-6 shadow-card sm:rounded-panel sm:p-10 md:grid-cols-[auto_1fr_auto] md:items-center', $bg, 'text-ink-fg' => $bg === 'panel-ink'])>
                        <span @class(['font-display text-6xl font-bold leading-none sm:text-8xl', 'text-accent' => $bg === 'panel-ink', 'text-ink/80' => in_array($bg, ['bg-hero', 'bg-mint'], true), 'text-accent-text' => ! in_array($bg, ['panel-ink', 'bg-hero', 'bg-mint'], true)])>{{ $i + 1 }}</span>
                        <div class="space-y-2">
                            <h3 @class(['text-3xl sm:text-4xl', 'text-ink-fg' => $bg === 'panel-ink', 'text-ink' => in_array($bg, ['bg-hero', 'bg-mint'], true)])>{{ $title }}</h3>
                            <p @class(['max-w-xl text-lg', 'text-ink-muted' => $bg === 'panel-ink', 'text-ink' => in_array($bg, ['bg-hero', 'bg-mint'], true), 'text-fg-body' => ! in_array($bg, ['panel-ink', 'bg-hero', 'bg-mint'], true)])>{{ $body }}</p>
                            <p @class(['text-sm font-medium', 'text-ink-muted' => $bg === 'panel-ink', 'text-ink/70' => in_array($bg, ['bg-hero', 'bg-mint'], true), 'text-fg-muted' => ! in_array($bg, ['panel-ink', 'bg-hero', 'bg-mint'], true)])>{{ $modules }}</p>
                        </div>
                        <x-dynamic-component :component="$icon" @class(['hidden h-20 w-20 md:block', 'text-accent' => $bg === 'panel-ink', 'text-ink/60' => $bg !== 'panel-ink']) aria-hidden="true" />
                    </article>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="container-page mt-20">
        <div class="panel-ink grid gap-8 overflow-hidden p-6 sm:p-10 lg:grid-cols-[1fr_1.1fr] lg:items-center" data-reveal>
            <div class="overflow-hidden rounded-card border border-ink-fg/15">
                <img src="{{ asset('gif/bos-allo/allo-bicara.gif') }}" alt="Bos Allo sedang menjelaskan" width="400" height="225" loading="lazy" class="block aspect-video w-full object-cover motion-reduce:hidden">
                <img src="{{ asset('gif/bos-allo/allo-transparan.webp') }}" alt="Bos Allo" width="400" height="225" loading="lazy" class="hidden aspect-video w-full object-cover motion-reduce:block">
            </div>
            <div class="space-y-4">
                <h2 class="text-3xl text-ink-fg sm:text-5xl">Bicara saja, <em class="font-bold">Bos Allo</em> mendengar.</h2>
                <p class="max-w-lg text-lg text-ink-muted">Tidak perlu mengetik panjang. Tahan tombol mikrofon, ceritakan usahamu dengan bahasa sehari-hari, dan dengarkan jawabannya. Setiap sesi ditutup dengan catatan dan langkah yang bisa langsung dikerjakan.</p>
                <ul class="grid gap-2 text-ink-fg sm:grid-cols-2">
                    @foreach (['Mengenal modal dan kapanewonmu', 'Jawaban dibacakan dan ditulis', 'Catatan Konsultasi bisa diunduh', 'Soal hukum dan pinjaman diarahkan ke ahlinya'] as $point)
                        <li class="flex gap-2 text-sm"><x-phosphor-check-circle class="mt-0.5 h-5 w-5 shrink-0 text-accent" aria-hidden="true" />{{ $point }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    @if ($programs->isNotEmpty())
        <section class="container-page mt-20">
            <h2 class="text-3xl sm:text-4xl" data-reveal>Kelas dari pengusaha asli</h2>
            <div @class(['mt-6 grid gap-4', 'md:grid-cols-3' => $programs->count() >= 3, 'md:grid-cols-2' => $programs->count() === 2])>
                @foreach ($programs as $i => $program)<div data-reveal style="--i: {{ $i }}"><x-program-card :program="$program" /></div>@endforeach
            </div>
            <x-button variant="secondary" :href="route('programs.index')" class="mt-6">Lihat Semua Kelas</x-button>
        </section>
    @endif

    <section class="container-page mt-20 grid gap-10 lg:grid-cols-2">
        <div data-reveal>
            <h2 class="text-3xl">Siniar Bos</h2>
            <p class="mt-2 text-fg-muted">Obrolan bersama pengusaha, lengkap dengan ringkasan dan tiga pelajaran.</p>
            @if ($podcasts->isNotEmpty())
                <div class="mt-5 grid gap-3 sm:grid-cols-2">@foreach ($podcasts->take(2) as $podcast)<x-podcast-card :podcast="$podcast" compact />@endforeach</div>
            @endif
            <x-button variant="secondary" :href="route('podcasts.index')" class="mt-5">Lihat Semua Siniar</x-button>
        </div>
        <div data-reveal style="--i: 1">
            <h2 class="text-3xl">Lapak warga</h2>
            <p class="mt-2 text-fg-muted">Produk buatan warga Gunungkidul. Pesan langsung ke penjual lewat WhatsApp.</p>
            @if ($shops->isNotEmpty())
                <div class="mt-5 grid gap-3 sm:grid-cols-2">@foreach ($shops->take(2) as $shop)<x-shop-card :shop="$shop" />@endforeach</div>
            @endif
            <x-button variant="secondary" :href="route('shops.index')" class="mt-5">Lihat Semua Lapak</x-button>
        </div>
    </section>

    <section class="container-page mt-20 grid gap-6 md:grid-cols-[1.2fr_1fr]">
        <div class="card flex flex-col justify-between gap-6 p-6 sm:p-10" data-reveal>
            <div class="space-y-3">
                <x-phosphor-seal-check class="h-12 w-12 text-success" aria-hidden="true" />
                <h2 class="text-3xl">Sertifikat yang bisa diperiksa siapa saja</h2>
                <p class="max-w-lg text-fg-body">Sertifikat kelas ditandatangani pembicara dan sidik digitalnya dicatat di blockchain, catatan publik yang tidak bisa diubah. Calon mitra atau pemberi modal cukup memindai kode QR.</p>
            </div>
            <a href="{{ route('certificates.verify.form') }}" class="w-fit font-medium text-accent-text underline underline-offset-4">Verifikasi Sertifikat</a>
        </div>
        <div class="bg-hero flex flex-col justify-between gap-6 rounded-[28px] p-6 sm:rounded-panel sm:p-10" data-reveal style="--i: 1">
            <div class="space-y-3">
                <h2 class="text-3xl text-ink">Kamu pengusaha?</h2>
                <p class="text-ink">Dampingi warga yang baru mulai. Atur slot sendiri, catatan sesi tersusun rapi, dan lihat dampaknya.</p>
            </div>
            <x-button variant="secondary" :href="route('become-boss')" class="w-fit">Jadi Bos Pendamping</x-button>
        </div>
    </section>

    <section class="container-page mt-20">
        <div class="panel-ink p-4 sm:p-6" data-reveal>
            <div class="bg-mint flex flex-col items-center gap-5 rounded-[24px] px-6 py-14 text-center sm:rounded-[32px]">
                <h2 class="max-w-xl text-4xl text-ink sm:text-5xl">Mulai dari satu pertanyaan.</h2>
                <p class="max-w-md text-ink">Ceritakan usahamu ke Bos Allo. Gratis untuk warga Gunungkidul.</p>
                <x-button variant="primary" size="lg" :href="$tanyaUrl" icon="phosphor-chat-circle-dots">Tanya Bos Allo</x-button>
            </div>
        </div>
    </section>

    @push('scripts')
        @vite('resources/js/home-scroll.js')
    @endpush
</x-layouts.public>
