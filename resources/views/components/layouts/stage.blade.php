@props(['title' => null, 'exit' => null, 'confirmExit' => null])
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    @include('partials.head', ['description' => null])
    @stack('head')
</head>
<body class="h-full overflow-hidden bg-surface">
    <div class="flex h-[100dvh] flex-col">
        <header class="flex h-14 shrink-0 items-center justify-between gap-3 border-b border-line px-3 sm:px-5">
            <div class="flex min-w-0 items-center gap-3">
                <x-logo />
                <span class="hidden truncate text-sm text-fg-muted sm:block">{{ $title }}</span>
            </div>
            <div class="flex items-center gap-1">
                {{ $actions ?? '' }}
                <x-theme-toggle />
                <a href="{{ $exit ?? route('dashboard') }}" class="btn btn-ghost btn-sm" @if ($confirmExit) data-exit-confirm="{{ $confirmExit }}" @endif>
                    <x-phosphor-sign-out class="h-5 w-5" aria-hidden="true" />Keluar
                </a>
            </div>
        </header>
        <main id="isi" class="min-h-0 flex-1">{{ $slot }}</main>
    </div>
    @include('partials.confirm-dialog')
    @livewireScriptConfig
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
