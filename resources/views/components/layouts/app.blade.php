@props(['title' => null, 'wide' => false])
@php($menu = config('menus.warga'))
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['description' => null])
    @livewireStyles
    @stack('head')
</head>
<body class="bg-surface pb-20 lg:pb-0">
    <a href="#isi" class="sr-only z-flash rounded-full bg-brand px-4 py-2 text-brand-fg focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Lewati ke isi</a>
    <header class="sticky top-0 z-nav border-b border-line bg-surface/90 backdrop-blur-md">
        <div class="container-page flex h-16 items-center justify-between gap-3">
            <a href="{{ route('warga.dashboard') }}" aria-label="Beranda warga"><x-logo /></a>
            <nav aria-label="Modul warga" class="hidden items-center gap-1 lg:flex">
                @foreach ($menu as [$label, $route, $icon, $children])
                    @if ($children)
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
                            <button type="button" @click="open = !open" :aria-expanded="open" @class(['flex items-center gap-1 rounded-full px-4 py-2 text-sm font-medium transition', 'bg-brand text-brand-fg' => collect($children)->contains(fn ($c) => request()->routeIs(Str::beforeLast($c[1], '.').'.*')), 'text-fg-body hover:bg-fg/10' => ! collect($children)->contains(fn ($c) => request()->routeIs(Str::beforeLast($c[1], '.').'.*'))])>
                                {{ $label }} <x-phosphor-caret-down class="h-4 w-4" aria-hidden="true" />
                            </button>
                            <div x-cloak x-show="open" x-transition.origin.top class="absolute left-0 top-full z-drawer mt-2 w-56 rounded-[20px] border border-line bg-surface-raised p-2 shadow-float">
                                @foreach ($children as [$childLabel, $childRoute])
                                    @if (Route::has($childRoute))
                                        <a href="{{ route($childRoute) }}" class="block rounded-2xl px-4 py-2.5 text-sm text-fg hover:bg-fg/10">{{ $childLabel }}</a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @elseif (Route::has($route))
                        <a href="{{ route($route) }}" @class(['rounded-full px-4 py-2 text-sm font-medium transition', 'bg-brand text-brand-fg' => request()->routeIs(Str::beforeLast($route, '.').'.*') && $route !== 'warga.dashboard' || request()->routeIs($route), 'text-fg-body hover:bg-fg/10' => ! (request()->routeIs(Str::beforeLast($route, '.').'.*') && $route !== 'warga.dashboard' || request()->routeIs($route))])>{{ $label }}</a>
                    @endif
                @endforeach
            </nav>
            <div class="flex items-center gap-1">
                <a href="{{ route('home') }}" class="btn btn-ghost btn-sm px-3" aria-label="Ke beranda alloBos"><x-phosphor-house class="h-5 w-5" aria-hidden="true" /><span class="hidden sm:inline">Ke Beranda</span></a>
                <x-notification-bell />
                <x-account-menu />
            </div>
        </div>
    </header>

    <x-flash />

    <main id="isi" @class(['container-page py-6 sm:py-10', 'max-w-[1200px]' => $wide])>
        {{ $slot }}
    </main>

    <nav aria-label="Navigasi bawah" class="pb-safe fixed inset-x-0 bottom-0 z-nav border-t border-line bg-surface-raised/95 backdrop-blur-md lg:hidden">
        <ul class="grid grid-cols-5">
            @foreach ($menu as [$label, $route, $icon, $children])
                @php($active = request()->routeIs($route) || collect($children)->contains(fn ($c) => request()->routeIs($c[1])))
                <li>
                    <a href="{{ Route::has($route) ? route($route) : '#' }}" @class(['flex min-h-[60px] flex-col items-center justify-center gap-0.5 text-[11px] font-medium', 'text-fg' => $active, 'text-fg-muted' => ! $active]) @if ($active) aria-current="page" @endif>
                        <span @class(['flex h-7 w-12 items-center justify-center rounded-full', 'bg-accent text-accent-fg' => $active])><x-dynamic-component :component="$icon" class="h-5 w-5" aria-hidden="true" /></span>
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    @include('partials.confirm-dialog')
    @livewireScriptConfig
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
