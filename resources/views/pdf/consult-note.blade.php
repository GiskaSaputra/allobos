@extends('pdf.layout', ['title' => 'Catatan Konsultasi'])
@section('content')
    @php($note = $session->note)
    <h1>Catatan Konsultasi</h1>
    <p class="muted">{{ $session->user->name }}. Topik: {{ $session->topic }}. {{ $session->created_at->translatedFormat('d F Y, H:i') }} WIB.</p>
    @unless ($note->is_ai)<p class="muted">Catatan disusun tanpa AI karena layanan AI sedang tidak tersedia.</p>@endunless
    <h2>Inti masalah</h2>
    <div class="box">{{ $note->problem }}</div>
    <h2>Saran utama</h2>
    <ul>@foreach ($note->advice as $item)<li>{{ $item }}</li>@endforeach</ul>
    <h2>Langkah lanjut</h2>
    <ol>@foreach ($note->next_steps as $item)<li>{{ $item }}</li>@endforeach</ol>
    <h2>Modul yang disarankan</h2>
    <p>{{ collect(App\Services\Boss\Modules::links($note->modules))->pluck('label')->implode(', ') ?: '-' }}</p>
    <p class="muted" style="margin-top: 20px">Bos Allo adalah karakter AI. Saran ini bahan pertimbangan, bukan nasihat hukum, pajak, atau pinjaman.</p>
@endsection
