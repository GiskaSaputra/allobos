@props(['title' => null, 'description' => null])
{{-- Ringan untuk pembeli dan pemeriksa sertifikat: hanya CSS, tanpa pustaka skrip. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    @stack('head')
</head>
<body class="bg-surface" data-motion>
    <header class="border-b border-line">
        <div class="container-page flex h-14 items-center justify-between">
            <a href="{{ route('home') }}" aria-label="alloBos, ke beranda"><x-logo /></a>
            <a href="{{ route('home') }}" class="text-sm text-fg-muted hover:text-fg">Tentang alloBos</a>
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
