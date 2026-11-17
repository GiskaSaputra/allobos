<x-layouts.app title="Sertifikat Saya">
    <x-page-header title="Sertifikat Saya" description="Sertifikat kelas yang tercatat di blockchain bisa diperiksa keasliannya oleh siapa saja lewat kode QR." />
    @if ($certificates->isEmpty())
        <x-empty-state icon="phosphor-certificate" title="Belum ada sertifikat" action="Lihat Kelas" :href="route('programs.index')">Ikuti kelas Bos dan hadir minimal 75 persen sesi. Sertifikat dibuat setelah kelas ditutup dan ditandatangani pembicara.</x-empty-state>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($certificates as $certificate)<x-certificate-card :certificate="$certificate" />@endforeach
        </div>
    @endif
</x-layouts.app>
