<x-layouts.dashboard title="Tanda Tangan Sertifikat">
    <x-page-header title="Tanda Tangan Sertifikat" description="Kelas yang sertifikatnya kamu tanda tangani.">
        <x-slot:actions><x-button variant="secondary" :href="route('signing.specimen.edit')" icon="phosphor-signature">{{ $hasSpecimen ? 'Ganti Spesimen' : 'Simpan Spesimen' }}</x-button></x-slot:actions>
    </x-page-header>
    @unless ($hasSpecimen)<p class="mb-6 rounded-[20px] bg-warning/15 p-4 text-sm text-fg">Kamu belum menyimpan spesimen tanda tangan. Simpan sekali, lalu bisa dipakai untuk semua kelas.</p>@endunless
    @if ($signers->isEmpty())
        <x-empty-state icon="phosphor-signature" title="Belum ada kelas untuk ditandatangani">Pengelola IPI akan menetapkanmu sebagai penanda tangan di kelas yang kamu isi.</x-empty-state>
    @else
        <div class="grid gap-3">
            @foreach ($signers as $item)
                <a href="{{ route('signing.show', $item['signer']->program) }}" class="card card-link flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <span><span class="block font-display text-lg font-semibold text-fg">{{ $item['signer']->program->title }}</span><span class="text-sm text-fg-muted">Sebagai {{ $item['signer']->title }}</span></span>
                    @if ($item['signer']->signed_at)
                        <span class="badge badge-success">Sudah kamu tanda tangani</span>
                    @elseif ($item['pending'])
                        <span class="badge badge-warning">{{ $item['pending'] }} menunggu</span>
                    @else
                        <span class="badge badge-neutral">Kelas belum ditutup</span>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.dashboard>
