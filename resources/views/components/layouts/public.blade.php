@props(['title' => null, 'description' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    @livewireStyles
    @stack('head')
</head>
<body class="bg-surface" data-motion>
    <a href="#isi" class="sr-only z-flash rounded-full bg-brand px-4 py-2 text-brand-fg focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Lewati ke isi</a>
    <div id="nav-sentinel" class="absolute top-0 h-6 w-px" aria-hidden="true"></div>

    <header data-nav class="group/nav sticky top-0 z-nav transition-all duration-300 ease-out data-[scrolled]:bg-surface/85 data-[scrolled]:shadow-card data-[scrolled]:backdrop-blur-md" x-data="{ open: false }" @keydown.escape.window="open = false">
        <div class="container-page flex h-[72px] items-center justify-between gap-4 transition-all duration-300 group-data-[scrolled]/nav:h-16">
            <a href="{{ route('home') }}" aria-label="alloBos, ke beranda"><x-logo /></a>

            <nav aria-label="Navigasi utama" class="hidden rounded-full border border-line bg-surface-raised/70 p-1 lg:flex">
                @foreach ([['Kelas', 'programs.index'], ['Siniar', 'podcasts.index'], ['Lapak', 'shops.index'], ['Bos', 'bosses.index'], ['Tentang', 'pages.tentang']] as [$label, $route])
                    @if (Route::has($route))
                        <a href="{{ route($route) }}" @class(['rounded-full px-4 py-2 text-sm font-medium transition duration-200', 'bg-brand text-brand-fg' => request()->routeIs($route.'*'), 'text-fg-body hover:bg-fg/10' => ! request()->routeIs($route.'*')])>{{ $label }}</a>
                    @endif
                @endforeach
            </nav>

            <div class="flex items-center gap-1 sm:gap-2">
                <x-theme-toggle />
                @auth
                    <x-button variant="primary" :href="route('dashboard')" class="hidden sm:inline-flex">Buka Dashboard</x-button>
                @else
                    <x-button variant="ghost" :href="route('login')" class="hidden sm:inline-flex">Masuk</x-button>
                    <x-button variant="primary" :href="route('register')" class="hidden sm:inline-flex">Daftar</x-button>
                @endauth
                <button type="button" class="btn btn-ghost btn-icon lg:hidden" @click="open = true" aria-label="Buka menu" :aria-expanded="open">
                    <x-phosphor-list class="h-6 w-6" aria-hidden="true" />
                </button>
            </div>
        </div>

        <div x-cloak x-show="open" class="fixed inset-0 z-drawer bg-ink/50 lg:hidden" @click="open = false" x-transition.opacity></div>
        <div x-cloak x-show="open" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:leave="transition duration-200" x-transition:leave-end="translate-x-full"
             class="fixed inset-y-0 right-0 z-drawer flex w-[min(86vw,340px)] flex-col gap-2 bg-surface-raised p-5 shadow-float lg:hidden" role="dialog" aria-label="Menu">
            <div class="mb-4 flex items-center justify-between">
                <x-logo />
                <button type="button" class="btn btn-ghost btn-icon" @click="open = false" aria-label="Tutup menu"><x-phosphor-x class="h-6 w-6" aria-hidden="true" /></button>
            </div>
            @foreach ([['Kelas', 'programs.index'], ['Siniar', 'podcasts.index'], ['Lapak', 'shops.index'], ['Bos', 'bosses.index'], ['Tentang', 'pages.tentang'], ['Verifikasi Sertifikat', 'certificates.verify.form']] as [$label, $route])
                @if (Route::has($route))
                    <a href="{{ route($route) }}" class="rounded-2xl px-4 py-3 font-medium text-fg hover:bg-fg/10">{{ $label }}</a>
                @endif
            @endforeach
            <div class="mt-auto grid gap-2">
                @auth
                    <x-button variant="primary" :href="route('dashboard')" full>Buka Dashboard</x-button>
                @else
                    <x-button variant="secondary" :href="route('login')" full>Masuk</x-button>
                    <x-button variant="primary" :href="route('register')" full>Daftar</x-button>
                @endauth
            </div>
        </div>
    </header>

    <x-flash />

    <main id="isi">{{ $slot }}</main>

    <footer class="mt-24 border-t border-line">
        <div class="container-page grid gap-10 py-14 md:grid-cols-[1.4fr_1fr_1fr]">
            <div class="max-w-sm space-y-3">
                <x-logo />
                <p class="text-sm text-fg-muted">Pendamping usaha untuk warga Gunungkidul. Dijalankan bersama Institusi Pengusaha Indonesia.</p>
            </div>
            <nav aria-label="Jelajah" class="grid content-start gap-2 text-sm">
                <p class="font-display font-semibold text-fg">Jelajah</p>
                @foreach ([['Kelas Bos', 'programs.index'], ['Siniar Bos', 'podcasts.index'], ['Direktori Lapak', 'shops.index'], ['Para Bos', 'bosses.index'], ['Jadi Bos Pendamping', 'become-boss'], ['Verifikasi Sertifikat', 'certificates.verify.form']] as [$label, $route])
                    @if (Route::has($route))<a href="{{ route($route) }}" class="text-fg-body hover:text-fg">{{ $label }}</a>@endif
                @endforeach
            </nav>
            <nav aria-label="Informasi" class="grid content-start gap-2 text-sm">
                <p class="font-display font-semibold text-fg">Informasi</p>
                @foreach (['tentang' => 'Tentang', 'bantuan' => 'Bantuan', 'privasi' => 'Privasi', 'syarat' => 'Syarat', 'kontak' => 'Kontak'] as $slug => $label)
                    @if (Route::has('pages.'.$slug))<a href="{{ route('pages.'.$slug) }}" class="text-fg-body hover:text-fg">{{ $label }}</a>@endif
                @endforeach
            </nav>
        </div>
        <div class="container-page flex flex-wrap items-center justify-between gap-2 border-t border-line py-6 text-xs text-fg-muted"><span>&copy; {{ now()->year }} alloBos. Pendamping usaha warga Gunungkidul.</span><span>Bersama Institusi Pengusaha Indonesia</span></div>
    </footer>

    @include('partials.confirm-dialog')
    @livewireScriptConfig
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
