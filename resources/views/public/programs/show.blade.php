<x-layouts.public :title="$program->title" :description="Str::limit($program->description, 150)">
    <section class="container-page pt-6">
        <x-back-link :href="route('programs.index')" class="mb-5">Semua Kelas</x-back-link>
        <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
            <div class="space-y-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge badge-brand">{{ $program->kind->label() }}</span>
                    @if ($program->is_official)<x-official-badge />@endif
                    <x-demo-badge :show="$program->is_demo" />
                    @if ($program->field)<x-badge>{{ $program->field->name }}</x-badge>@endif
                </div>
                <h1 class="text-4xl sm:text-5xl">{{ $program->title }}</h1>
                <div class="prose-body whitespace-pre-line">{{ $program->description }}</div>

                @if ($program->speakers->isNotEmpty())
                    <section>
                        <h2 class="mb-3 text-2xl">Pembicara</h2>
                        <ul class="grid gap-3 sm:grid-cols-2">
                            @foreach ($program->speakers as $speaker)
                                <li class="card flex items-center gap-4 p-4 sm:p-4">
                                    @if ($speaker->photoUrl())
                                        <img src="{{ $speaker->photoUrl() }}" alt="" class="h-14 w-14 rounded-full object-cover">
                                    @else
                                        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-brand font-display text-xl font-bold text-brand-fg">{{ mb_substr($speaker->name, 0, 1) }}</span>
                                    @endif
                                    <span class="min-w-0">
                                        <span class="block font-medium text-fg">{{ $speaker->name }} <x-demo-badge :show="$speaker->is_demo" /></span>
                                        <span class="block text-sm text-fg-muted">{{ $speaker->business }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section>
                    <h2 class="mb-3 text-2xl">Jadwal</h2>
                    <ol class="space-y-2">
                        @foreach ($program->sessions as $session)
                            <li class="card flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-4">
                                <span class="font-medium text-fg">{{ $session->title }}</span>
                                <span class="text-sm text-fg-muted">{{ $session->starts_at->translatedFormat('l, d F Y') }}, {{ $session->starts_at->format('H:i') }} sampai {{ $session->ends_at->format('H:i') }} WIB</span>
                            </li>
                        @endforeach
                    </ol>
                </section>

                @if ($program->materials)
                    <section>
                        <h2 class="mb-3 text-2xl">Materi</h2>
                        <ul class="space-y-2">
                            @foreach ($program->materials as $i => $material)
                                <li>
                                    @if ($enrollment)
                                        <a href="{{ route('warga.programs.material', [$program, $i]) }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-accent-text underline underline-offset-2"><x-phosphor-file-text class="h-5 w-5" aria-hidden="true" />{{ $material['title'] }}</a>
                                    @else
                                        <span class="flex items-center gap-2 text-fg-body"><x-phosphor-lock-simple class="h-5 w-5" aria-hidden="true" />{{ $material['title'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @unless ($enrollment)<p class="mt-2 text-sm text-fg-muted">Materi terbuka setelah kamu mendaftar.</p>@endunless
                    </section>
                @endif
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="card space-y-4">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-fg-muted">Biaya</dt><dd @class(['font-display text-lg font-semibold', 'text-success' => ! $program->isPaid(), 'text-fg' => $program->isPaid()])>{{ $program->priceLabel() }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-fg-muted">Lokasi</dt><dd class="text-right font-medium text-fg">{{ $program->location }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-fg-muted">Mulai</dt><dd class="text-right font-medium text-fg">{{ $program->startsAt()?->translatedFormat('d F Y, H:i') }} WIB</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-fg-muted">Sesi</dt><dd class="font-medium text-fg">{{ $program->sessions->count() }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-fg-muted">Sisa kuota</dt><dd class="font-medium text-fg">{{ $program->seatsLeft() }} dari {{ $program->quota }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-fg-muted">Status</dt><dd><x-status-badge :status="$program->status" /></dd></div>
                    </dl>

                    @if ($certificate)
                        <x-button variant="secondary" :href="route('warga.certificates.show', $certificate)" full>Lihat Sertifikat ({{ $certificate->status->label() }})</x-button>
                    @elseif ($enrollment?->status === App\Enums\EnrollmentStatus::MenungguBayar)
                        <p class="rounded-[20px] bg-warning/15 p-3 text-sm text-fg">Kursimu ditahan sampai pembayaran selesai.</p>
                        <x-button variant="primary" size="lg" :href="route('warga.programs.confirm', $program)" icon="phosphor-qr-code" full>Lanjutkan Pembayaran</x-button>
                    @elseif ($enrollment)
                        <button class="btn btn-secondary btn-lg btn-full" disabled>Sudah Terdaftar</button>
                        @unless ($program->hasStarted())
                            <form method="POST" action="{{ route('warga.programs.cancel', $program) }}" data-confirm="Batalkan pendaftaran di {{ $program->title }}? Tempatmu akan diberikan ke warga lain." data-confirm-ok="Batalkan Pendaftaran">
                                @csrf
                                <x-button variant="danger" size="sm" full>Batalkan Pendaftaran</x-button>
                            </form>
                        @endunless
                        <x-button variant="ghost" :href="route('warga.programs.index')" full>Buka Kelas Saya</x-button>
                    @elseif ($program->status === App\Enums\ProgramStatus::Penuh)
                        <button class="btn btn-primary btn-lg btn-full" disabled>Kuota Penuh</button>
                        <p class="text-sm text-fg-muted">Semua tempat sudah terisi. Pantau kelas lain di halaman Kelas Bos.</p>
                    @elseif ($program->status === App\Enums\ProgramStatus::Dibuka)
                        @auth
                            <x-button variant="primary" size="lg" :href="route('warga.programs.confirm', $program)" icon-right="phosphor-arrow-right" full>{{ $program->isPaid() ? 'Daftar dan Bayar' : 'Daftar Kelas' }}</x-button>
                        @else
                            <x-button variant="primary" size="lg" full :href="route('register', ['tujuan' => '/kelas/'.$program->slug])">Daftar Kelas</x-button>
                            <p class="text-center text-sm text-fg-muted">Sudah punya akun? <a href="{{ route('login') }}" class="text-accent-text underline underline-offset-2">Masuk</a></p>
                        @endauth
                    @else
                        <button class="btn btn-primary btn-lg btn-full" disabled>Pendaftaran Ditutup</button>
                    @endif
                </div>
            </aside>
        </div>
    </section>
</x-layouts.public>
