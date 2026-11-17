<x-layouts.dashboard title="Sertifikat">
    <x-page-header title="Sertifikat" description="Status penandatanganan dan pencatatan blockchain semua kelas." />
    @if ($failed->isNotEmpty())
        <x-card class="mb-6 border-warning/50">
            <h2 class="text-xl">Pencatatan perlu perhatian</h2>
            <ul class="mt-3 space-y-2">
                @foreach ($failed as $tx)
                    <li class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-sm"><span class="font-medium text-fg">{{ $tx->program?->title }}</span><span class="block text-fg-muted">Percobaan {{ $tx->attempts }}. {{ Str::limit($tx->error, 120) }}</span></span>
                        @if ($tx->program && $tx->status === 'gagal')
                            <form method="POST" action="{{ route('pengelola.certificates.retry', $tx->program) }}">@csrf<x-button variant="secondary" size="sm">Catat Ulang ke Blockchain</x-button></form>
                        @else
                            <span class="badge badge-warning">Sedang dicoba ulang</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-card>
    @endif
    <form method="GET" class="mb-4 grid gap-3 sm:grid-cols-[1fr_1fr_1fr_auto] sm:items-end">
        <x-field label="Kelas" name="kelas"><x-select name="kelas" :options="$programs" :value="request('kelas')" placeholder="Semua kelas" /></x-field>
        <x-field label="Status" name="status"><x-select name="status" :options="collect(App\Enums\CertificateStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])" :value="$status" placeholder="Semua status" /></x-field>
        <x-field label="Cari" name="q"><x-text-input name="q" :value="request('q')" placeholder="Kode atau nama" /></x-field>
        <x-button variant="secondary">Saring</x-button>
    </form>
    @if ($certificates->isEmpty())
        <x-empty-state icon="phosphor-certificate" title="Tidak ada sertifikat" />
    @else
        <x-table>
            <thead><tr><th>Kode</th><th>Penerima</th><th>Kelas</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
                @foreach ($certificates as $c)
                    <tr>
                        <td data-label="Kode"><a href="{{ route('certificates.verify', $c->code) }}" target="_blank" class="font-mono text-xs text-fg underline-offset-2 hover:underline">{{ $c->code }}</a></td>
                        <td data-label="Penerima">{{ $c->recipient_name }} <x-demo-badge :show="$c->is_demo" /></td>
                        <td data-label="Kelas">{{ Str::limit($c->program->title, 40) }}</td>
                        <td data-label="Status"><x-status-badge :status="$c->status" /></td>
                        <td data-label="Aksi">
                            @if ($c->status === App\Enums\CertificateStatus::Tercatat)
                                <form method="POST" action="{{ route('pengelola.certificates.revoke', $c) }}" data-confirm="Cabut sertifikat {{ $c->code }}? Pencabutan dicatat di blockchain dan tidak bisa dibatalkan." data-confirm-reason="reason" data-confirm-ok="Cabut Sertifikat">@csrf<x-button variant="danger" size="sm">Cabut Sertifikat</x-button></form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-6">{{ $certificates->links() }}</div>
    @endif
</x-layouts.dashboard>
