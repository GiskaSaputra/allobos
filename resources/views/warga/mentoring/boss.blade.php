<x-layouts.app :title="$boss->user->name">
    <div class="grid gap-8 lg:grid-cols-[1fr_420px]">
        <section class="space-y-6">
            <div class="flex items-center gap-4">
                @if ($boss->photo_path)<img src="{{ Storage::disk('public')->url($boss->photo_path) }}" alt="" class="h-20 w-20 rounded-full object-cover">@endif
                <div>
                    <h1>{{ $boss->user->name }}</h1>
                    <p class="text-fg-muted">{{ $boss->business_name }}{{ $boss->years_running ? ', berjalan '.$boss->years_running.' tahun' : '' }}</p>
                    <div class="mt-1 flex flex-wrap gap-2">@if ($boss->field)<x-badge>{{ $boss->field->name }}</x-badge>@endif<x-demo-badge :show="$boss->is_demo" /></div>
                </div>
            </div>
            <div class="prose-body whitespace-pre-line">{{ $boss->business_desc }}</div>
            @if ($boss->track_record)<x-card><h2 class="text-xl">Rekam jejak</h2><p class="mt-2 whitespace-pre-line text-fg-body">{{ $boss->track_record }}</p></x-card>@endif
            @if ($ratings->isNotEmpty())
                <section>
                    <h2 class="text-xl">Penilaian warga ({{ number_format($boss->rating_avg, 1, ',', '') }} dari 5, {{ $boss->rating_count }} sesi)</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($ratings as $r)<li class="card p-4 sm:p-4 text-sm"><span class="font-medium text-fg">{{ $r->rating }} dari 5</span>@if ($r->rating_comment)<span class="text-fg-body">. {{ $r->rating_comment }}</span>@endif</li>@endforeach
                    </ul>
                </section>
            @endif
        </section>

        <aside>
            <form method="POST" x-data="{ slot: '{{ old('slot') }}', topic: @js(old('topic', '')) }" :action="slot ? '{{ url('warga/temu-bos/slot') }}/' + slot : '#'" class="card space-y-5 lg:sticky lg:top-24">
                @csrf
                <h2 class="text-xl">Ajukan sesi</h2>
                @if ($slots->isEmpty())
                    <p class="text-sm text-fg-muted">Bos ini belum membuka slot. Coba Bos lain atau kembali nanti.</p>
                @else
                    <fieldset>
                        <legend class="mb-2 text-sm font-medium text-fg">Pilih slot</legend>
                        <div class="grid gap-2">
                            @foreach ($slots as $slot)
                                @php($free = in_array($slot->id, $bookable, true))
                                <label>
                                    <input type="radio" name="slot" value="{{ $slot->id }}" class="peer sr-only" x-model="slot" @disabled(! $free)>
                                    <span class="flex cursor-pointer items-center justify-between rounded-full border border-line px-4 py-2.5 text-sm transition peer-checked:border-brand peer-checked:bg-brand peer-checked:text-brand-fg peer-disabled:cursor-not-allowed peer-disabled:opacity-50 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-accent-text">
                                        <span>{{ $slot->starts_at->translatedFormat('D, d M H:i') }} ({{ $slot->duration_minutes }} menit)</span>
                                        <span>{{ $free ? App\Models\BossSlot::MODES[$slot->mode] : 'Terisi' }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-fg-muted">Slot bisa dipesan paling lambat 12 jam sebelum waktunya.</p>
                    </fieldset>
                    <x-field label="Topik yang ingin dibahas" name="topic" required>
                        <x-textarea name="topic" rows="4" maxlength="1000" x-model="topic" placeholder="Contoh: saya ingin menjual keripik ke toko oleh-oleh di Pantai Baron, bagaimana caranya?" />
                    </x-field>
                    @if ($notes->isNotEmpty())
                        <x-field label="Lampirkan Catatan Konsultasi" name="consult_note_id" hint="Supaya Bos tidak mulai dari nol.">
                            <x-select name="consult_note_id" :options="$notes->mapWithKeys(fn ($n) => [$n->id => $n->session->topic.' ('.$n->created_at->translatedFormat('d M').')'])" :value="$noteId" placeholder="Tanpa lampiran" />
                        </x-field>
                    @endif
                    <x-button variant="primary" full loading="Mengajukan" x-bind:disabled="!slot || topic.trim().length < 10">Ajukan Sesi</x-button>
                    <p class="text-sm text-fg-muted" x-show="!slot || topic.trim().length < 10">Pilih slot dan tulis topik minimal 10 karakter.</p>
                @endif
            </form>
        </aside>
    </div>
</x-layouts.app>
