@php($user = auth()->user())
@php($unread = $user->unreadNotifications()->count())
@php($latest = $user->notifications()->limit(6)->get())
<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
    <button type="button" class="btn btn-ghost btn-icon relative" @click="open = !open" :aria-expanded="open" aria-label="Notifikasi, {{ $unread }} belum dibaca">
        <x-phosphor-bell class="h-6 w-6" aria-hidden="true" />
        @if ($unread)
            <span class="absolute right-1 top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-danger px-1 text-[11px] font-bold text-status-fg">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </button>
    <div x-cloak x-show="open" x-transition.origin.top.right class="fixed inset-x-3 top-16 z-drawer rounded-card border border-line bg-surface-raised p-3 shadow-float sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-96">
        <div class="flex items-center justify-between px-2 pb-2">
            <p class="font-display font-semibold text-fg">Notifikasi</p>
            @if ($unread)
                <form method="POST" action="{{ route('notifications.read') }}">@csrf<x-button variant="ghost" size="sm">Tandai Semua Dibaca</x-button></form>
            @endif
        </div>
        <ul class="max-h-[60vh] space-y-1 overflow-y-auto">
            @forelse ($latest as $note)
                <li>
                    <a href="{{ $note->data['url'] ?? route('notifications.index') }}" @class(['block rounded-2xl px-3 py-2.5 hover:bg-fg/10', 'bg-accent/15' => ! $note->read_at])>
                        <p class="text-sm font-medium text-fg">{{ $note->data['title'] ?? 'Pemberitahuan' }}</p>
                        <p class="line-clamp-2 text-sm text-fg-muted">{{ $note->data['body'] ?? '' }}</p>
                        <p class="mt-1 text-xs text-fg-muted">{{ $note->created_at->diffForHumans() }}</p>
                    </a>
                </li>
            @empty
                <li class="px-3 py-6 text-sm text-fg-muted">Belum ada notifikasi.</li>
            @endforelse
        </ul>
        <a href="{{ route('notifications.index') }}" class="mt-2 block rounded-full px-3 py-2 text-center text-sm font-medium text-fg hover:bg-fg/10">Lihat semua</a>
    </div>
</div>
