<x-layouts.dashboard :title="$program->title">
    <x-page-header :title="$program->title" :description="$program->kind->label().'. '.$program->location.'.'">
        <x-slot:actions>
            <x-button variant="secondary" :href="route('pengelola.programs.edit', $program)">Ubah</x-button>
            @if ($program->status === App\Enums\ProgramStatus::Draf)
                <form method="POST" action="{{ route('pengelola.programs.publish', $program) }}" data-confirm="Terbitkan kelas ini? Kelas akan tampil di halaman publik dan warga bisa mendaftar." data-confirm-ok="Terbitkan">@csrf<x-button variant="primary">Terbitkan</x-button></form>
            @elseif ($program->status !== App\Enums\ProgramStatus::Selesai)
                <form method="POST" action="{{ route('pengelola.programs.close', $program) }}" data-confirm="Tutup kelas? Draf sertifikat dibuat untuk peserta yang hadir minimal 75 persen, lalu penanda tangan diberi tahu." data-confirm-ok="Tutup Kelas">@csrf<x-button variant="secondary">Tutup Kelas</x-button></form>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 flex flex-wrap gap-2"><x-status-badge :status="$program->status" />@if ($program->is_official)<x-official-badge />@endif<x-demo-badge :show="$program->is_demo" /></div>

    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <div class="space-y-6">
            <x-card>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-xl">Sesi dan kode kehadiran</h2>
                    <div class="flex gap-2">
                        <x-button variant="secondary" size="sm" :href="route('pengelola.programs.participants', $program)">Peserta ({{ $enrolled }})</x-button>
                        <x-button variant="secondary" size="sm" :href="route('pengelola.programs.export', $program)" icon="phosphor-microsoft-excel-logo">Unduh Peserta (XLSX)</x-button>
                    </div>
                </div>
                <ul class="mt-4 space-y-2">
                    @foreach ($program->sessions as $session)
                        <li class="flex flex-col gap-2 rounded-[20px] border border-line p-3 sm:flex-row sm:items-center sm:justify-between">
                            <span class="text-sm"><span class="block font-medium text-fg">{{ $session->title }}</span><span class="text-fg-muted">{{ $session->starts_at->translatedFormat('d M Y, H:i') }} sampai {{ $session->ends_at->format('H:i') }}</span></span>
                            @if ($program->status !== App\Enums\ProgramStatus::Draf && ! $session->ends_at->isPast())
                                <form method="POST" action="{{ route('pengelola.programs.code', $session) }}" target="_blank">@csrf<x-button variant="secondary" size="sm">Buka Kode Kehadiran</x-button></form>
                            @elseif ($session->ends_at->isPast())
                                <span class="text-sm text-fg-muted">Selesai</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </x-card>
            @if ($certificates->isNotEmpty())
                <x-card>
                    <h2 class="text-xl">Sertifikat</h2>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($certificates as $s => $total)<li><x-status-badge :status="App\Enums\CertificateStatus::from($s)" /> {{ $total }}</li>@endforeach
                    </ul>
                    <x-button variant="ghost" size="sm" :href="route('pengelola.certificates.index', ['kelas' => $program->id])" class="mt-3">Kelola Sertifikat</x-button>
                </x-card>
            @endif
        </div>

        <x-card class="space-y-4">
            <h2 class="text-xl">Penanda tangan sertifikat</h2>
            <p class="text-sm text-fg-muted">Satu atau dua orang: pembicara kelas (akun Bos) atau pimpinan IPI. Wajib ditetapkan sebelum kelas ditutup.</p>
            @if ($program->signers->isNotEmpty())
                <ul class="space-y-2">
                    @foreach ($program->signers as $signer)
                        <li class="rounded-2xl bg-surface-alt p-3 text-sm"><span class="block font-medium text-fg">{{ $signer->user->name }}</span><span class="text-fg-muted">{{ $signer->title }}</span>@if ($signer->signed_at)<span class="badge badge-success ml-1">Sudah tanda tangan</span>@endif</li>
                    @endforeach
                </ul>
            @endif
            @if ($program->signers->whereNotNull('signed_at')->isEmpty())
                @php($current = $program->signers->values())
                <form method="POST" action="{{ route('pengelola.programs.signers', $program) }}" class="space-y-3" x-data="{ second: {{ $current->count() > 1 ? 'true' : 'false' }} }">
                    @csrf
                    @foreach ([0, 1] as $i)
                        <fieldset class="space-y-2" @if ($i === 1) x-show="second" x-cloak :disabled="!second" @endif>
                            <legend class="text-sm font-medium text-fg">Penanda tangan {{ $i + 1 }}</legend>
                            <label class="sr-only" for="signer-{{ $i }}">Akun penanda tangan {{ $i + 1 }}</label>
                            <select id="signer-{{ $i }}" name="signers[{{ $i }}][user_id]" class="field-input" required>
                                <option value="">Pilih akun</option>
                                @foreach ($signerOptions as $option)<option value="{{ $option['id'] }}" @selected(($current[$i]->user_id ?? null) === $option['id'])>{{ $option['label'] }}</option>@endforeach
                            </select>
                            <label class="sr-only" for="signer-title-{{ $i }}">Jabatan atau usaha penanda tangan {{ $i + 1 }}</label>
                            <input id="signer-title-{{ $i }}" name="signers[{{ $i }}][title]" value="{{ $current[$i]->title ?? ($i === 0 ? 'Pembicara kelas' : 'Pimpinan Institusi Pengusaha Indonesia') }}" class="field-input" maxlength="120" required placeholder="Jabatan atau usaha">
                        </fieldset>
                    @endforeach
                    <button type="button" class="btn btn-ghost btn-sm" @click="second = !second" x-text="second ? 'Pakai satu penanda tangan' : 'Tambah penanda tangan kedua'"></button>
                    @error('signers')<p class="text-sm text-danger">{{ $message }}</p>@enderror
                    <x-button variant="secondary">Tetapkan Penanda Tangan</x-button>
                </form>
            @endif
        </x-card>
    </div>
</x-layouts.dashboard>
