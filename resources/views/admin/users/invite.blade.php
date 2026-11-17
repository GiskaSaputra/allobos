<x-layouts.dashboard title="Undang Pengguna">
    <x-page-header title="Undang Bos atau Pengelola" description="Tautan undangan bertanda tangan dikirim lewat email dan berlaku 7 hari." />
    <form method="POST" action="{{ route('admin.users.store') }}" class="card max-w-xl space-y-5">
        @csrf
        <x-field label="Email" name="email" required><x-text-input name="email" type="email" required /></x-field>
        <x-field label="Peran" name="role"><x-select name="role" :options="['bos' => 'Bos', 'pengelola' => 'Pengelola IPI']" value="bos" /></x-field>
        <x-button variant="primary">Kirim Undangan</x-button>
    </form>
</x-layouts.dashboard>
