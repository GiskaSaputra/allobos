@props(['title' => null])
@php($menu = collect(config('menus.'.auth()->user()->active_role->value, []))->filter(fn ($item) => Route::has($item[1])))
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['description' => null])
    @livewireStyles
    @stack('head')
</head>
<body class="bg-surface" x-data="{ drawer: false }" @keydown.escape.window="drawer = false">
    @include('partials.splash')
    <a href="#isi" class="sr-only z-flash rounded-full bg-brand px-4 py-2 text-brand-fg focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Lewati ke isi</a>
    <div x-cloak x-show="drawer" class="fixed inset-0 z-drawer bg-ink/50 lg:hidden" @click="drawer = false"></div>
    <aside :class="drawer ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-drawer flex w-[272px] -translate-x-full flex-col border-r border-line bg-surface-raised transition-transform duration-300 ease-out lg:translate-x-0" aria-label="Menu {{ auth()->user()->active_role->label() }}">
        <div class="flex h-16 items-center justify-between px-5">
            <a href="{{ route('dashboard') }}" aria-label="Ringkasan"><x-logo /></a>
            <button type="button" class="btn btn-ghost btn-icon btn-sm lg:hidden" @click="drawer = false" aria-label="Tutup menu"><x-phosphor-x class="h-5 w-5" aria-hidden="true" /></button>
        </div>
        <p class="label-caps px-5 pb-2">{{ auth()->user()->active_role->label() }}</p>
        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 pb-6">
            @foreach ($menu as [$label, $route, $icon])
                @php($active = App\Support\Nav::active($route))
                <a href="{{ route($route) }}" @class(['flex min-h-[44px] items-center gap-3 rounded-full px-4 text-sm font-medium transition', 'bg-brand text-brand-fg' => $active, 'text-fg-body hover:bg-fg/10' => ! $active]) @if ($active) aria-current="page" @endif>
                    <x-dynamic-component :component="$icon" class="h-5 w-5 shrink-0" aria-hidden="true" />{{ $label }}
                </a>
            @endforeach
        </nav>
    </aside>

    <div class="lg:pl-[272px]">
        <header class="sticky top-0 z-nav flex h-16 items-center justify-between gap-3 border-b border-line bg-surface/90 px-4 backdrop-blur-md sm:px-6">
            <button type="button" class="btn btn-ghost btn-icon lg:hidden" @click="drawer = true" aria-label="Buka menu"><x-phosphor-list class="h-6 w-6" aria-hidden="true" /></button>
            <p class="hidden truncate text-sm text-fg-muted sm:block">{{ $title }}</p>
            <div class="ml-auto flex items-center gap-1">
                <a href="{{ route('home') }}" class="btn btn-ghost btn-sm px-3" aria-label="Ke beranda alloBos"><x-phosphor-house class="h-5 w-5" aria-hidden="true" /><span class="hidden sm:inline">Ke Beranda</span></a>
                <x-notification-bell />
                <x-account-menu />
            </div>
        </header>
        <x-flash />
        <main id="isi" class="mx-auto w-full max-w-[1440px] px-4 py-6 sm:px-6 sm:py-8 lg:px-10">{{ $slot }}</main>
    </div>

    @include('partials.confirm-dialog')
    @livewireScriptConfig
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
