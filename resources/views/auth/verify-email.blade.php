<x-layouts.auth title="Pastikan email">
    <h1 class="text-3xl">Pastikan email kamu</h1>
    <p class="mt-3 text-fg-body">Kami sudah mengirim tautan ke <strong class="text-fg">{{ auth()->user()->email }}</strong>. Buka email itu dan tekan tombol Pastikan Email. Periksa juga folder spam.</p>
    @if (session('status') === 'verification-link-sent')
        <p class="mt-6 rounded-[20px] bg-success/15 p-4 text-sm text-success">Tautan baru sudah dikirim.</p>
    @endif
    <div class="mt-8 flex flex-wrap items-center gap-3" x-data="{ wait: 0 }">
        <form method="POST" action="{{ route('verification.send') }}" @submit="wait = 60; const t = setInterval(() => { if (--wait <= 0) clearInterval(t) }, 1000)">
            @csrf
            <button type="submit" class="btn btn-secondary" :disabled="wait > 0"><span x-text="wait > 0 ? 'Kirim ulang dalam ' + wait + ' detik' : 'Kirim Ulang Email Verifikasi'">Kirim Ulang Email Verifikasi</span></button>
        </form>
        <form method="POST" action="{{ route('logout') }}">@csrf<x-button variant="ghost">Keluar</x-button></form>
    </div>
</x-layouts.auth>
