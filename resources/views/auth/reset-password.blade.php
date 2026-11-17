<x-layouts.auth title="Atur ulang sandi">
    <h1 class="text-[28px] leading-tight sm:text-3xl">Buat kata sandi baru</h1>
    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-field label="Email" name="email"><x-text-input name="email" type="email" icon="phosphor-envelope-simple" :value="$request->email" required autocomplete="username" /></x-field>
        <x-field label="Kata sandi baru" name="password" hint="Minimal 8 karakter."><x-text-input name="password" type="password" icon="phosphor-lock-simple" required autofocus autocomplete="new-password" /></x-field>
        <x-field label="Ulangi kata sandi baru" name="password_confirmation"><x-text-input name="password_confirmation" type="password" icon="phosphor-lock-simple" required autocomplete="new-password" /></x-field>
        <x-button variant="primary" size="lg" full loading="Menyimpan">Simpan Sandi Baru</x-button>
    </form>
</x-layouts.auth>
