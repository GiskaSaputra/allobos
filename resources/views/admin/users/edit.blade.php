<x-layouts.dashboard :title="$user->name">
    <x-page-header :title="$user->name" :description="$user->email" />
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="card max-w-xl space-y-5">
        @csrf @method('PUT')
        <fieldset class="space-y-2">
            <legend class="text-sm font-medium text-fg">Peran</legend>
            @foreach (App\Enums\RoleName::cases() as $role)
                <label class="flex items-center gap-2 text-sm text-fg-body"><input type="checkbox" name="roles[]" value="{{ $role->value }}" class="field-check" @checked($user->hasRole($role->value))>{{ $role->label() }}</label>
            @endforeach
            @error('roles')<p class="text-sm text-danger">{{ $message }}</p>@enderror
        </fieldset>
        <label class="flex items-center gap-2 text-sm text-fg-body"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="field-check" @checked($user->is_active)>Akun aktif</label>
        <x-button variant="primary">Simpan</x-button>
    </form>
</x-layouts.dashboard>
