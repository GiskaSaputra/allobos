<x-layouts.public title="Siniar Bos" description="Obrolan bersama pengusaha tentang memulai dan menjalankan usaha.">
    <section class="container-page pt-6">
        <h1 class="text-4xl sm:text-5xl">Siniar Bos</h1>
        <p class="mt-3 max-w-xl text-fg-muted">Obrolan bersama pengusaha. Tiap episode punya ringkasan dan tiga pelajaran yang bisa langsung dicoba.</p>
        @if ($fields->isNotEmpty())
            <nav class="chip-row mt-6" aria-label="Saring bidang">
                <a href="{{ route('podcasts.index') }}" @class(['chip', 'chip-active' => ! request('bidang')])>Semua bidang</a>
                @foreach ($fields as $field)<a href="{{ route('podcasts.index', ['bidang' => $field->slug]) }}" @class(['chip', 'chip-active' => request('bidang') === $field->slug])>{{ $field->name }}</a>@endforeach
            </nav>
        @endif
        @if ($podcasts->isEmpty())
            <x-empty-state icon="phosphor-headphones" title="Belum ada episode" class="mt-8">Episode baru akan muncul di sini.</x-empty-state>
        @else
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@foreach ($podcasts as $podcast)<x-podcast-card :podcast="$podcast" />@endforeach</div>
            <div class="mt-8">{{ $podcasts->links() }}</div>
        @endif
    </section>
</x-layouts.public>
