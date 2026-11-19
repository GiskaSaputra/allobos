@php($pending = $certificates->where('status', App\Enums\CertificateStatus::MenungguTandaTangan))
<x-layouts.dashboard :title="'Tanda tangan '.$program->title">
    <x-page-header :title="$program->title" :description="'Kamu menandatangani sebagai '.$signer->title.'.'" />
    @if ($signer->signed_at)
        <p class="mb-6 rounded-[20px] bg-success/15 p-4 text-sm text-fg">Kamu sudah menandatangani pada {{ $signer->signed_at->translatedFormat('d F Y, H:i') }} WIB.{{ $program->signers->whereNull('signed_at')->isNotEmpty() ? ' Menunggu penanda tangan lain.' : '' }}</p>
    @endif
    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <section>
            <h2 class="mb-3 text-xl">Penerima ({{ $pending->count() }})</h2>
            @if ($certificates->isEmpty())
                <x-empty-state icon="phosphor-certificate" title="Tidak ada sertifikat menunggu">{{ $signedCount ? $signedCount.' sertifikat sudah ditandatangani.' : 'Sertifikat dibuat setelah Pengelola menutup kelas.' }}</x-empty-state>
            @else
                <x-table>
                    <thead><tr><th>Nama</th><th>Kode</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead>
                    <tbody>
                        @foreach ($certificates as $certificate)
                            <tr>
                                <td data-label="Nama" class="font-medium text-fg">{{ $certificate->recipient_name }}</td>
                                <td data-label="Kode"><code class="text-xs">{{ $certificate->code }}</code></td>
                                <td data-label="Status"><x-status-badge :status="$certificate->status" />@if ($certificate->reason)<span class="block text-xs text-fg-muted">{{ $certificate->reason }}</span>@endif</td>
                                <td data-label="Aksi">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('signing.preview', [$program, $certificate]) }}" target="_blank" class="btn btn-secondary btn-sm">Lihat Pratinjau</a>
                                        @if ($certificate->status === App\Enums\CertificateStatus::MenungguTandaTangan && ! $signer->signed_at)
                                            <form method="POST" action="{{ route('signing.exclude', [$program, $certificate]) }}" data-confirm="Keluarkan {{ $certificate->recipient_name }} dari daftar penerima? Sertifikatnya dibatalkan dan warga diberi tahu." data-confirm-reason="reason" data-confirm-ok="Keluarkan">@csrf<x-button variant="danger" size="sm">Keluarkan dari Daftar</x-button></form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-table>
            @endif
        </section>

        <aside class="space-y-4">
            @if (! $signer->signed_at && $pending->isNotEmpty())
                <form method="POST" action="{{ route('signing.sign', $program) }}" class="card space-y-4" data-confirm="Tanda tangani {{ $pending->count() }} sertifikat kelas ini? Tanda tanganmu tidak bisa ditarik setelah sertifikat tercatat." data-confirm-ok="Tanda Tangani">
                    @csrf
                    <h2 class="text-xl">Tanda tangani</h2>
                    <p class="text-sm text-fg-muted">Penanda tangan kelas: {{ $program->signers->pluck('user.name')->implode(' dan ') }}.</p>
                    <x-field label="Kata sandi" name="password" hint="Tanda tangan tidak bisa diwakilkan ke akun lain."><x-text-input name="password" type="password" required autocomplete="current-password" /></x-field>
                    <x-button variant="primary" size="lg" full loading="Menandatangani">Tanda Tangani {{ $pending->count() }} Sertifikat</x-button>
                </form>
            @endif
            <x-card class="space-y-3" x-data="walletSign({{ \Illuminate\Support\Js::from(route('signing.wallet', $program)) }}, {{ \Illuminate\Support\Js::from($walletMessage) }})" x-show="available" x-cloak>
                <h2 class="text-xl">Tanda tangan dompet (opsional)</h2>
                <p class="text-sm text-fg-muted">Bukti tambahan: dompet peramban menandatangani id kelas dan sidik gabungan sertifikat. Tanpa dompet, alur tetap berjalan.</p>
                @if ($signer->wallet_address)
                    <p class="break-all text-sm text-success">Tersimpan: {{ $signer->wallet_address }}</p>
                @else
                    <button type="button" class="btn btn-secondary" @click="connect" :disabled="busy"><span x-text="busy ? 'Menunggu dompet' : 'Hubungkan Dompet'">Hubungkan Dompet</span></button>
                    <p class="text-sm" :class="error ? 'text-danger' : 'text-success'" x-text="message" aria-live="polite"></p>
                @endif
            </x-card>
        </aside>
    </div>
</x-layouts.dashboard>
