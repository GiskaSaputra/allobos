<x-layouts.dashboard title="Wawasan">
    <x-page-header title="Wawasan" description="Agregat tanpa identitas pribadi, dihitung langsung dari basis data.">
        <x-slot:actions><x-button variant="secondary" :href="route('pengelola.insights', ['unduh' => 'xlsx'])" icon="phosphor-download-simple">Unduh Wawasan (XLSX)</x-button></x-slot:actions>
    </x-page-header>
    <div class="grid gap-6 lg:grid-cols-2">
        @foreach ($sections as $key => $section)
            <x-card>
                <h2 class="text-xl">{{ $section['title'] }}</h2>
                @if (empty($section['rows']))
                    <p class="mt-3 text-sm text-fg-muted">Belum ada data.</p>
                @else
                    <x-chart class="mt-4" :label="$section['title']" :config="['type' => 'bar', 'horizontal' => true, 'labels' => array_map(fn ($r) => Str::limit($r[0], 28), $section['rows']), 'series' => [['label' => 'Jumlah', 'data' => array_column($section['rows'], 1), 'color' => 'brand']]]" />
                    <table class="sr-only"><caption>{{ $section['title'] }}</caption>@foreach ($section['rows'] as [$name, $value])<tr><th>{{ $name }}</th><td>{{ $value }}</td></tr>@endforeach</table>
                @endif
            </x-card>
        @endforeach
    </div>
</x-layouts.dashboard>
