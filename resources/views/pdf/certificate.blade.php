@php($preview = $preview ?? false)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Sertifikat {{ $certificate->code }}</title>
    {{-- dompdf tidak membaca variabel CSS. Nilai rgb di sini salinan token brand (zaitun) dan accent (lime). Hiasan ada di gambar latar. --}}
    <style>
        @page { size: A4 landscape; margin: 0; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; color: rgb(20, 26, 12); }
        .sheet { position: relative; width: 297mm; height: 210mm; overflow: hidden; background: rgb(252, 252, 244); }
        .bg { position: absolute; left: 0; top: 0; width: 297mm; height: 210mm; }
        .center { position: absolute; left: 40mm; right: 40mm; text-align: center; }
        .logo { top: 15mm; }
        .logo img { height: 13mm; }
        .title { top: 31mm; font-family: DejaVu Serif, serif; font-size: 46px; font-weight: bold; letter-spacing: 7px; color: rgb(40, 50, 18); }
        .subtitle { top: 50mm; font-size: 12px; letter-spacing: 6px; color: rgb(62, 77, 30); }
        .rule { position: absolute; top: 58mm; left: 128mm; width: 41mm; height: 1.2mm; background: rgb(205, 224, 72); }
        .given { top: 63mm; font-size: 11px; letter-spacing: 3px; color: rgb(84, 96, 62); }
        .name { top: 70mm; font-family: DejaVu Serif, serif; font-size: 36px; font-weight: bold; font-style: italic; color: rgb(20, 26, 12); }
        .reason { top: 90mm; font-size: 12px; color: rgb(50, 60, 36); }
        .program { top: 97mm; font-size: 18px; font-weight: bold; color: rgb(40, 50, 18); }
        .meta { top: 107mm; left: 55mm; right: 55mm; font-size: 10.5px; line-height: 1.5; color: rgb(84, 96, 62); }
        .signer { position: absolute; bottom: 33mm; width: 66mm; text-align: center; }
        .signer.left { left: 50mm; }
        .signer.right { right: 50mm; }
        .signer img { height: 18mm; max-width: 56mm; }
        .signer .blank { height: 18mm; }
        .signer .line { border-top: 1px solid rgb(40, 50, 18); padding-top: 1.5mm; font-size: 12px; font-weight: bold; }
        .signer .role { font-size: 9.5px; color: rgb(84, 96, 62); }
        .verify { position: absolute; right: 22mm; top: 42mm; width: 32mm; text-align: center; font-size: 7.5px; line-height: 1.35; color: rgb(84, 96, 62); }
        .verify .qr { width: 27mm; height: 27mm; padding: 1.5mm; background: rgb(255, 255, 255); border: 0.6mm solid rgb(62, 77, 30); border-radius: 3mm; }
        .verify .qr img { width: 27mm; height: 27mm; }
        .verify .code { margin-top: 1.5mm; font-size: 8.5px; font-weight: bold; letter-spacing: 0.5px; color: rgb(20, 26, 12); }
        .demo { position: absolute; left: 22mm; top: 42mm; font-size: 10px; border: 1px solid rgb(150, 96, 12); color: rgb(150, 96, 12); padding: 1mm 3mm; border-radius: 10px; }
    </style>
    @if ($preview)
        {{-- Pratinjau di peramban: lembar ditaruh di tengah dan diskalakan oleh halaman induk. --}}
        <style>html, body { background: transparent; } .sheet { box-shadow: 0 10px 40px rgba(20, 26, 12, 0.25); }</style>
    @endif
</head>
<body>
<div class="sheet">
    <img class="bg" src="{{ $background }}" alt="">
    @if ($certificate->is_demo && config('features.demo'))<div class="demo">Demo</div>@endif

    <div class="center logo"><img src="{{ $logo }}" alt="alloBos"></div>
    <div class="center title">SERTIFIKAT</div>
    <div class="center subtitle">PENYELESAIAN {{ strtoupper($program->kind->label()) }}</div>
    <div class="rule"></div>
    <div class="center given">DIBERIKAN KEPADA</div>
    <div class="center name">{{ $certificate->recipient_name }}</div>
    <div class="center reason">atas kehadiran dan keberhasilan menyelesaikan {{ strtolower($program->kind->label()) }}</div>
    <div class="center program">{{ $program->title }}</div>
    <div class="center meta">
        {{ $program->field?->name ? 'Bidang '.$program->field->name.'. ' : '' }}{{ $program->sessions->first()?->starts_at->translatedFormat('d F Y') }}{{ $program->sessions->count() > 1 ? ' sampai '.$program->sessions->last()->ends_at->translatedFormat('d F Y') : '' }}, {{ $program->location }}.
        @if ($certificate->issued_on)<br>Diterbitkan {{ $certificate->issued_on->translatedFormat('d F Y') }} oleh alloBos bersama Institusi Pengusaha Indonesia.@endif
    </div>

    @foreach ($signers->take(2) as $i => $signer)
        <div class="signer {{ $i === 0 ? 'left' : 'right' }}">
            @if ($signer['image'])<img src="{{ $signer['image'] }}" alt="">@else<div class="blank"></div>@endif
            <div class="line">{{ $signer['name'] }}</div>
            <div class="role">{{ $signer['title'] }}</div>
        </div>
    @endforeach

    <div class="verify">
        <div class="qr">@if ($qr)<img src="{{ $qr }}" alt="Kode QR verifikasi">@endif</div>
        <div class="code">{{ $certificate->code }}</div>
        Pindai untuk memeriksa keaslian. Sidik digitalnya tercatat di blockchain.
    </div>
</div>
</body>
</html>
