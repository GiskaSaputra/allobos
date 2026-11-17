<x-layouts.auth title="Daftar">
    <h1 class="text-3xl">Buat akun warga</h1>
    <p class="mt-2 text-fg-muted">
        @if ($purpose)Daftar dulu supaya bisa melanjutkan. @endif
        Sudah punya akun? <a href="{{ route('login') }}" class="font-medium text-accent-text underline underline-offset-2">Masuk</a>.
    </p>
    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5" x-data="{ agree: {{ old('consent') ? 'true' : 'false' }} }">
        @csrf
        <x-field label="Nama lengkap" name="name" required><x-text-input name="name" required autofocus autocomplete="name" /></x-field>
        <x-field label="Nomor WhatsApp" name="whatsapp" hint="Dipakai pembeli lapak untuk menghubungi kamu. Contoh 081234567890." required><x-text-input name="whatsapp" type="tel" inputmode="tel" required autocomplete="tel" /></x-field>
        <x-field label="Email" name="email" required><x-text-input name="email" type="email" required autocomplete="username" /></x-field>
        <x-field label="Kata sandi" name="password" hint="Minimal 8 karakter." required><x-text-input name="password" type="password" required autocomplete="new-password" /></x-field>
        <x-field label="Ulangi kata sandi" name="password_confirmation" required><x-text-input name="password_confirmation" type="password" required autocomplete="new-password" /></x-field>
        <div class="space-y-2">
            <label class="flex items-start gap-3 text-sm text-fg-body">
                <input type="checkbox" name="consent" value="1" class="field-check mt-0.5" x-model="agree">
                <span>Saya setuju data saya dipakai sesuai <a href="{{ Route::has('pages.privasi') ? route('pages.privasi') : '#' }}" class="text-accent-text underline underline-offset-2" target="_blank">kebijakan privasi</a> dan <a href="{{ Route::has('pages.syarat') ? route('pages.syarat') : '#' }}" class="text-accent-text underline underline-offset-2" target="_blank">syarat pemakaian</a>.</span>
            </label>
            @error('consent')<p class="text-sm text-danger">{{ $message }}</p>@enderror
            <p class="text-sm text-fg-muted" x-show="!agree">Centang persetujuan di atas untuk membuat akun.</p>
        </div>
        <x-button variant="primary" full loading="Membuat akun" x-bind:disabled="!agree">Buat Akun</x-button>
    </form>
</x-layouts.auth>
