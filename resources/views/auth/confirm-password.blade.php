<x-layouts.auth title="Konfirmasi sandi">
    <h1 class="text-[28px] leading-tight sm:text-3xl">Konfirmasi kata sandi</h1>
    <p class="mt-2 text-fg-muted">Tindakan ini penting. Masukkan kata sandimu sekali lagi untuk melanjutkan.</p>
    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5">
        @csrf
        <x-field label="Kata sandi" name="password"><x-text-input name="password" type="password" icon="phosphor-lock-simple" required autofocus autocomplete="current-password" /></x-field>
        <x-button variant="primary" size="lg" full loading="Memeriksa">Konfirmasi</x-button>
    </form>
</x-layouts.auth>
