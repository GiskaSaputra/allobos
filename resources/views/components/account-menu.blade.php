@php($user = auth()->user())
<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
    <button type="button" class="flex min-h-[44px] items-center gap-2 rounded-full py-1 pl-1 pr-2 hover:bg-fg/10" @click="open = !open" :aria-expanded="open" aria-label="Menu akun {{ $user->name }}">
        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand font-display text-sm font-bold text-brand-fg">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
        <x-phosphor-caret-down class="hidden h-4 w-4 sm:block" aria-hidden="true" />
    </button>
    <div x-cloak x-show="open" x-transition.origin.top.right class="absolute right-0 top-full z-drawer mt-2 w-64 rounded-card border border-line bg-surface-raised p-2 shadow-float">
        <div class="px-3 py-2">
            <p class="truncate font-medium text-fg">{{ $user->name }}</p>
            <p class="truncate text-sm text-fg-muted">{{ $user->active_role->label() }}</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm text-fg hover:bg-fg/10"><x-phosphor-user class="h-5 w-5" aria-hidden="true" />Profil</a>
        @if (count($user->roleOptions()) > 1)
            <p class="label-caps px-3 pb-1 pt-3">Ganti Peran</p>
            @foreach ($user->roleOptions() as $role)
                <form method="POST" action="{{ route('active-role.update') }}">
                    @csrf
                    <input type="hidden" name="role" value="{{ $role->value }}">
                    <button type="submit" class="flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-sm text-fg hover:bg-fg/10" @disabled($role === $user->active_role)>
                        <x-phosphor-swap class="h-5 w-5" aria-hidden="true" />{{ $role->label() }}@if ($role === $user->active_role)<span class="ml-auto text-xs text-fg-muted">aktif</span>@endif
                    </button>
                </form>
            @endforeach
        @endif
        <button type="button" data-theme-toggle class="flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-sm text-fg hover:bg-fg/10"><x-phosphor-circle-half class="h-5 w-5" aria-hidden="true" />Tema</button>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-sm text-fg hover:bg-fg/10"><x-phosphor-sign-out class="h-5 w-5" aria-hidden="true" />Keluar</button>
        </form>
    </div>
</div>
