<x-layouts.shop title="Verifikasi Sertifikat" description="Periksa keaslian Sertifikat Terverifikasi alloBos.">
    <div class="mx-auto max-w-xl space-y-6 py-6">
        @if (auth()->user()?->active_role === App\Enums\RoleName::Warga)
            <x-back-link :href="route('warga.certificates.index')">Kembali ke Sertifikat Saya</x-back-link>
        @elseif (auth()->check())
            <x-back-link :href="route('dashboard')">Kembali ke Dashboard</x-back-link>
        @else
            <x-back-link :href="route('home')">Kembali ke Beranda</x-back-link>
        @endif
        <h1 class="text-4xl">Verifikasi sertifikat</h1>
        <p class="text-fg-body">Ketik kode yang tertulis di bawah kode QR sertifikat. Pemeriksaan membaca catatan blockchain secara langsung, tanpa perlu menghubungi penyelenggara.</p>
        <form method="POST" action="{{ route('certificates.verify.lookup') }}" class="card space-y-4">
            @csrf
            <div class="space-y-2">
                <label for="kode" class="block text-sm font-medium text-fg">Kode sertifikat</label>
                <input id="kode" name="code" value="{{ old('code') }}" class="field-input font-mono uppercase tracking-wider" placeholder="ALB-2026-7KQ4MX" required maxlength="30" autocapitalize="characters">
                @error('code')<p class="text-sm text-danger">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn btn-primary btn-full">Periksa Sertifikat</button>
        </form>
    </div>
</x-layouts.shop>
