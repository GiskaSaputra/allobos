@php($rp = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.'))
<x-layouts.app title="Rencana Usaha">
    @if (! $plan)
        <x-page-header title="Rencana Usaha" description="Rencana satu halaman: produk, pembeli, cara jual, modal, pesaing, dan risiko." />
        @if ($chosen)
            <x-card tone="sorot" class="max-w-2xl">
                <p class="label-caps">Usaha Tujuan</p>
                <p class="mt-1 font-display text-xl font-semibold text-fg">{{ $chosen->title }}</p>
                <form method="POST" action="{{ route('warga.plan.build') }}" class="mt-4">
                    @csrf
                    <x-button variant="primary" loading="Menyusun rencana" :disabled="$quotaLeft === 0">Susun Rencana</x-button>
                    @if ($quotaLeft === 0)<p class="mt-2 text-sm text-danger">Jatah menyusun rencana hari ini habis. Coba lagi besok.</p>@endif
                </form>
            </x-card>
        @else
            <x-empty-state icon="phosphor-compass" title="Pilih usaha tujuan dulu" action="Buka Peluang" :href="route('warga.opportunities.index')">Rencana disusun dari peluang yang kamu pilih atau idemu sendiri.</x-empty-state>
        @endif
        <div class="mt-6"><x-button variant="secondary" :href="route('warga.plan.cost')" icon="phosphor-calculator">Buka Kalkulator HPP</x-button></div>
    @else
        <x-page-header :title="$plan->title" description="Rencana Usaha satu halaman. Tekan Ubah pada bagian yang ingin kamu sesuaikan.">
            <x-slot:actions>
                <x-button variant="secondary" :href="route('warga.plan.pdf')" icon="phosphor-download-simple">Unduh PDF</x-button>
                <x-button variant="secondary" :href="route('warga.plan.cost')" icon="phosphor-calculator">Kalkulator HPP</x-button>
            </x-slot:actions>
        </x-page-header>
        <div class="mb-6 flex flex-wrap items-center gap-2">
            @if ($plan->is_ai)<x-ai-badge />@else<x-badge>Disusun tanpa AI</x-badge>@endif
            <span class="text-sm text-fg-muted">Disusun {{ $plan->built_at->translatedFormat('d F Y') }}</span>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach (App\Models\BusinessPlan::SECTIONS as $key => $label)
                <x-plan-section :key="$key" :label="$label" :value="$plan->$key">
                    <p class="whitespace-pre-line text-fg-body">{{ $plan->$key }}</p>
                </x-plan-section>
            @endforeach

            <x-plan-section key="capital" label="Kebutuhan modal" :value="collect($plan->capital)->map(fn ($c) => $c['name'].' - '.$c['amount'])->implode(PHP_EOL)" hint="Satu baris satu kebutuhan, format: Nama - jumlah rupiah">
                <ul class="space-y-1.5 text-fg-body">
                    @foreach ($plan->capital ?? [] as $item)
                        <li class="flex justify-between gap-4"><span>{{ $item['name'] }}</span><span class="font-medium text-fg">{{ $rp($item['amount']) }}</span></li>
                    @endforeach
                    <li class="flex justify-between gap-4 border-t border-line pt-1.5 font-medium text-fg"><span>Total</span><span>{{ $rp($plan->capitalTotal()) }}</span></li>
                </ul>
            </x-plan-section>

            <x-plan-section key="risks" label="Risiko" :value="implode(PHP_EOL, $plan->risks ?? [])" hint="Satu baris satu risiko">
                <ul class="list-disc space-y-1 pl-5 text-fg-body">@foreach ($plan->risks ?? [] as $risk)<li>{{ $risk }}</li>@endforeach</ul>
            </x-plan-section>
        </div>

        <div class="mt-8 flex flex-wrap gap-2">
            <x-button variant="primary" :href="route('warga.steps.index')" icon="phosphor-list-checks">Buka Langkah 30 Hari</x-button>
            <x-button variant="ghost" :href="route('warga.opportunities.index')">Ganti Usaha Tujuan</x-button>
        </div>
    @endif
</x-layouts.app>
