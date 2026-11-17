@props(['certificate'])
<article class="card flex flex-col gap-3">
    <div class="flex flex-wrap items-center gap-2"><x-status-badge :status="$certificate->status" /><x-demo-badge :show="$certificate->is_demo" /></div>
    <h3 class="text-lg">{{ $certificate->program->title }}</h3>
    <p class="text-sm text-fg-muted">Kode {{ $certificate->code }}{{ $certificate->issued_on ? ', terbit '.$certificate->issued_on->translatedFormat('d F Y') : '' }}</p>
    <div class="mt-auto flex flex-wrap gap-2">
        <x-button variant="primary" size="sm" :href="route('warga.certificates.show', $certificate)">Buka</x-button>
        @if ($certificate->status->downloadable())
            <x-button variant="secondary" size="sm" :href="route('warga.certificates.pdf', $certificate)" icon="phosphor-download-simple">Unduh PDF</x-button>
            <button type="button" class="btn btn-secondary btn-sm" data-copy="{{ $certificate->verifyUrl() }}"><x-phosphor-link class="h-4 w-4" aria-hidden="true" /><span data-copy-label>Salin Tautan Verifikasi</span></button>
        @endif
    </div>
</article>
