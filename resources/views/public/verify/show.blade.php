<x-layouts.shop :title="'Verifikasi '.$code">
    <div class="mx-auto max-w-3xl space-y-6 py-4">
        <x-verify-status :status="$status" />

        @if ($chain_error)<p class="rounded-[20px] bg-warning/15 p-4 text-sm text-fg">{{ $chain_error }}</p>@endif

        @if ($certificate)
            <section class="card space-y-4">
                <h2 class="text-xl">Data sertifikat</h2>
                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-fg-muted">Penerima</dt><dd class="text-base font-medium text-fg">{{ $certificate->displayName() }}</dd></div>
                    <div><dt class="text-fg-muted">Kode</dt><dd class="font-mono text-fg">{{ $certificate->code }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-fg-muted">Kelas</dt><dd class="text-base font-medium text-fg">{{ $certificate->program->title }}</dd></div>
                    <div><dt class="text-fg-muted">Bidang</dt><dd class="text-fg">{{ $certificate->program->field?->name ?? '-' }}</dd></div>
                    <div><dt class="text-fg-muted">Tanggal terbit</dt><dd class="text-fg">{{ $certificate->issued_on?->translatedFormat('d F Y') ?? '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-fg-muted">Penyelenggara</dt><dd class="text-fg">alloBos bersama Institusi Pengusaha Indonesia @if ($certificate->is_demo && config('features.demo'))<span class="badge badge-warning">Demo</span>@endif</dd></div>
                </dl>
                @if ($status === 'dicabut')
                    <p class="rounded-[20px] bg-danger/10 p-4 text-sm text-fg"><strong>Alasan dicabut:</strong> {{ $chain['reason'] ?? null ?: $certificate->reason }}</p>
                @endif
            </section>

            <section class="card space-y-3">
                <h2 class="text-xl">Penanda tangan</h2>
                <ul class="space-y-2">
                    @foreach ($certificate->program->signers as $signer)
                        <li class="text-sm"><span class="block font-medium text-fg">{{ $signer->user->name }}</span><span class="text-fg-muted">{{ $signer->title }}{{ $signer->signed_at ? ', menandatangani '.$signer->signed_at->translatedFormat('d F Y') : '' }}</span>@if ($signer->wallet_address)<span class="block break-all font-mono text-xs text-fg-muted">Dompet {{ $signer->wallet_address }}</span>@endif</li>
                    @endforeach
                </ul>
            </section>

            @if ($certificate->transaction)
                <x-chain-proof :certificate="$certificate" :tx-url="$txUrl" :contract-url="$contractUrl" :revoke-url="$revokeUrl" />
            @endif
        @endif

        <section class="space-y-2 text-sm text-fg-body">
            <h2 class="text-lg">Cara kerja pemeriksaan</h2>
            <p>Tiap sertifikat punya sidik digital, yaitu ringkasan unik dari isi sertifikat. Sidik itu dicatat di blockchain, catatan publik yang tidak bisa diubah, sehingga halaman ini cukup mencocokkan sidik di server dengan catatan blockchain.</p>
            <p>Jaringan: {{ config('chain.name') }}@if (config('chain.testnet')) <span class="badge badge-warning">Jaringan uji</span>@endif. Data pribadi tidak ditulis ke blockchain.</p>
        </section>

        <a href="{{ route('certificates.verify.form') }}" class="btn btn-secondary">Periksa Kode Lain</a>
        @if ($certificate && $certificate->show_on_shop && $certificate->user->shop?->status === App\Enums\ShopStatus::Terbit)
            <a href="{{ route('shops.show', $certificate->user->shop) }}" class="btn btn-ghost btn-sm">Lihat Lapak Pemilik</a>
        @endif
    </div>
</x-layouts.shop>
