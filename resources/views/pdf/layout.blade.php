<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    {{-- dompdf tidak membaca variabel CSS, jadi nilai token ditulis ulang sebagai rgb di sini. --}}
    <style>
        @page { margin: 32px 40px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: rgb(50, 60, 36); line-height: 1.5; }
        h1, h2, h3 { color: rgb(20, 26, 12); margin: 0 0 6px; }
        h1 { font-size: 20px; } h2 { font-size: 14px; margin-top: 18px; }
        .muted { color: rgb(84, 96, 62); }
        .brand { color: rgb(62, 77, 30); font-weight: bold; }
        .box { border: 1px solid rgb(196, 205, 168); border-radius: 10px; padding: 12px 14px; margin-top: 10px; }
        .header { border-bottom: 2px solid rgb(62, 77, 30); padding-bottom: 8px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 5px 6px; border-bottom: 1px solid rgb(196, 205, 168); text-align: left; vertical-align: top; }
        ul, ol { margin: 4px 0 0 18px; padding: 0; }
        .footer { position: fixed; bottom: -16px; left: 0; right: 0; font-size: 9px; color: rgb(84, 96, 62); }
    </style>
</head>
<body>
    <div class="header"><span class="brand">alloBos</span> <span class="muted">Pendamping usaha warga Gunungkidul</span></div>
    @yield('content')
    <div class="footer">Dicetak {{ now()->translatedFormat('d F Y, H:i') }} WIB dari alloBos.</div>
</body>
</html>
