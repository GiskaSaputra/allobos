<x-layouts.dashboard title="Ringkasan Admin">
    <x-page-header title="Ringkasan Admin" description="Pengguna, pemakaian AI hari ini, antrean, dan catatan audit." />
    @unless ($aiConfigured)<p class="mb-6 rounded-[20px] bg-warning/15 p-4 text-sm text-fg">GEMINI_API_KEY belum diisi. Semua fitur AI memakai jalur cadangan tanpa AI.</p>@endunless
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($roles as [$label, $count])<x-card><p class="text-sm text-fg-muted">{{ $label }}</p><p class="font-display text-3xl font-bold text-fg">{{ $count }}</p></x-card>@endforeach
    </div>
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-card>
            <h2 class="text-xl">AI hari ini</h2>
            @if ($ai->isEmpty())
                <p class="mt-3 text-sm text-fg-muted">Belum ada permintaan AI hari ini.</p>
            @else
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($ai as $feature => $rows)
                        <li class="flex justify-between gap-3"><span class="text-fg">{{ config("ai.features.$feature.label") }}</span><span class="text-fg-muted">@foreach ($rows as $r){{ $r->status }} {{ $r->total }} ({{ (int) $r->avg_ms }} ms)@if (! $loop->last), @endif @endforeach</span></li>
                    @endforeach
                </ul>
            @endif
        </x-card>
        <x-card>
            <h2 class="text-xl">Antrean</h2>
            <p class="mt-3 text-sm text-fg-body">{{ $pendingJobs }} pekerjaan menunggu, {{ $failedJobs }} gagal.</p>
            <h2 class="mt-6 text-xl">Audit terbaru</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse ($audit as $log)<li class="flex justify-between gap-3"><span class="text-fg">{{ $log->action }} <span class="text-fg-muted">{{ $log->user?->name }}</span></span><span class="text-fg-muted">{{ $log->created_at->diffForHumans() }}</span></li>@empty<li class="text-fg-muted">Belum ada catatan.</li>@endforelse
            </ul>
            <x-button variant="ghost" size="sm" :href="route('admin.audit')" class="mt-3">Lihat Audit</x-button>
        </x-card>
    </div>
</x-layouts.dashboard>
