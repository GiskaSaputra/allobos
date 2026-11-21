<x-layouts.public>
    @php
        $tanyaUrl = auth()->check() ? route('boss-room.setup') : route('register', ['tujuan' => '/ruang-bos']);
    @endphp
    <section class="container-page pt-4">
        <div class="bg-hero relative isolate overflow-hidden rounded-[28px] px-5 pt-8 sm:rounded-panel sm:px-10 sm:pt-12">
            <div aria-hidden="true" class="pointer-events-none absolute -right-24 top-10 -z-10 h-[520px] w-[520px] rounded-full bg-accent/45 blur-3xl"></div>
            <div class="grid gap-6 lg:grid-cols-[1.1fr_1fr] lg:gap-10">
                <div class="space-y-6 lg:self-center lg:pb-12">
                    <p class="inline-flex items-center gap-2 rounded-full bg-ink/25 px-3 py-1.5 text-sm font-medium text-ink-fg backdrop-blur-md"><x-phosphor-storefront class="h-4 w-4" aria-hidden="true" />Pendamping usaha warga Gunungkidul</p>
                    <h1 class="w-fit rounded-[24px] bg-ink/40 px-5 py-4 font-display text-[40px] font-semibold leading-[1.05] tracking-tight text-ink-fg backdrop-blur-md sm:text-6xl lg:text-[68px]" aria-label="Tanya bos, belajar, lalu buka usaha.">
                        <span aria-hidden="true">@foreach (['Tanya', 'bos,', 'belajar,', 'lalu'] as $i => $word)<span data-word style="--i: {{ $i }}">{{ $word }}</span> @endforeach<em class="font-bold text-accent"><span data-word style="--i: 4">buka</span> <span data-word style="--i: 5">usaha.</span></em></span>
                    </h1>
                    <p class="max-w-md text-base text-ink sm:text-lg">Bos Allo menjawab pertanyaan usahamu dengan suara, pengusaha asli IPI mendampingi, dan lapakmu bisa langsung dipesan.</p>
                    <div class="flex flex-wrap gap-3">
                        <x-button variant="accent" size="lg" :href="$tanyaUrl" icon="phosphor-chat-circle-dots">Tanya Bos Allo</x-button>
                        <x-button variant="glass" size="lg" :href="route('programs.index')">Lihat Kelas</x-button>
                    </div>
                </div>
                <div class="relative mx-auto h-[380px] w-full max-w-[460px] self-end sm:h-[460px] lg:mx-0 lg:h-[560px] lg:max-w-none xl:h-[620px]">
                    <img src="{{ asset('image/pose-3.webp') }}" alt="Bos Allo, maskot alloBos berjas, mempersilakan dengan tangan terbuka" width="720" height="965" fetchpriority="high" class="pose-rise absolute bottom-0 left-1/2 h-full w-auto max-w-none -translate-x-1/2 object-contain object-bottom drop-shadow-[0_30px_40px_rgb(var(--ink)/0.35)]">
                    <div class="hero-float absolute left-0 top-[42%] z-10 max-w-[170px] rounded-[20px] rounded-br-md border border-ink-fg/30 bg-ink/45 p-3 text-sm text-ink-fg shadow-float backdrop-blur-md sm:max-w-[210px] lg:-left-4 lg:top-[30%] xl:max-w-[230px]" style="--i: 0">
                        <p class="text-xs text-ink-muted">Bos Allo</p>
                        <p class="font-medium">Mau mulai usaha apa? Ceritakan saja, aku dengarkan.</p>
                    </div>
                    <div class="hero-float absolute bottom-8 right-0 flex items-center gap-3 rounded-full border border-ink-fg/30 bg-surface-raised/85 py-2 pl-2 pr-4 text-sm text-fg shadow-float backdrop-blur-md" style="--i: 1">
                        <span class="grid h-9 w-9 place-items-center rounded-full bg-brand text-brand-fg"><x-phosphor-microphone class="h-5 w-5" aria-hidden="true" /></span>
                        <span><span class="block font-semibold">Suara atau teks</span><span class="text-xs text-fg-muted">Bahasa sehari-hari</span></span>
                    </div>
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
            <x-boss-avatar clip="bicara" class="border-ink-fg/15" />
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

    <section class="container-page mt-20" aria-labelledby="judul-warung">
        @php($warungUrl = match (true) { auth()->user()?->active_role === App\Enums\RoleName::Warga => route('game.index'), auth()->check() => route('dashboard'), default => route('register', ['tujuan' => '/warung']) })
        {{-- Ponsel: gambar di atas, teks di bawah. Layar lebar: teks menumpang di sisi kiri gambar. --}}
        <a href="{{ $warungUrl }}" class="group relative isolate grid overflow-hidden rounded-[28px] border border-line bg-ink shadow-card sm:rounded-panel md:min-h-[480px]" data-reveal>
            <div class="relative aspect-[16/9] overflow-hidden md:absolute md:inset-0 md:-z-10 md:aspect-auto">
                <img src="{{ asset('image/warung-3d.jpg') }}" alt="Bagian dalam Warung Bos 3D: etalase berisi keripik, tiwul, gethuk, dan es teh, papan harga di dinding, dan tanda Buka di meja depan" width="1280" height="608" loading="lazy" class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-[1.03] md:object-[75%_center]">
                <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-ink to-transparent md:inset-0 md:h-auto md:bg-gradient-to-r md:from-ink md:from-20% md:via-ink/50 md:via-45% md:to-transparent md:to-70%"></div>
            </div>
            <div class="flex max-w-xl flex-col gap-4 p-6 pt-2 sm:p-10 md:justify-center">
                <p class="inline-flex w-fit items-center gap-2 rounded-full bg-accent px-3 py-1.5 text-sm font-semibold text-accent-fg"><x-phosphor-cube class="h-4 w-4" aria-hidden="true" />Simulasi 3D</p>
                <h2 id="judul-warung" class="text-3xl text-ink-fg sm:text-5xl">Latihan jaga warung di <em class="font-bold text-accent">Warung Bos 3D</em></h2>
                <p class="text-ink-muted sm:text-lg">Kamu mewarisi warung simbah yang hampir tutup. Belanja ke pemasok, pajang barang di rak, atur harga, lalu layani pembeli selama tujuh hari. Salah hitung di sini tidak merugikan modal sungguhan.</p>
                <ul class="flex flex-wrap gap-2 text-sm text-ink-fg">
                    @foreach (['Tujuh babak', 'Bisa dari ponsel', 'Masuk Skor Siap Usaha'] as $point)
                        <li class="rounded-full border border-ink-fg/25 bg-ink/50 px-3 py-1">{{ $point }}</li>
                    @endforeach
                </ul>
                <span class="btn btn-accent btn-lg mt-2 w-fit">Masuk ke Warung<x-phosphor-arrow-right class="h-5 w-5 transition group-hover:translate-x-1" aria-hidden="true" /></span>
            </div>
        </a>
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

    {{-- Subgrid menyejajarkan judul, keterangan, kartu, dan tombol kedua kolom walau panjang teksnya berbeda --}}
    <section class="container-page mt-20 grid gap-x-10 gap-y-12 lg:grid-cols-2 lg:grid-rows-[auto_auto_1fr_auto]">
        @foreach ([
            ['Siniar Bos', 'Obrolan bersama pengusaha, lengkap dengan ringkasan dan tiga pelajaran.', $podcasts->take(2), 'podcast', route('podcasts.index'), 'Lihat Semua Siniar'],
            ['Lapak warga', 'Produk buatan warga Gunungkidul. Pesan langsung ke penjual lewat WhatsApp.', $shops->take(2), 'shop', route('shops.index'), 'Lihat Semua Lapak'],
        ] as $i => [$heading, $intro, $items, $type, $url, $cta])
            <div class="grid gap-y-5 lg:row-span-4 lg:grid-rows-subgrid" data-reveal style="--i: {{ $i }}">
                <h2 class="text-3xl">{{ $heading }}</h2>
                <p class="-mt-3 text-fg-muted">{{ $intro }}</p>
                <div class="grid content-start gap-3 sm:grid-cols-2">
                    @foreach ($items as $item)
                        @if ($type === 'podcast')<x-podcast-card :podcast="$item" compact />@else<x-shop-card :shop="$item" />@endif
                    @endforeach
                </div>
                <x-button variant="secondary" :href="$url" class="w-fit">{{ $cta }}</x-button>
            </div>
        @endforeach
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
        <div class="bg-hero relative flex min-h-[340px] flex-col justify-between gap-6 overflow-hidden rounded-[28px] p-6 sm:rounded-panel sm:p-10" data-reveal style="--i: 1">
            <div class="relative z-10 max-w-[62%] space-y-3 sm:max-w-[58%]">
                <h2 class="text-3xl text-ink">Kamu pengusaha?</h2>
                <p class="text-ink">Dampingi warga yang baru mulai. Atur slot sendiri, catatan sesi tersusun rapi, dan lihat dampaknya.</p>
            </div>
            <x-button variant="secondary" :href="route('become-boss')" class="relative z-10 w-fit">Jadi Bos Pendamping</x-button>
            <img src="{{ asset('image/pose-4.webp') }}" alt="" width="720" height="965" loading="lazy" class="pointer-events-none absolute -bottom-2 -right-10 h-[92%] w-auto max-w-none object-contain object-bottom sm:-right-4">
        </div>
    </section>

    <section class="container-page mt-20">
        <div class="panel-ink p-4 sm:p-6" data-reveal>
            <div class="bg-mint relative grid overflow-hidden rounded-[24px] sm:rounded-[32px] md:grid-cols-[1fr_auto]">
                <div class="flex flex-col items-start gap-5 px-6 py-12 sm:px-12 sm:py-16">
                    <h2 class="max-w-xl text-4xl text-ink sm:text-5xl">Mulai dari <em class="font-bold">satu pertanyaan.</em></h2>
                    <p class="max-w-md text-ink">Ceritakan usahamu ke Bos Allo. Gratis untuk warga Gunungkidul.</p>
                    <x-button variant="primary" size="lg" :href="$tanyaUrl" icon="phosphor-chat-circle-dots">Tanya Bos Allo</x-button>
                </div>
                <img src="{{ asset('image/pose-6.webp') }}" alt="" width="720" height="965" loading="lazy" class="mx-auto -mb-1 h-[280px] w-auto self-end object-contain object-bottom sm:h-[360px] md:mr-10">
            </div>
        </div>
    </section>

</x-layouts.public>
