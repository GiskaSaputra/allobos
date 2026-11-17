@props(['title' => null, 'pose' => 'crop-boss', 'headline' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['description' => null])
</head>
<body class="h-[100dvh] overflow-hidden bg-surface">
    <x-flash />
    {{-- Latar dan panel gambar tetap di tempat. Hanya kolom formulir yang bisa digulir. --}}
    <main class="grid h-full lg:grid-cols-[1.08fr_1fr]">
        <section class="bg-hero relative isolate hidden overflow-hidden lg:block" aria-hidden="true">
            <div class="absolute -left-32 top-24 -z-10 h-[460px] w-[460px] rounded-full bg-accent/50 blur-3xl"></div>
            <div class="absolute -bottom-24 right-0 -z-10 h-[380px] w-[380px] rounded-full bg-brand/40 blur-3xl"></div>
            <img src="{{ asset("image/{$pose}.webp") }}" alt="" width="720" height="965" class="pose-rise absolute bottom-0 left-1/2 h-[88%] w-auto max-w-none -translate-x-1/2 object-contain object-bottom">
            <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-ink/90 via-ink/50 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 space-y-4 p-12">
                <p class="inline-flex items-center gap-2 rounded-full border border-ink-fg/25 bg-ink/35 px-3 py-1.5 text-sm text-ink-fg backdrop-blur-md"><x-phosphor-storefront class="h-4 w-4" />Pendamping usaha warga Gunungkidul</p>
                <p class="max-w-lg font-display text-4xl font-semibold leading-[1.1] text-ink-fg xl:text-5xl">{!! $headline ?? 'Tanya bos, belajar, lalu <em class="font-bold text-accent">buka usaha</em>.' !!}</p>
                <p class="max-w-md text-ink-muted">Bos Allo, kelas dari pengusaha asli, dan lapak gratis dalam satu tempat.</p>
            </div>
        </section>
        <section class="flex h-full min-h-0 flex-col">
            <div class="shrink-0 px-4 pt-4 sm:px-8 lg:hidden" aria-hidden="true">
                <div class="bg-hero relative mx-auto flex h-28 max-w-[480px] items-end overflow-hidden rounded-[28px] p-5">
                    <p class="relative z-10 max-w-[60%] font-display text-lg font-semibold leading-tight text-ink">Tanya bos, belajar, lalu <em class="font-bold">buka usaha</em>.</p>
                    <img src="{{ asset("image/{$pose}.webp") }}" alt="" width="720" height="965" class="absolute -bottom-6 right-0 h-40 w-auto object-contain object-bottom">
                </div>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain">
                <div class="flex min-h-full flex-col items-center justify-center gap-6 px-4 py-6 sm:px-8 sm:py-10">
                    <div class="w-full max-w-[480px] rounded-[28px] border border-line bg-surface-raised p-6 shadow-float sm:p-10">
                        <div class="mb-8 flex items-center justify-between gap-3">
                            <a href="{{ route('home') }}" aria-label="alloBos, ke beranda"><x-logo size="h-10" /></a>
                            <x-theme-toggle />
                        </div>
                        {{ $slot }}
                    </div>
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm text-fg-muted hover:text-fg"><x-phosphor-arrow-left class="h-4 w-4" aria-hidden="true" />Kembali ke beranda</a>
                </div>
            </div>
        </section>
    </main>
    @livewireScriptConfig
    @vite(['resources/js/app.js'])
</body>
</html>
