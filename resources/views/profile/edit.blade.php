<x-dynamic-component :component="$user->active_role === App\Enums\RoleName::Warga ? 'layouts.app' : 'layouts.dashboard'" title="Profil">
    <x-page-header title="Profil akun" description="Data masuk dan kontak kamu." />
    <div class="grid max-w-2xl gap-6">
        <x-card>
            <h2 class="text-xl">Data akun</h2>
            <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
                @csrf @method('PATCH')
                <x-field label="Nama lengkap" name="name"><x-text-input name="name" :value="$user->name" required /></x-field>
                <x-field label="Nomor WhatsApp" name="whatsapp"><x-text-input name="whatsapp" type="tel" :value="$user->whatsapp" /></x-field>
                <x-field label="Email" name="email"><x-text-input name="email" type="email" :value="$user->email" required /></x-field>
                <x-button variant="primary" loading="Menyimpan">Simpan Data Akun</x-button>
            </form>
        </x-card>
        <x-card>
            <h2 class="text-xl">Ganti kata sandi</h2>
            <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-5">
                @csrf @method('PUT')
                <x-field label="Kata sandi sekarang" name="current_password"><x-text-input name="current_password" type="password" autocomplete="current-password" /></x-field>
                @foreach ($errors->updatePassword->all() as $message)<p class="text-sm text-danger">{{ $message }}</p>@endforeach
                <x-field label="Kata sandi baru" name="password"><x-text-input name="password" type="password" autocomplete="new-password" /></x-field>
                <x-field label="Ulangi kata sandi baru" name="password_confirmation"><x-text-input name="password_confirmation" type="password" autocomplete="new-password" /></x-field>
                <x-button variant="secondary" loading="Menyimpan">Ganti Sandi</x-button>
            </form>
        </x-card>
        <x-card>
            <h2 class="text-xl">Hapus akun</h2>
            <p class="mt-2 text-fg-muted">Semua data kamu, termasuk profil usaha, catatan, dan lapak, dihapus permanen.</p>
            <form method="POST" action="{{ route('profile.destroy') }}" class="mt-5 space-y-4" data-confirm="Akun dan semua datamu akan dihapus permanen dan tidak bisa dikembalikan.">
                @csrf @method('DELETE')
                <x-field label="Kata sandi" name="password"><x-text-input name="password" type="password" autocomplete="current-password" /></x-field>
                @foreach ($errors->userDeletion->all() as $message)<p class="text-sm text-danger">{{ $message }}</p>@endforeach
                <x-button variant="danger">Hapus Akun</x-button>
            </form>
        </x-card>
    </div>
</x-dynamic-component>
