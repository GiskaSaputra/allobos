<x-layouts.auth title="Masuk">
    <h1 class="text-3xl">Masuk</h1>
    <p class="mt-2 text-fg-muted">Belum punya akun? <a href="{{ route('register') }}" class="font-medium text-accent-text underline underline-offset-2">Daftar dulu</a>.</p>
    @if (session('status'))<p class="mt-6 rounded-[20px] bg-success/15 p-4 text-sm text-success">{{ session('status') }}</p>@endif
    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf
        <x-field label="Email" name="email"><x-text-input name="email" type="email" required autofocus autocomplete="username" /></x-field>
        <x-field label="Kata sandi" name="password"><x-text-input name="password" type="password" required autocomplete="current-password" /></x-field>
        <div class="flex items-center justify-between gap-4">
            <label class="flex items-center gap-2 text-sm text-fg-body"><input type="checkbox" name="remember" class="field-check">Ingat saya</label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-accent-text underline underline-offset-2">Lupa sandi</a>
        </div>
        <x-button variant="primary" full loading="Memeriksa">Masuk</x-button>
    </form>
</x-layouts.auth>
