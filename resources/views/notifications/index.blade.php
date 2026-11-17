<x-dynamic-component :component="auth()->user()->active_role === App\Enums\RoleName::Warga ? 'layouts.app' : 'layouts.dashboard'" title="Notifikasi">
    <x-page-header title="Notifikasi">
        <x-slot:actions>
            <form method="POST" action="{{ route('notifications.read') }}">@csrf<x-button variant="secondary">Tandai Semua Dibaca</x-button></form>
        </x-slot:actions>
    </x-page-header>
    @if ($notifications->isEmpty())
        <x-empty-state icon="phosphor-bell" title="Belum ada notifikasi">Pemberitahuan tentang sesi, kelas, sertifikat, dan lapak akan muncul di sini.</x-empty-state>
    @else
        <ul class="divide-y divide-line rounded-card border border-line bg-surface-raised">
            @foreach ($notifications as $note)
                <li>
                    <a href="{{ $note->data['url'] ?? '#' }}" class="flex gap-4 px-5 py-4 hover:bg-fg/5">
                        <span @class(['mt-2 h-2 w-2 shrink-0 rounded-full', 'bg-accent-text' => ! $note->read_at, 'bg-transparent' => $note->read_at]) aria-hidden="true"></span>
                        <span class="min-w-0">
                            <span class="block font-medium text-fg">{{ $note->data['title'] ?? 'Pemberitahuan' }}@unless ($note->read_at)<span class="sr-only"> (belum dibaca)</span>@endunless</span>
                            <span class="block text-sm text-fg-body">{{ $note->data['body'] ?? '' }}</span>
                            <span class="mt-1 block text-xs text-fg-muted">{{ $note->created_at->translatedFormat('d F Y, H:i') }}</span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
</x-dynamic-component>
