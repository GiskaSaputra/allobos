<x-layouts.public :title="$podcast->title" :description="Str::limit($podcast->summary, 150)">
    <section class="container-page pt-6">
        <x-back-link :href="route('podcasts.index')" class="mb-5">Semua Siniar</x-back-link>
        <div class="grid gap-8 lg:grid-cols-[1fr_340px]">
            <div class="space-y-6">
                <div class="overflow-hidden rounded-card border border-line bg-ink" x-data="{ play: false }">
                    <div class="relative aspect-video">
                        <template x-if="play">
                            <iframe class="absolute inset-0 h-full w-full" src="https://www.youtube-nocookie.com/embed/{{ $podcast->youtube_id }}?autoplay=1&rel=0" title="{{ $podcast->title }}" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </template>
                        <button type="button" x-show="!play" @click="play = true" class="group absolute inset-0 flex items-center justify-center" aria-label="Putar video {{ $podcast->title }}">
                            <img src="{{ $podcast->thumbnail() }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-90 transition group-hover:opacity-100">
                            <span class="relative flex h-[72px] w-[72px] items-center justify-center rounded-full bg-accent text-accent-fg shadow-float transition group-hover:scale-105"><x-phosphor-play-fill class="h-8 w-8" aria-hidden="true" /></span>
                        </button>
                    </div>
                </div>
                <p class="text-sm text-fg-muted">Pemutar YouTube baru dimuat setelah kamu menekan Putar, supaya hemat kuota.</p>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($podcast->field)<x-badge>{{ $podcast->field->name }}</x-badge>@endif
                    @if ($podcast->durationLabel())<x-badge>{{ $podcast->durationLabel() }}</x-badge>@endif
                    <x-demo-badge :show="$podcast->is_demo" />
                </div>
                <h1 class="text-3xl sm:text-4xl">{{ $podcast->title }}</h1>
                <section class="space-y-3">
                    <div class="flex items-center gap-2"><h2 class="text-2xl">Ringkasan</h2>@if ($podcast->summary_is_ai)<span class="badge badge-info"><x-phosphor-sparkle class="h-3.5 w-3.5" aria-hidden="true" />Dibantu AI, diperiksa Pengelola IPI</span>@endif</div>
                    <p class="max-w-[68ch] text-fg-body">{{ $podcast->summary }}</p>
                </section>
                <section>
                    <h2 class="mb-3 text-2xl">Tiga pelajaran</h2>
                    <ol class="grid gap-3 sm:grid-cols-3">
                        @foreach ($podcast->lessons ?? [] as $i => $lesson)
                            <li class="card p-4 sm:p-5"><span class="font-display text-3xl font-bold text-accent-text">{{ $i + 1 }}</span><p class="mt-2 text-fg-body">{{ $lesson }}</p></li>
                        @endforeach
                    </ol>
                </section>
            </div>
            <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
                @if ($isWarga)
                    <div class="card space-y-4">
                        @if ($listen?->listened_at)
                            <button class="btn btn-secondary btn-full" disabled><x-phosphor-check class="h-5 w-5" aria-hidden="true" />Sudah Didengar</button>
                        @else
                            <form method="POST" action="{{ route('warga.podcasts.listen', $podcast) }}">@csrf<x-button variant="primary" full>Tandai Sudah Didengar</x-button></form>
                        @endif
                        <form method="POST" action="{{ route('warga.podcasts.note', $podcast) }}" class="space-y-3">
                            @csrf
                            <x-field label="Catatan pribadimu" name="note"><x-textarea name="note" :value="$listen?->note" rows="4" maxlength="2000" placeholder="Apa yang mau kamu coba dari episode ini?" /></x-field>
                            <x-button variant="secondary">Simpan Catatan</x-button>
                        </form>
                    </div>
                @elseif (! auth()->check())
                    <div class="card space-y-3"><p class="text-fg-body">Masuk sebagai warga untuk menandai episode dan menyimpan catatan.</p><x-button variant="primary" :href="route('register', ['tujuan' => '/siniar/'.$podcast->slug])" full>Daftar</x-button></div>
                @endif
                @if ($more->isNotEmpty())
                    <div class="space-y-3"><h2 class="text-xl">Episode lain</h2>@foreach ($more as $item)<x-podcast-card :podcast="$item" compact />@endforeach</div>
                @endif
            </aside>
        </div>
    </section>
</x-layouts.public>
