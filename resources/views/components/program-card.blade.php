@props(['program'])
<a href="{{ route('programs.show', $program) }}" class="card card-link group relative isolate flex flex-col gap-3 overflow-hidden">
    <x-logo-mark class="-bottom-6 -right-6 h-28 w-28" />
    <span class="flex flex-wrap items-center gap-2">
        <span class="badge badge-brand">{{ $program->kind->label() }}</span>
        @if ($program->is_official)<x-official-badge />@endif
        <x-demo-badge :show="$program->is_demo" />
        <x-status-badge :status="$program->status" />
    </span>
    <span class="font-display text-xl font-semibold leading-snug text-fg">{{ $program->title }}</span>
    <span class="grid gap-1 text-sm text-fg-body">
        @if ($program->startsAt())
            <span class="flex items-center gap-2"><x-phosphor-calendar-blank class="h-4 w-4 shrink-0 text-fg-muted" aria-hidden="true" />{{ $program->startsAt()->translatedFormat('d F Y, H:i') }} WIB</span>
        @endif
        <span class="flex items-center gap-2"><x-phosphor-map-pin class="h-4 w-4 shrink-0 text-fg-muted" aria-hidden="true" />{{ $program->location }}</span>
        @if ($program->field)<span class="flex items-center gap-2"><x-phosphor-tag class="h-4 w-4 shrink-0 text-fg-muted" aria-hidden="true" />{{ $program->field->name }}</span>@endif
    </span>
</a>
