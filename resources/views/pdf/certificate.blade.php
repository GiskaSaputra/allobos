@php($preview = $preview ?? false)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Sertifikat {{ $certificate->code }}</title>
    {{-- dompdf tidak membaca variabel CSS. Nilai rgb di sini salinan token brand, accent, ink. Tetap terbaca bila dicetak hitam putih. --}}
    <style>
        @page { size: A4 landscape; margin: 0; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; color: rgb(20, 26, 12); }
        .sheet { position: relative; width: 297mm; height: 210mm; box-sizing: border-box; padding: 16mm 20mm; }
        .frame { position: absolute; inset: 8mm; border: 2.5px solid rgb(62, 77, 30); border-radius: 18px; }
        .bar { position: absolute; left: 8mm; right: 8mm; top: 8mm; height: 10mm; background: rgb(205, 224, 72); border-radius: 16px 16px 0 0; }
        .brand { position: relative; margin-top: 8mm; font-size: 13px; color: rgb(62, 77, 30); }
        .brand b { font-size: 18px; color: rgb(20, 26, 12); }
        h1 { position: relative; margin: 10mm 0 2mm; font-size: 40px; letter-spacing: 1px; color: rgb(20, 26, 12); }
        .lead { font-size: 13px; color: rgb(84, 96, 62); margin: 0; }
        .name { font-size: 32px; font-weight: bold; margin: 5mm 0 2mm; border-bottom: 1.5px solid rgb(196, 205, 168); padding-bottom: 3mm; display: inline-block; min-width: 150mm; }
        .program { font-size: 17px; font-weight: bold; margin: 3mm 0 1mm; }
        .meta { font-size: 12px; color: rgb(50, 60, 36); }
        .signers { position: absolute; left: 20mm; bottom: 20mm; }
        .signer { display: inline-block; width: 70mm; margin-right: 8mm; vertical-align: bottom; text-align: center; }
        .signer img { height: 20mm; max-width: 60mm; }
        .signer .blank { height: 20mm; }
        .signer .line { border-top: 1px solid rgb(20, 26, 12); padding-top: 1.5mm; font-size: 12px; font-weight: bold; }
        .signer .role { font-size: 10px; color: rgb(84, 96, 62); }
        .verify { position: absolute; right: 20mm; bottom: 18mm; width: 48mm; text-align: center; font-size: 9px; color: rgb(50, 60, 36); }
        .verify img { width: 32mm; height: 32mm; }
        .qr-placeholder { width: 32mm; height: 32mm; border: 1px dashed rgb(84, 96, 62); margin: 0 auto; }
        .code { font-size: 12px; font-weight: bold; letter-spacing: 1px; margin-top: 1mm; color: rgb(20, 26, 12); }
        .demo { position: absolute; right: 20mm; top: 22mm; font-size: 11px; border: 1px solid rgb(150, 96, 12); color: rgb(150, 96, 12); padding: 1mm 3mm; border-radius: 10px; }
    </style>
</head>
<body>
<div class="sheet">
    <div class="frame"></div>
    <div class="bar"></div>
    @if ($certificate->is_demo)<div class="demo">Demo</div>@endif
    <div class="brand"><b>alloBos</b> bersama Institusi Pengusaha Indonesia</div>
    <h1>SERTIFIKAT</h1>
    <p class="lead">Diberikan kepada</p>
    <div class="name">{{ $certificate->recipient_name }}</div>
    <p class="lead">atas kehadiran dan penyelesaian {{ strtolower($program->kind->label()) }}</p>
    <div class="program">{{ $program->title }}</div>
    <div class="meta">
        {{ $program->field?->name ? 'Bidang '.$program->field->name.'. ' : '' }}{{ $program->sessions->first()?->starts_at->translatedFormat('d F Y') }}{{ $program->sessions->count() > 1 ? ' sampai '.$program->sessions->last()->ends_at->translatedFormat('d F Y') : '' }}, {{ $program->location }}.
        @if ($certificate->issued_on) Diterbitkan {{ $certificate->issued_on->translatedFormat('d F Y') }}. @endif
    </div>

    <div class="signers">
        @foreach ($signers as $signer)
            <div class="signer">
                @if ($signer['image'])<img src="{{ $signer['image'] }}" alt="">@else<div class="blank"></div>@endif
                <div class="line">{{ $signer['name'] }}</div>
                <div class="role">{{ $signer['title'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="verify">
        @if ($qr)<img src="{{ $qr }}" alt="">@else<div class="qr-placeholder"></div>@endif
        <div class="code">{{ $certificate->code }}</div>
        Pindai kode QR atau buka {{ preg_replace('#^https?://#', '', route('certificates.verify.form')) }} untuk memeriksa keaslian. Sidik digital sertifikat ini tercatat di blockchain.
    </div>
</div>
@if ($preview)
    <style>body { background: rgb(219, 226, 196); padding: 16px; } .sheet { background: rgb(255, 255, 250); margin: 0 auto; transform-origin: top left; } @media (max-width: 1150px) { .sheet { transform: scale(0.5); } }</style>
@endif
</body>
</html>
