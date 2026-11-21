@props(['title' => null, 'description' => null])
{{-- Ringan untuk pembeli dan pemeriksa sertifikat: hanya CSS, tanpa pustaka skrip. Warga yang sudah masuk tetap di tampilan dashboard. --}}
@if (auth()->user()?->active_role === App\Enums\RoleName::Warga && auth()->user()->profile?->onboarded_at)
<x-layouts.app :title="$title">
    {{ $slot }}
    @isset($footer)<div class="mt-10 border-t border-line pt-6 text-xs text-fg-muted">{{ $footer }}</div>@endisset
</x-layouts.app>
@else
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    @stack('head')
</head>
<body class="bg-surface" data-motion>
    @include('partials.splash')
    <header class="border-b border-line">
        <div class="container-page flex h-14 items-center justify-between">
            <a href="{{ route('home') }}" aria-label="alloBos, ke beranda"><x-logo /></a>
            <nav aria-label="Navigasi utama" class="flex items-center gap-4 text-sm">
                <a href="{{ route('certificates.verify.form') }}" @class(['hover:text-fg', 'font-medium text-fg' => request()->routeIs('certificates.verify*'), 'text-fg-muted' => ! request()->routeIs('certificates.verify*')])>Verifikasi Sertifikat</a>
                <a href="{{ route('home') }}" class="hidden text-fg-muted hover:text-fg sm:inline">Tentang alloBos</a>
            </nav>
        </div>
    </header>
    <main id="isi" class="container-page py-8">{{ $slot }}</main>
    <footer class="container-page border-t border-line py-6 text-xs text-fg-muted">
        {{ $footer ?? '' }}
        <p class="mt-2">Dibuat dengan alloBos, pendamping usaha warga Gunungkidul.</p>
    </footer>
    <script>
        document.addEventListener('click', function (e) {
            var b = e.target.closest('[data-copy]');
            if (!b || !navigator.clipboard) return;
            navigator.clipboard.writeText(b.getAttribute('data-copy')).then(function () {
                var l = b.querySelector('[data-copy-label]') || b, old = l.textContent;
                l.textContent = 'Tersalin';
                setTimeout(function () { l.textContent = old; }, 2000);
            });
        });
    </script>
</body>
</html>
@endif
