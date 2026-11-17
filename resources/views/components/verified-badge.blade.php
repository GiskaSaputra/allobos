@props(['certificate'])
@if ($certificate->status === App\Enums\CertificateStatus::Tercatat)
    <a href="{{ $certificate->verifyUrl() }}" {{ $attributes->class('inline-flex items-center gap-1.5 rounded-full border border-success/40 bg-success/10 px-3 py-1 text-sm text-fg hover:bg-success/20') }}>
        <x-phosphor-seal-check class="h-4 w-4 text-success" aria-hidden="true" />
        <span>Lulus kelas {{ $certificate->program->title }}</span>
        <span class="text-success underline underline-offset-2">Periksa</span>
    </a>
@endif
