<x-layouts.public title="Para Bos" description="Pengusaha aktif yang dikurasi IPI untuk mendampingi warga Gunungkidul.">
    <section class="container-page pt-6">
        <h1 class="text-4xl sm:text-5xl">Para Bos</h1>
        <p class="mt-3 max-w-xl text-fg-muted">Pengusaha aktif yang lolos kurasi IPI: bisnis berjalan, rekam jejak jelas, pengalaman riil, dan dapat ditelusuri.</p>
        @if ($bosses->isEmpty())
            <x-empty-state icon="phosphor-user-circle" title="Belum ada Bos aktif" class="mt-8" />
        @else
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($bosses as $boss)
                    <x-boss-card :boss="$boss" :href="auth()->check() && Route::has('warga.mentoring.boss') ? route('warga.mentoring.boss', $boss) : route('register', ['tujuan' => '/warga/temu-bos'])"><span class="line-clamp-3 text-sm text-fg-body">{{ $boss->business_desc }}</span></x-boss-card>
                @endforeach
            </div>
        @endif
        @if (Route::has('become-boss'))
            <div class="mt-12"><x-button variant="secondary" :href="route('become-boss')">Jadi Bos Pendamping</x-button></div>
        @endif
    </section>
</x-layouts.public>
