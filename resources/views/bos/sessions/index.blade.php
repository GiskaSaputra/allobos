<x-layouts.dashboard title="Permintaan Sesi">
    <x-page-header title="Permintaan Sesi" />
    <nav class="chip-row mb-4" aria-label="Saring status">
        <a href="{{ route('bos.sessions.index') }}" @class(['chip', 'chip-active' => ! $status])>Semua</a>
        @foreach (App\Enums\MentoringStatus::cases() as $case)<a href="{{ route('bos.sessions.index', ['status' => $case->value]) }}" @class(['chip', 'chip-active' => $status === $case->value])>{{ $case->label() }}</a>@endforeach
    </nav>
    @if ($sessions->isEmpty())
        <x-empty-state icon="phosphor-chats" title="Belum ada sesi" />
    @else
        <x-table>
            <thead><tr><th>Warga</th><th>Waktu</th><th>Topik</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($sessions as $s)
                    <tr>
                        <td data-label="Warga"><a href="{{ route('bos.sessions.show', $s) }}" class="font-medium text-fg hover:underline">{{ $s->user->name }}</a></td>
                        <td data-label="Waktu">{{ $s->slot->starts_at->translatedFormat('d M Y, H:i') }}</td>
                        <td data-label="Topik">{{ Str::limit($s->topic, 60) }}</td>
                        <td data-label="Status"><x-status-badge :status="$s->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
        <div class="mt-6">{{ $sessions->links() }}</div>
    @endif
</x-layouts.dashboard>
