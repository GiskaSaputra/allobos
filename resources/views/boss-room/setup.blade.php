<x-layouts.app title="Ruang Bos">
    <div class="grid gap-8 lg:grid-cols-[1fr_380px]">
        <section>
            <x-page-header title="Ruang Bos" description="Ngobrol langsung dengan Bos Allo soal usahamu. Kamu bisa bicara lewat mikrofon atau mengetik." />
            <form method="POST" action="{{ route('boss-room.store') }}" class="space-y-8" x-data="{ topic: @js(old('topic', '')), custom: @js(old('custom_topic', '')), mode: '{{ old('mode', 'suara') }}', voice: true }"
                  x-init="voice = !!(window.SpeechRecognition || window.webkitSpeechRecognition); if (!voice) mode = 'teks'">
                @csrf
                <fieldset class="space-y-3">
                    <legend class="font-display text-xl font-semibold text-fg">Mau bahas apa?</legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($topics as $item)
                            <label>
                                <input type="radio" name="topic" value="{{ $item }}" class="peer sr-only" x-model="topic" @change="custom = ''">
                                <span class="chip cursor-pointer peer-checked:border-brand peer-checked:bg-brand peer-checked:text-brand-fg peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-accent-text">{{ $item }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-field label="Atau tulis topikmu sendiri" name="custom_topic">
                        <x-text-input name="custom_topic" maxlength="160" x-model="custom" @input="if (custom) topic = ''" placeholder="Contoh: keripik saya kurang laku di pasar" />
                    </x-field>
                    @error('topic')<p class="text-sm text-danger">{{ $message }}</p>@enderror
                </fieldset>

                <fieldset class="space-y-3">
                    <legend class="font-display text-xl font-semibold text-fg">Cara ngobrol</legend>
                    <div class="segment" role="radiogroup" aria-label="Pilihan mode">
                        <label><input type="radio" name="mode" value="suara" class="segment-input sr-only" x-model="mode" :disabled="!voice"><span class="segment-item"><x-phosphor-microphone class="h-5 w-5" aria-hidden="true" />Suara</span></label>
                        <label><input type="radio" name="mode" value="teks" class="segment-input sr-only" x-model="mode"><span class="segment-item"><x-phosphor-keyboard class="h-5 w-5" aria-hidden="true" />Teks</span></label>
                    </div>
                    <p class="text-sm text-fg-muted" x-show="!voice" x-cloak>Peramban ini belum mendukung pengenalan suara, jadi mode teks dipakai. Jawaban Bos Allo tetap bisa dibacakan bila suaranya tersedia.</p>
                </fieldset>

                <div class="space-y-2">
                    <x-button variant="primary" size="lg" icon="phosphor-chat-circle-dots" loading="Menyiapkan" :disabled="$sessionsLeft === 0" x-bind:disabled="{{ $sessionsLeft === 0 ? 'true' : 'false' }} || (!topic && !custom.trim())">Mulai Bicara dengan Bos Allo</x-button>
                    @if ($sessionsLeft === 0)
                        <p class="text-sm text-danger">Jatah sesi hari ini sudah habis. Bisa dipakai lagi {{ $resetsAt->translatedFormat('l') }} pukul 00.00 WIB.</p>
                    @else
                        <p class="text-sm text-fg-muted">Sisa {{ $sessionsLeft }} sesi hari ini{{ $turnsLeft !== null ? ', '.$turnsLeft.' giliran tanya jawab' : '' }}.</p>
                    @endif
                </div>
            </form>
        </section>

        <aside class="space-y-4">
            <x-boss-avatar />
            <x-card>
                <h2 class="text-xl">Yang perlu kamu tahu</h2>
                <ul class="mt-3 space-y-2 text-sm text-fg-body">
                    <li class="flex gap-2"><x-phosphor-check class="mt-0.5 h-4 w-4 shrink-0 text-success" aria-hidden="true" />Bos Allo adalah karakter AI. Jawabannya bahan pertimbangan, bukan keputusan.</li>
                    <li class="flex gap-2"><x-phosphor-check class="mt-0.5 h-4 w-4 shrink-0 text-success" aria-hidden="true" />Soal hukum, pajak, dan pinjaman, Bos Allo akan menyarankan Temu Bos atau lembaga resmi.</li>
                    <li class="flex gap-2"><x-phosphor-check class="mt-0.5 h-4 w-4 shrink-0 text-success" aria-hidden="true" />Akhiri sesi untuk mendapat Catatan Konsultasi yang bisa diunduh.</li>
                </ul>
            </x-card>
            @if ($recent->isNotEmpty())
                <x-card>
                    <div class="flex items-center justify-between"><h2 class="text-xl">Sesi terakhir</h2><a href="{{ route('boss-room.history') }}" class="text-sm text-accent-text underline underline-offset-2">Riwayat</a></div>
                    <ul class="mt-3 space-y-2">
                        @foreach ($recent as $item)
                            <li><a href="{{ route('boss-room.show', $item) }}" class="flex items-center justify-between gap-2 rounded-2xl px-2 py-2 text-sm hover:bg-fg/5"><span class="truncate text-fg">{{ $item->topic }}</span><x-status-badge :status="$item->status" /></a></li>
                        @endforeach
                    </ul>
                </x-card>
            @endif
        </aside>
    </div>
</x-layouts.app>
