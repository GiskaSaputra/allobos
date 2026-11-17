@php($status = $session->status)
<x-layouts.app title="Sesi Temu Bos">
    <x-page-header :title="'Sesi dengan '.$session->boss->user->name" :description="$session->slot->starts_at->translatedFormat('l, d F Y, H:i').' WIB, '.$session->slot->duration_minutes.' menit, '.Str::lower(App\Models\BossSlot::MODES[$session->slot->mode]).'.'" />
    <div class="grid max-w-3xl gap-4">
        <x-card>
            <div class="flex items-center justify-between gap-2"><h2 class="text-xl">Status</h2><x-status-badge :status="$status" /></div>
            @if ($session->reason && in_array($status, [App\Enums\MentoringStatus::Ditolak, App\Enums\MentoringStatus::Dibatalkan], true))<p class="mt-2 text-fg-body">Alasan: {{ $session->reason }}</p>@endif
            @if ($status === App\Enums\MentoringStatus::Menunggu)<p class="mt-2 text-fg-muted">Menunggu jawaban Bos, paling lambat 48 jam.</p>@endif
            @if ($status === App\Enums\MentoringStatus::Disetujui)
                @if ($session->meeting_link)
                    <a href="{{ $session->meeting_link }}" target="_blank" rel="noopener" class="btn btn-primary mt-4"><x-phosphor-video-camera class="h-5 w-5" aria-hidden="true" />Buka Tautan Rapat</a>
                @else
                    <p class="mt-3 text-fg-body"><strong class="text-fg">Alamat:</strong> {{ $session->address }}</p>
                @endif
            @endif
        </x-card>
        <x-card>
            <h2 class="text-xl">Topik</h2>
            <p class="mt-2 whitespace-pre-line text-fg-body">{{ $session->topic }}</p>
            @if ($session->note)<p class="mt-3 text-sm text-fg-muted">Lampiran: <a class="text-accent-text underline" href="{{ route('boss-room.note', $session->note->boss_session_id) }}">Catatan Konsultasi</a></p>@endif
        </x-card>
        @if ($status === App\Enums\MentoringStatus::Selesai)
            <x-card tone="sorot">
                <h2 class="text-xl">Catatan dari Bos</h2>
                <p class="mt-2 whitespace-pre-line text-fg-body">{{ $session->boss_notes }}</p>
                @if ($session->boss_steps)
                    <h3 class="mt-4">Saran langkah</h3>
                    <ol class="mt-2 list-decimal space-y-1 pl-5 text-fg-body">@foreach ($session->boss_steps as $step)<li>{{ $step }}</li>@endforeach</ol>
                    <form method="POST" action="{{ route('warga.mentoring.steps', $session) }}" class="mt-4">@csrf<x-button variant="secondary" size="sm">Tambahkan ke Langkah 30 Hari</x-button></form>
                @endif
            </x-card>
            <x-card>
                <h2 class="text-xl">Penilaian</h2>
                @if ($session->rating)
                    <p class="mt-2 text-fg-body">Kamu memberi {{ $session->rating }} dari 5.{{ $session->rating_comment ? ' '.$session->rating_comment : '' }}</p>
                @else
                    <form method="POST" action="{{ route('warga.mentoring.rate', $session) }}" class="mt-3 space-y-4" x-data="{ r: 0 }">
                        @csrf
                        <fieldset><legend class="mb-2 text-sm font-medium text-fg">Seberapa membantu sesi ini?</legend>
                            <div class="segment">
                                @for ($i = 1; $i <= 5; $i++)<label><input type="radio" name="rating" value="{{ $i }}" class="segment-input sr-only" x-model="r"><span class="segment-item">{{ $i }}</span></label>@endfor
                            </div>
                        </fieldset>
                        <x-field label="Komentar (boleh kosong)" name="comment"><x-textarea name="comment" rows="2" maxlength="500" /></x-field>
                        <x-button variant="primary" x-bind:disabled="r == 0">Kirim Penilaian</x-button>
                    </form>
                @endif
            </x-card>
        @endif
        @if (in_array($status, [App\Enums\MentoringStatus::Menunggu, App\Enums\MentoringStatus::Disetujui], true) && $session->slot->starts_at->isFuture())
            @php($canCancel = $session->slot->starts_at->gte(now()->addHours(6)))
            <form method="POST" action="{{ route('warga.mentoring.cancel', $session) }}" data-confirm="Batalkan sesi ini? Bos akan diberi tahu dan slot terbuka lagi." data-confirm-ok="Batalkan Sesi">
                @csrf
                <x-button variant="danger" size="sm" :disabled="! $canCancel">Batalkan Sesi</x-button>
                @unless ($canCancel)<p class="mt-2 text-sm text-fg-muted">Pembatalan hanya bisa paling lambat 6 jam sebelum sesi.</p>@endunless
            </form>
        @endif
    </div>
</x-layouts.app>
