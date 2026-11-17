@php($status = $session->status)
<x-layouts.dashboard title="Sesi Temu Bos">
    <x-page-header :title="'Sesi dengan '.$session->user->name" :description="$session->slot->starts_at->translatedFormat('l, d F Y, H:i').' WIB, '.Str::lower(App\Models\BossSlot::MODES[$session->slot->mode]).'.'" />
    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <div class="space-y-4">
            <x-card>
                <div class="flex items-center justify-between gap-2"><h2 class="text-xl">Topik</h2><x-status-badge :status="$status" /></div>
                <p class="mt-2 whitespace-pre-line text-fg-body">{{ $session->topic }}</p>
                <p class="mt-3 text-sm text-fg-muted">Warga dari {{ $session->user->profile?->district?->name ?? 'Gunungkidul' }}{{ $session->user->profile?->existing_business ? ', usaha '.$session->user->profile->existing_business : '' }}.</p>
            </x-card>
            @if ($session->note)
                <x-card>
                    <h2 class="text-xl">Lampiran Catatan Konsultasi</h2>
                    <p class="mt-2 text-fg-body"><strong class="text-fg">Inti masalah:</strong> {{ $session->note->problem }}</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-fg-body">@foreach ($session->note->advice as $a)<li>{{ $a }}</li>@endforeach</ul>
                </x-card>
            @endif
            @if ($status === App\Enums\MentoringStatus::Selesai)
                <x-card><h2 class="text-xl">Catatanmu</h2><p class="mt-2 whitespace-pre-line text-fg-body">{{ $session->boss_notes }}</p>@if ($session->rating)<p class="mt-3 text-sm text-fg-muted">Penilaian warga: {{ $session->rating }} dari 5.</p>@endif</x-card>
            @endif
        </div>
        <div class="space-y-4">
            @if ($status === App\Enums\MentoringStatus::Menunggu)
                <form method="POST" action="{{ route('bos.sessions.approve', $session) }}" class="card space-y-4">
                    @csrf
                    <h2 class="text-xl">Setujui</h2>
                    @if ($session->slot->mode === 'daring')
                        <x-field label="Tautan rapat" name="meeting_link" hint="Hanya terlihat oleh kamu dan warga setelah disetujui." required><x-text-input name="meeting_link" type="url" placeholder="https://meet.google.com/..." required /></x-field>
                    @else
                        <x-field label="Alamat pertemuan" name="address" required><x-text-input name="address" required /></x-field>
                    @endif
                    <x-button variant="primary" size="sm">Setujui</x-button>
                </form>
                <form method="POST" action="{{ route('bos.sessions.reject', $session) }}" data-confirm="Tolak permintaan ini? Warga menerima alasanmu dan slot terbuka lagi." data-confirm-reason="reason" data-confirm-ok="Tolak">@csrf<x-button variant="danger" size="sm">Tolak</x-button></form>
            @elseif ($status === App\Enums\MentoringStatus::Disetujui)
                <x-card>
                    <h2 class="text-xl">Detail pertemuan</h2>
                    <p class="mt-2 break-all text-fg-body">{{ $session->meeting_link ?? $session->address }}</p>
                    @if ($session->meeting_link)<a href="{{ $session->meeting_link }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm mt-3">Buka Tautan Rapat</a>@endif
                </x-card>
                <form method="POST" action="{{ route('bos.sessions.complete', $session) }}" class="card space-y-4" x-data="{ notes: @js(old('notes', '')) }">
                    @csrf
                    <h2 class="text-xl">Tandai selesai</h2>
                    <x-field label="Catatan sesi" name="notes" required><x-textarea name="notes" rows="4" maxlength="3000" x-model="notes" /></x-field>
                    <x-field label="Saran langkah" name="steps" hint="Satu baris satu langkah. Warga bisa menambahkannya ke Langkah 30 Hari."><x-textarea name="steps" rows="3" maxlength="2000" /></x-field>
                    <x-button variant="primary" x-bind:disabled="notes.trim().length < 10">Tandai Selesai</x-button>
                    <p class="text-sm text-fg-muted" x-show="notes.trim().length < 10">Tulis catatan minimal 10 karakter.</p>
                </form>
                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('bos.sessions.no-show', $session) }}" data-confirm="Tandai warga tidak hadir?" data-confirm-ok="Tandai Tidak Hadir">@csrf<x-button variant="secondary" size="sm" :disabled="$session->slot->starts_at->isFuture()">Tandai Tidak Hadir</x-button></form>
                    @if ($session->slot->starts_at->isFuture())
                        <form method="POST" action="{{ route('bos.sessions.cancel', $session) }}" data-confirm="Batalkan sesi ini? Warga menerima alasanmu." data-confirm-reason="reason" data-confirm-ok="Batalkan Sesi">@csrf<x-button variant="danger" size="sm">Batalkan Sesi</x-button></form>
                    @endif
                </div>
                @if ($session->slot->starts_at->isFuture())<p class="text-xs text-fg-muted">Tandai Tidak Hadir aktif setelah waktu sesi lewat.</p>@endif
            @endif
        </div>
    </div>
</x-layouts.dashboard>
