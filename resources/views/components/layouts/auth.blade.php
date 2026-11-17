@props(['title' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['description' => null])
</head>
<body class="bg-surface">
    <x-flash />
    <main class="grid min-h-[100dvh] lg:grid-cols-2">
        <section class="relative hidden overflow-hidden p-6 lg:block">
            <div class="bg-hero relative flex h-full flex-col justify-between overflow-hidden rounded-panel p-10">
                <a href="{{ route('home') }}" class="w-fit rounded-full bg-ink/35 px-4 py-2 backdrop-blur-md" aria-label="Ke beranda"><x-logo on-dark /></a>
                <div class="space-y-6">
                    <div class="w-fit overflow-hidden rounded-card border border-ink-fg/20 shadow-float">
                        <img src="{{ asset('gif/bos-allo/allo-transparan.webp') }}" alt="Bos Allo, maskot alloBos, duduk di meja kerja" width="400" height="225" class="block w-[360px]">
                    </div>
                    <p class="max-w-md rounded-[24px] bg-ink/45 p-6 font-display text-3xl font-semibold leading-tight text-ink-fg backdrop-blur-md">Tanya bos, belajar, lalu <em class="font-bold">buka usaha</em>.</p>
                </div>
            </div>
        </section>
        <section class="flex flex-col px-4 py-8 sm:px-10 lg:justify-center lg:px-16">
            <div class="mb-10 flex items-center justify-between lg:hidden">
                <a href="{{ route('home') }}" aria-label="Ke beranda"><x-logo /></a>
                <x-theme-toggle />
            </div>
            <div class="mx-auto w-full max-w-md">
                {{ $slot }}
            </div>
        </section>
    </main>
    @livewireScriptConfig
    @vite(['resources/js/app.js'])
</body>
</html>
