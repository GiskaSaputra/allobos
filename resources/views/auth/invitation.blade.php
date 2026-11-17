<x-layouts.auth title="Undangan">
    <h1 class="text-3xl">Undangan sebagai {{ $invitation->role->label() }}</h1>
    @if (! $invitation->usable())
        <p class="mt-3 text-fg-body">Undangan ini sudah dipakai atau kedaluwarsa. Minta undangan baru ke pengelola.</p>
    @else
        <p class="mt-2 text-fg-muted">Untuk {{ $invitation->email }}.</p>
        <form method="POST" action="{{ request()->fullUrl() }}" class="mt-8 space-y-5">
            @csrf
            @if ($hasAccount)
                <p class="text-fg-body">Email ini sudah punya akun. Pastikan kamu sudah masuk dengan akun itu, lalu terima undangan.</p>
            @else
                <x-field label="Nama lengkap" name="name" required><x-text-input name="name" required /></x-field>
                <x-field label="Nomor WhatsApp" name="whatsapp" required><x-text-input name="whatsapp" type="tel" required /></x-field>
                <x-field label="Kata sandi" name="password" required><x-text-input name="password" type="password" required autocomplete="new-password" /></x-field>
                <x-field label="Ulangi kata sandi" name="password_confirmation" required><x-text-input name="password_confirmation" type="password" required autocomplete="new-password" /></x-field>
            @endif
            <x-button variant="primary" full>Terima Undangan</x-button>
        </form>
    @endif
</x-layouts.auth>
