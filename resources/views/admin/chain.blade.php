<x-layouts.dashboard title="Blockchain">
    <x-page-header title="Blockchain" description="Jaringan, kontrak pintar, dompet penerbit, dan transaksi terakhir." />
    @unless ($configured)<p class="mb-6 rounded-[20px] bg-warning/15 p-4 text-sm text-fg">CHAIN_RPC_URL, CERT_CONTRACT_ADDRESS, atau ISSUER_PRIVATE_KEY belum diisi. Sertifikat akan berstatus Menunggu Pencatatan sampai jaringan diatur.</p>@endunless
    @if ($error)<p class="mb-6 rounded-[20px] bg-danger/10 p-4 text-sm text-fg">Jaringan tidak bisa dihubungi: {{ $error }}</p>@endif
    @if ($lowBalance)<p class="mb-6 rounded-[20px] bg-warning/15 p-4 text-sm text-fg">Saldo dompet penerbit di bawah {{ config('chain.min_balance') }}. Isi ulang dari faucet jaringan uji sebelum menutup kelas berikutnya.</p>@endif
    <div class="grid gap-6 lg:grid-cols-2">
        <x-card>
            <h2 class="text-xl">Jaringan</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-fg-muted">Nama</dt><dd class="text-fg">{{ config('chain.name') }} @if (config('chain.testnet'))<x-badge tone="warning">Jaringan uji</x-badge>@endif</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-fg-muted">Chain ID</dt><dd class="text-fg">{{ $status['chain_id'] ?? config('chain.id') }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-fg-muted">Blok terakhir</dt><dd class="text-fg">{{ $status ? number_format($status['block'], 0, ',', '.') : '-' }}</dd></div>
                <div class="gap-3"><dt class="text-fg-muted">Kontrak</dt><dd class="break-all font-mono text-xs text-fg">@if ($contractUrl)<a href="{{ $contractUrl }}" target="_blank" rel="noopener" class="underline">{{ config('chain.contract') }}</a>@else{{ config('chain.contract') ?? '-' }}@endif</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-fg-muted">Kontrak ter-deploy</dt><dd class="text-fg">{{ $status ? ($status['contract_deployed'] ? 'Ya' : 'Tidak') : '-' }}</dd></div>
            </dl>
        </x-card>
        <x-card>
            <h2 class="text-xl">Dompet penerbit</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div><dt class="text-fg-muted">Alamat</dt><dd class="break-all font-mono text-xs text-fg">@if ($issuerUrl)<a href="{{ $issuerUrl }}" target="_blank" rel="noopener" class="underline">{{ $status['issuer'] }}</a>@else{{ $status['issuer'] ?? '-' }}@endif</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-fg-muted">Saldo</dt><dd class="font-display text-lg font-semibold text-fg">{{ $status['balance'] ?? '-' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-fg-muted">Terdaftar sebagai penerbit</dt><dd class="text-fg">{{ $status ? ($status['issuer_allowed'] ? 'Ya' : 'Tidak') : '-' }}</dd></div>
            </dl>
            <p class="mt-3 text-xs text-fg-muted">Kunci privat hanya ada di variabel lingkungan server dan tidak pernah ditampilkan.</p>
        </x-card>
    </div>
    <h2 class="mb-3 mt-8 text-xl">Transaksi terakhir</h2>
    @if ($transactions->isEmpty())
        <x-empty-state icon="phosphor-link" title="Belum ada transaksi" />
    @else
        <x-table>
            <thead><tr><th>Jenis</th><th>Kelas</th><th>Hash</th><th>Blok</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($transactions as $tx)
                    <tr>
                        <td data-label="Jenis">{{ $tx->kind === 'issue' ? 'Terbit' : 'Cabut' }}</td>
                        <td data-label="Kelas">{{ Str::limit($tx->program?->title, 30) }}</td>
                        <td data-label="Hash" class="font-mono text-xs">@if ($tx->tx_hash && $chain->explorerTx($tx->tx_hash))<a href="{{ $chain->explorerTx($tx->tx_hash) }}" target="_blank" rel="noopener" class="underline">{{ Str::limit($tx->tx_hash, 18) }}</a>@else{{ Str::limit($tx->tx_hash ?? '-', 18) }}@endif</td>
                        <td data-label="Blok">{{ $tx->block_number ?? '-' }}</td>
                        <td data-label="Status"><x-badge :tone="$tx->status === 'terkonfirmasi' ? 'success' : ($tx->status === 'gagal' ? 'danger' : 'warning')">{{ ucfirst($tx->status) }}</x-badge></td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
    @endif
</x-layouts.dashboard>
