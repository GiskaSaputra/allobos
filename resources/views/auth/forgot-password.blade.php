<x-layouts.auth title="Lupa sandi">
    <h1 class="text-3xl">Lupa sandi</h1>
    <p class="mt-2 text-fg-muted">Tulis email akunmu. Kami kirim tautan untuk membuat kata sandi baru.</p>
    @if (session('status'))<p class="mt-6 rounded-[20px] bg-success/15 p-4 text-sm text-success">{{ session('status') }}</p>@endif
    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf
        <x-field label="Email" name="email"><x-text-input name="email" type="email" required autofocus /></x-field>
        <x-button variant="primary" full loading="Mengirim">Kirim Tautan Atur Ulang</x-button>
    </form>
    <a href="{{ route('login') }}" class="mt-6 inline-block text-sm font-medium text-accent-text underline underline-offset-2">Kembali ke Masuk</a>
</x-layouts.auth>
