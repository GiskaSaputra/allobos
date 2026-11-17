@php($program = $payment->enrollment->program)
<x-layouts.app title="Status Pembayaran">
    <div class="mx-auto max-w-xl">
        <section class="card space-y-5 text-center">
            @if ($payment->status === App\Enums\PaymentStatus::Lunas)
                <x-phosphor-seal-check class="mx-auto h-16 w-16 text-success" aria-hidden="true" />
                <h1 class="text-2xl">Pembayaran berhasil</h1>
                <p class="text-fg-body">Kamu terdaftar di <strong class="text-fg">{{ $program->title }}</strong>. Pengingat dikirim sehari sebelum sesi.</p>
                <x-button variant="primary" :href="route('warga.programs.index')" full>Buka Kelas Saya</x-button>
            @elseif ($payment->status === App\Enums\PaymentStatus::Menunggu)
                {{-- Status diperbarui webhook SumoPod. Halaman dimuat ulang sendiri sampai pembayaran tercatat. --}}
                <meta http-equiv="refresh" content="6">
                <x-phosphor-hourglass-medium class="mx-auto h-16 w-16 text-warning" aria-hidden="true" />
                <h1 class="text-2xl">Menunggu konfirmasi pembayaran</h1>
                <p class="text-fg-body">Bila kamu sudah membayar, halaman ini berganti otomatis dalam beberapa detik. Bila belum, buka lagi halaman pembayaran.</p>
                @if ($payment->isOpen())
                    <x-button variant="primary" :href="$payment->payment_url" icon="phosphor-qr-code" full>Buka Halaman Pembayaran</x-button>
                @endif
            @else
                <x-phosphor-x-circle class="mx-auto h-16 w-16 text-danger" aria-hidden="true" />
                <h1 class="text-2xl">Pembayaran {{ strtolower($payment->status->label()) }}</h1>
                <p class="text-fg-body">Kursi di {{ $program->title }} sudah dilepas. Kamu bisa mendaftar dan membayar lagi selama kuota masih ada.</p>
                <x-button variant="primary" :href="route('warga.programs.confirm', $program)" full>Daftar Lagi</x-button>
            @endif

            <dl class="grid gap-2 border-t border-line pt-4 text-left text-sm">
                <div class="flex justify-between gap-4"><dt class="text-fg-muted">Kelas</dt><dd class="text-right font-medium text-fg">{{ $program->title }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-fg-muted">Nomor pesanan</dt><dd class="font-mono text-fg">{{ $payment->order_id }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-fg-muted">Jumlah</dt><dd class="font-medium text-fg">Rp{{ number_format($payment->amount, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-fg-muted">Status</dt><dd><x-status-badge :status="$payment->status" /></dd></div>
            </dl>
        </section>
    </div>
</x-layouts.app>
