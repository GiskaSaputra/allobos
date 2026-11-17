<x-layouts.dashboard title="Audit">
    <x-page-header title="Log audit" description="Setiap perubahan penting tercatat beserta akun dan alamat IP." />
    <nav class="chip-row mb-4" aria-label="Saring aksi">
        <a href="{{ route('admin.audit') }}" @class(['chip', 'chip-active' => ! request('aksi')])>Semua</a>
        @foreach ($actions as $action)<a href="{{ route('admin.audit', ['aksi' => $action]) }}" @class(['chip', 'chip-active' => request('aksi') === $action])>{{ $action }}</a>@endforeach
    </nav>
    <x-table>
        <thead><tr><th>Waktu</th><th>Akun</th><th>Aksi</th><th>Objek</th><th>IP</th></tr></thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td data-label="Waktu" class="whitespace-nowrap">{{ $log->created_at->translatedFormat('d M Y H:i') }}</td>
                    <td data-label="Akun">{{ $log->user?->name ?? 'Sistem' }}</td>
                    <td data-label="Aksi"><code class="text-xs">{{ $log->action }}</code></td>
                    <td data-label="Objek" class="text-xs">{{ $log->subject }}@if ($log->meta)<span class="block text-fg-muted">{{ Str::limit(json_encode($log->meta, JSON_UNESCAPED_UNICODE), 80) }}</span>@endif</td>
                    <td data-label="IP" class="font-mono text-xs">{{ $log->ip }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada catatan.</td></tr>
            @endforelse
        </tbody>
    </x-table>
    <div class="mt-6">{{ $logs->links() }}</div>
</x-layouts.dashboard>
