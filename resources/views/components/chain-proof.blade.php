@props(['certificate', 'txUrl' => null, 'contractUrl' => null, 'revokeUrl' => null])
@php($tx = $certificate->transaction)
<section {{ $attributes->class('card space-y-4') }}>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-xl">Bukti blockchain</h2>
        <span class="flex items-center gap-2 text-sm text-fg-body">{{ $tx?->chain_name ?? config('chain.name') }}@if (config('chain.testnet'))<span class="badge badge-warning">Jaringan uji</span>@endif</span>
    </div>
    @if ($tx?->tx_hash)
        <dl class="grid gap-3 text-sm sm:grid-cols-2">
            <div class="sm:col-span-2">
                <dt class="text-fg-muted">Hash transaksi</dt>
                <dd class="mt-1 flex items-center gap-2">
                    <code class="truncate rounded-xl bg-surface-alt px-3 py-1.5 font-mono text-xs text-fg">{{ Str::substr($tx->tx_hash, 0, 14) }}...{{ Str::substr($tx->tx_hash, -10) }}</code>
                    <button type="button" class="btn btn-ghost btn-icon btn-sm" data-copy="{{ $tx->tx_hash }}" aria-label="Salin hash transaksi"><x-phosphor-copy class="h-4 w-4" aria-hidden="true" /><span data-copy-label class="sr-only">Salin</span></button>
                </dd>
            </div>
            <div><dt class="text-fg-muted">Nomor blok</dt><dd class="font-medium text-fg">{{ number_format($tx->block_number ?? 0, 0, ',', '.') }}</dd></div>
            <div><dt class="text-fg-muted">Dicatat</dt><dd class="font-medium text-fg">{{ $tx->updated_at->translatedFormat('d F Y, H:i') }} WIB</dd></div>
            @if ($certificate->contract_address)
                <div class="sm:col-span-2"><dt class="text-fg-muted">Alamat kontrak</dt><dd class="break-all font-mono text-xs text-fg">{{ $certificate->contract_address }}</dd></div>
            @endif
        </dl>
        <div class="flex flex-wrap gap-2">
            @if ($txUrl)<a href="{{ $txUrl }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm"><x-phosphor-arrow-square-out class="h-4 w-4" aria-hidden="true" />Buka di Penjelajah Blok</a>@endif
            @if ($revokeUrl)<a href="{{ $revokeUrl }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">Transaksi pencabutan</a>@endif
        </div>
        @unless ($txUrl)<p class="text-sm text-fg-muted">Jaringan ini tidak punya penjelajah blok publik.</p>@endunless
    @else
        <p class="text-sm text-fg-muted">Belum ada transaksi.</p>
    @endif
</section>
