<x-layouts.app title="Konfirmasi Pendaftaran">
    <x-page-header title="Konfirmasi pendaftaran" description="Periksa jadwal dan biaya sebelum mendaftar." />

    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <section class="card space-y-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="label-caps">{{ $program->kind->label() }}</p>
                    <h2 class="mt-1 text-2xl">{{ $program->title }}</h2>
                    @if ($program->speakers->isNotEmpty())<p class="mt-1 text-sm text-fg-muted">Bersama {{ $program->speakers->pluck('name')->join(', ', ' dan ') }}</p>@endif
                </div>
                <span @class(['badge text-sm', 'badge-success' => ! $program->isPaid(), 'badge-brand' => $program->isPaid()])>{{ $program->priceLabel() }}</span>
            </div>

            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div class="rounded-[20px] bg-surface-alt p-4"><dt class="text-fg-muted">Lokasi</dt><dd class="mt-1 font-medium text-fg">{{ $program->location }}</dd></div>
                <div class="rounded-[20px] bg-surface-alt p-4"><dt class="text-fg-muted">Sisa kuota</dt><dd class="mt-1 font-medium text-fg">{{ $program->seatsLeft() }} dari {{ $program->quota }}</dd></div>
            </dl>

            <div>
                <h3 class="text-base">Jadwal sesi</h3>
                <ol class="mt-2 grid gap-2">
                    @foreach ($program->sessions as $session)
                        <li class="flex items-center gap-3 rounded-[20px] border border-line p-3 text-sm">
                            <x-phosphor-calendar-blank class="h-5 w-5 shrink-0 text-fg-muted" aria-hidden="true" />
                            <span><span class="block font-medium text-fg">{{ $session->title }}</span><span class="text-fg-muted">{{ $session->starts_at->translatedFormat('l, d F Y, H:i') }} sampai {{ $session->ends_at->format('H:i') }} WIB</span></span>
                        </li>
                    @endforeach
                </ol>
                <p class="mt-2 text-xs text-fg-muted">Sertifikat diberikan bila hadir minimal 75 persen sesi.</p>
            </div>
        </section>

        <aside class="lg:sticky lg:top-24 lg:self-start">
            <form method="POST" action="{{ route('warga.programs.enroll', $program) }}" class="card space-y-4">
                @csrf
                <div class="flex items-baseline justify-between gap-3">
                    <span class="text-sm text-fg-muted">Biaya kelas</span>
                    <span class="font-display text-2xl font-semibold text-fg">{{ $program->priceLabel() }}</span>
                </div>

                @if ($program->isPaid())
                    <div class="flex gap-3 rounded-[20px] bg-surface-alt p-4 text-sm text-fg-body">
                        <x-phosphor-qr-code class="h-6 w-6 shrink-0 text-fg" aria-hidden="true" />
                        <p>Pembayaran memakai QRIS lewat SumoPod. Pindai dengan aplikasi bank atau dompet digital apa pun. Kursimu ditahan selama satu jam sampai pembayaran selesai.</p>
                    </div>
                    @if ($pending?->isOpen())
                        <p class="rounded-[20px] bg-warning/15 p-3 text-sm text-fg">Kamu punya pembayaran yang belum selesai sampai {{ $pending->expires_at->timezone('Asia/Jakarta')->format('H:i') }} WIB. Tombol di bawah membuka halaman pembayaran yang sama.</p>
                    @endif
                @endif

                <label class="flex items-start gap-3 text-sm text-fg-body">
                    <input type="checkbox" name="agree" value="1" class="field-check mt-0.5" required>
                    <span>Saya bisa hadir sesuai jadwal di atas{{ $program->isPaid() ? ' dan memahami biaya kelas tidak bisa dikembalikan setelah kelas dimulai' : '' }}.</span>
                </label>
                @error('agree')<p class="text-sm text-danger">{{ $message }}</p>@enderror

                <x-button variant="primary" size="lg" full :loading="$program->isPaid() ? 'Menyiapkan pembayaran' : 'Mendaftar'" :icon="$program->isPaid() ? 'phosphor-qr-code' : 'phosphor-check-circle'">{{ $program->isPaid() ? ($pending?->isOpen() ? 'Lanjutkan Pembayaran' : 'Bayar dengan QRIS') : 'Konfirmasi Pendaftaran' }}</x-button>
                <x-button variant="ghost" :href="route('programs.show', $program)" full>Kembali ke Detail Kelas</x-button>
            </form>
        </aside>
    </div>
</x-layouts.app>
