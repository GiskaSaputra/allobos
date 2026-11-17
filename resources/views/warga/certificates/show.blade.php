@php($status = $certificate->status)
<x-layouts.app title="Sertifikat">
    <x-page-header :title="$certificate->program->title" :description="'Kode '.$certificate->code.'.'">
        <x-slot:actions><x-button variant="ghost" :href="route('warga.certificates.index')" icon="phosphor-arrow-left">Sertifikat Saya</x-button></x-slot:actions>
    </x-page-header>
    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="space-y-4">
            <x-card>
                <div class="flex flex-wrap items-center gap-2"><x-status-badge :status="$status" /><x-demo-badge :show="$certificate->is_demo" /></div>
                <p class="mt-3 text-fg-body">
                    @switch($status)
                        @case(App\Enums\CertificateStatus::MenungguTandaTangan) Sertifikat sedang menunggu tanda tangan pembicara. @break
                        @case(App\Enums\CertificateStatus::Ditandatangani) Sertifikat sudah ditandatangani dan sedang disusun. @break
                        @case(App\Enums\CertificateStatus::MenungguPencatatan) PDF sudah bisa diunduh. Sidik digitalnya sedang dicatat ke blockchain (catatan publik yang tidak bisa diubah). @break
                        @case(App\Enums\CertificateStatus::Tercatat) Sertifikat tercatat di blockchain. Siapa pun bisa memeriksa keasliannya lewat kode QR atau tautan verifikasi. @break
                        @case(App\Enums\CertificateStatus::Dicabut) Sertifikat ini dicabut. Alasan: {{ $certificate->reason }} @break
                        @default Sertifikat tidak diterbitkan. {{ $certificate->reason }}
                    @endswitch
                </p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <x-button variant="primary" :href="$status->downloadable() ? route('warga.certificates.pdf', $certificate) : null" icon="phosphor-download-simple" :disabled="! $status->downloadable()">Unduh PDF</x-button>
                    @if ($status !== App\Enums\CertificateStatus::MenungguTandaTangan && $status !== App\Enums\CertificateStatus::Dibatalkan)
                        <button type="button" class="btn btn-secondary" data-copy="{{ $certificate->verifyUrl() }}"><x-phosphor-link class="h-5 w-5" aria-hidden="true" /><span data-copy-label>Salin Tautan Verifikasi</span></button>
                    @endif
                    @if ($status === App\Enums\CertificateStatus::Tercatat && $explorer)
                        <a href="{{ $explorer }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">Lihat Bukti Blockchain</a>
                    @endif
                </div>
                @unless ($status->downloadable())<p class="mt-2 text-sm text-fg-muted">PDF bisa diunduh setelah semua penanda tangan menandatangani.</p>@endunless
            </x-card>
            @if ($certificate->transaction)<x-chain-proof :certificate="$certificate" :tx-url="$explorer" />@endif
        </div>

        <aside class="space-y-4">
            <form method="POST" action="{{ route('warga.certificates.update', $certificate) }}" class="card space-y-3">
                @csrf @method('PATCH')
                <h2 class="text-xl">Nama di halaman verifikasi</h2>
                <div class="segment" role="radiogroup" aria-label="Tampilan nama">
                    <label><input type="radio" name="show_full_name" value="1" class="segment-input sr-only" @checked($certificate->show_full_name) onchange="this.form.requestSubmit()"><span class="segment-item">Nama lengkap</span></label>
                    <label><input type="radio" name="show_full_name" value="0" class="segment-input sr-only" @checked(! $certificate->show_full_name) onchange="this.form.requestSubmit()"><span class="segment-item">Inisial</span></label>
                </div>
                <p class="text-sm text-fg-muted">Tampil sebagai: {{ $certificate->displayName() }}</p>
            </form>
            @php($canShow = $status === App\Enums\CertificateStatus::Tercatat && $hasShop)
            <form method="POST" action="{{ route('warga.certificates.update', $certificate) }}" class="card space-y-3">
                @csrf @method('PATCH')
                <h2 class="text-xl">Lencana di Lapak</h2>
                <input type="hidden" name="show_on_shop" value="0">
                <label class="flex cursor-pointer items-center justify-between gap-3">
                    <span class="text-sm text-fg-body">Tampilkan di Lapak</span>
                    <input type="checkbox" name="show_on_shop" value="1" class="peer sr-only" @checked($certificate->show_on_shop) @disabled(! $canShow) onchange="this.form.requestSubmit()">
                    <span class="relative h-7 w-12 rounded-full bg-line transition peer-checked:bg-brand peer-disabled:opacity-50 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-accent-text after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-surface-raised after:transition peer-checked:after:translate-x-5" aria-hidden="true"></span>
                </label>
                <p class="text-sm text-fg-muted">{{ $certificate->show_on_shop ? 'Sedang tampil' : 'Tidak tampil' }}.@unless ($canShow) {{ $hasShop ? 'Lencana bisa tampil setelah sertifikat Tercatat.' : 'Buka lapak dulu supaya lencana bisa tampil.' }}@endunless</p>
            </form>
            <x-button variant="ghost" :href="$certificate->verifyUrl()" target="_blank" full>Buka Halaman Verifikasi</x-button>
        </aside>
    </div>
</x-layouts.app>
