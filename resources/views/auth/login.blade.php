<x-layouts.auth title="Masuk">
    <h1 class="text-[28px] leading-tight sm:text-3xl">Masuk ke akun Anda</h1>
    <p class="mt-2 text-fg-muted">Lanjutkan belajar, bertanya ke Bos Allo, dan kelola lapakmu.</p>
    @if (session('status'))<p class="mt-6 rounded-[20px] bg-success/15 p-4 text-sm text-success">{{ session('status') }}</p>@endif
    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf
        <x-field label="Email" name="email"><x-text-input name="email" type="email" icon="phosphor-envelope-simple" placeholder="nama@email.com" required autofocus autocomplete="username" /></x-field>
        <x-field label="Kata sandi" name="password"><x-text-input name="password" type="password" icon="phosphor-lock-simple" placeholder="Kata sandi kamu" required autocomplete="current-password" /></x-field>
        <div class="flex items-center justify-between gap-4">
            <label class="flex items-center gap-2 text-sm text-fg-body"><input type="checkbox" name="remember" class="field-check">Ingat saya</label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-accent-text underline-offset-2 hover:underline">Lupa kata sandi?</a>
        </div>
        <x-button variant="primary" size="lg" full loading="Memeriksa" icon-right="phosphor-arrow-right">Masuk Sekarang</x-button>
    </form>
    <p class="mt-8 border-t border-line pt-6 text-center text-sm text-fg-muted">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-accent-text underline-offset-2 hover:underline">Daftar gratis</a></p>
</x-layouts.auth>
