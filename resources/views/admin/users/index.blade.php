<x-layouts.dashboard title="Pengguna">
    <x-page-header title="Pengguna" description="Peran, status akun, dan undangan.">
        <x-slot:actions><x-button variant="primary" :href="route('admin.users.create')" icon="phosphor-envelope-simple">Undang Bos atau Pengelola</x-button></x-slot:actions>
    </x-page-header>
    <form method="GET" class="mb-4 grid gap-3 sm:grid-cols-[1fr_200px_auto] sm:items-end">
        <x-field label="Cari" name="q"><x-text-input name="q" :value="request('q')" placeholder="Nama atau email" /></x-field>
        <x-field label="Peran" name="peran"><x-select name="peran" :options="collect(App\Enums\RoleName::cases())->mapWithKeys(fn ($r) => [$r->value => $r->label()])" :value="request('peran')" placeholder="Semua peran" /></x-field>
        <x-button variant="secondary">Saring</x-button>
    </form>
    <x-table>
        <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td data-label="Nama" class="font-medium text-fg">{{ $user->name }} <x-demo-badge :show="$user->is_demo" /></td>
                    <td data-label="Email">{{ $user->email }}</td>
                    <td data-label="Peran">{{ $user->roles->map(fn ($r) => App\Enums\RoleName::from($r->name)->label())->implode(', ') }}</td>
                    <td data-label="Status">@if ($user->is_active)<x-badge tone="success">Aktif</x-badge>@else<x-badge tone="danger">Nonaktif</x-badge>@endif</td>
                    <td data-label="Aksi"><div class="flex justify-end gap-2">
                        <x-button variant="secondary" size="sm" :href="route('admin.users.edit', $user)">Ubah</x-button>
                        <form method="POST" action="{{ route('admin.users.reset', $user) }}">@csrf<x-button variant="secondary" size="sm">Kirim Reset Sandi</x-button></form>
                    </div></td>
                </tr>
            @endforeach
        </tbody>
    </x-table>
    <div class="mt-6">{{ $users->links() }}</div>
</x-layouts.dashboard>
