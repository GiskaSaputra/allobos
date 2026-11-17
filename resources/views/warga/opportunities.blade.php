<x-layouts.app title="Peluang Usaha">
    <x-page-header title="Peluang Usaha" description="Bos Allo mencari tiga usaha yang cocok dengan profil, kapanewon, dan riwayat belajarmu.">
        <x-slot:actions>
            <form method="POST" action="{{ route('warga.opportunities.generate') }}">
                @csrf
                <x-button variant="primary" size="lg" icon="phosphor-sparkle" loading="Mencari peluang" :disabled="$quotaLeft === 0">{{ $items->isEmpty() ? 'Buka Peluang' : 'Buka Peluang Baru' }}</x-button>
            </form>
        </x-slot:actions>
    </x-page-header>
    @if ($quotaLeft === 0)
        <p class="mb-6 text-sm text-danger">Jatah Buka Peluang hari ini sudah habis. Bisa dipakai lagi besok pukul 00.00 WIB.</p>
    @endif

    @if ($chosen)
        <x-card tone="sorot" class="mb-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="label-caps">Usaha Tujuan</p>
                    <p class="mt-1 font-display text-xl font-semibold text-fg">{{ $chosen->title }}</p>
                </div>
                <form method="POST" action="{{ route('warga.plan.build') }}">
                    @csrf
                    <x-button variant="primary" loading="Menyusun rencana">{{ $hasPlan ? 'Susun Ulang Rencana' : 'Susun Rencana' }}</x-button>
                </form>
            </div>
        </x-card>
    @endif

    @if ($items->isEmpty())
        <x-empty-state icon="phosphor-compass" title="Belum ada peluang">Tekan Buka Peluang. Makin banyak kamu berkonsultasi, belajar, dan bermain simulasi, makin tepat sarannya.</x-empty-state>
    @else
        <div class="grid gap-4 lg:grid-cols-3">
            @foreach ($items as $item)
                <article @class(['card flex flex-col gap-4', 'border-brand ring-2 ring-brand/40' => $item->chosen_at])>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($item->is_ai)<x-ai-badge />@else<x-badge>Tanpa AI</x-badge>@endif
                        @if ($item->field)<x-badge>{{ $item->field->name }}</x-badge>@endif
                    </div>
                    <h2 class="text-xl">{{ $item->title }}</h2>
                    <dl class="space-y-3 text-sm">
                        <div><dt class="font-medium text-fg">Kenapa cocok</dt><dd class="text-fg-body">{{ $item->why }}</dd></div>
                        <div><dt class="font-medium text-fg">Perkiraan modal</dt><dd class="text-fg-body">{{ $item->capital_range }}</dd></div>
                        <div><dt class="font-medium text-fg">Risiko utama</dt><dd class="text-fg-body">{{ $item->risk }}</dd></div>
                    </dl>
                    <form method="POST" action="{{ route('warga.opportunities.choose', $item) }}" class="mt-auto">
                        @csrf
                        <x-button :variant="$item->chosen_at ? 'secondary' : 'primary'" full :disabled="(bool) $item->chosen_at">{{ $item->chosen_at ? 'Sudah Dipilih' : 'Pilih Usaha Ini' }}</x-button>
                    </form>
                </article>
            @endforeach
        </div>
        @unless ($items->first()->is_ai)
            <p class="mt-4 text-sm text-fg-muted">Layanan AI sedang tidak tersedia, jadi peluang di atas dicocokkan dari kata kunci profilmu terhadap bidang usaha IPI.</p>
        @endunless
    @endif

    <section class="mt-10" x-data="{ open: {{ $errors->has('title') ? 'true' : 'false' }} }">
        <button type="button" class="btn btn-ghost" @click="open = !open" :aria-expanded="open"><x-phosphor-pencil-simple class="h-5 w-5" aria-hidden="true" />Tulis Ide Sendiri</button>
        <form x-show="open" x-cloak method="POST" action="{{ route('warga.opportunities.generate') }}" class="card mt-4 max-w-2xl space-y-4">
            @csrf
            <x-field label="Nama usaha yang kamu inginkan" name="title" required><x-text-input name="title" maxlength="120" required /></x-field>
            <x-field label="Kenapa kamu ingin usaha ini" name="why"><x-textarea name="why" rows="3" maxlength="500" /></x-field>
            <x-button variant="primary">Simpan sebagai Usaha Tujuan</x-button>
        </form>
    </section>
</x-layouts.app>
