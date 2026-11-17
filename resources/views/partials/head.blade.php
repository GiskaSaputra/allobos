<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ? $title.' | alloBos' : 'alloBos | Pendamping usaha warga Gunungkidul' }}</title>
<meta name="description" content="{{ $description ?? 'Tanya Bos Allo, belajar dari pengusaha asli, lalu buka lapak. Pendamping usaha untuk warga Gunungkidul.' }}">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<script>
    (function () {
        var t = null;
        try { t = localStorage.getItem('tema'); } catch (e) {}
        if (t === 'gelap' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
@vite('resources/css/app.css')
