<x-layouts.dashboard title="Kurasi Bos">
    <x-page-header title="Kurasi Bos" description="Empat standar IPI: bisnis aktif saat ini, rekam jejak jelas, pengalaman riil, dan dapat ditelusuri." />
    <form method="POST" action="{{ route('pengelola.bosses.invite') }}" class="card mb-6 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
        @csrf
        <x-field label="Undang pengusaha lewat email" name="email" hint="Tautan undangan bertanda tangan, berlaku 7 hari."><x-text-input name="email" type="email" required placeholder="nama@usaha.id" /></x-field>
        <x-button variant="primary" icon="phosphor-envelope-simple">Undang Bos</x-button>
    </form>
    <nav class="chip-row mb-4" aria-label="Saring status">
        @foreach (App\Enums\BossStatus::cases() as $case)
            <a href="{{ route('pengelola.bosses.index', ['status' => $case->value]) }}" @class(['chip', 'chip-active' => $status === $case->value])>{{ $case->label() }} ({{ $counts[$case->value] ?? 0 }})</a>
        @endforeach
    </nav>
    @if ($bosses->isEmpty())
        <x-empty-state icon="phosphor-user-check" title="Tidak ada profil dengan status ini" />
    @else
        <div class="grid gap-4">
            @foreach ($bosses as $boss)
                <article class="card grid gap-4 lg:grid-cols-[1fr_320px]">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2"><h2 class="text-xl">{{ $boss->user->name }}</h2><x-status-badge :status="$boss->status" /><x-demo-badge :show="$boss->is_demo" /></div>
                        <p class="text-sm text-fg-muted">{{ $boss->business_name }}, {{ $boss->field?->name }}, {{ $boss->years_running }} tahun{{ $boss->district ? ', '.$boss->district->name : '' }}. {{ $boss->user->email }}</p>
                        <p class="text-fg-body">{{ $boss->business_desc }}</p>
                        @if ($boss->track_record)<p class="text-sm text-fg-body"><strong class="text-fg">Rekam jejak:</strong> {{ $boss->track_record }}</p>@endif
                        @if ($boss->trace_url)<a href="{{ $boss->trace_url }}" target="_blank" rel="noopener nofollow" class="text-sm text-accent-text underline underline-offset-2">{{ $boss->trace_url }}</a>@endif
                        @if ($boss->reject_reason)<p class="text-sm text-danger">Alasan ditolak: {{ $boss->reject_reason }}</p>@endif
                    </div>
                    @if ($boss->status !== App\Enums\BossStatus::Aktif)
                        <div class="space-y-3">
                            <form method="POST" action="{{ route('pengelola.bosses.activate', $boss) }}" class="space-y-2" x-data="{ checks: {} }">
                                @csrf
                                <p class="text-sm font-medium text-fg">Daftar periksa kurasi</p>
                                @foreach (App\Models\BossProfile::STANDARDS as $key => $label)
                                    <label class="flex items-center gap-2 text-sm text-fg-body"><input type="checkbox" name="checklist[{{ $key }}]" value="1" class="field-check" x-model="checks.{{ $key }}">{{ $label }}</label>
                                @endforeach
                                <x-button variant="primary" size="sm" x-bind:disabled="Object.values(checks).filter(Boolean).length < 4">Aktifkan</x-button>
                            </form>
                            <form method="POST" action="{{ route('pengelola.bosses.reject', $boss) }}" data-confirm="Tolak profil {{ $boss->user->name }}? Calon Bos menerima alasanmu dan bisa mengajukan ulang." data-confirm-reason="reason" data-confirm-ok="Tolak">@csrf<x-button variant="danger" size="sm">Tolak</x-button></form>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $bosses->links() }}</div>
    @endif
</x-layouts.dashboard>
