<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['title' => $heading ?? 'Ada masalah', 'description' => null])
</head>
<body class="bg-surface">
    <main class="container-page flex min-h-[100dvh] items-center py-10">
        <div class="bg-hero w-full rounded-[28px] p-6 sm:rounded-panel sm:p-14">
            <div class="max-w-xl rounded-[24px] bg-ink/55 p-6 text-ink-fg backdrop-blur-md sm:p-10">
                <p class="font-display text-6xl font-bold text-accent">@yield('code')</p>
                <h1 class="mt-4 text-3xl text-ink-fg">@yield('heading')</h1>
                <p class="mt-3 text-ink-muted">@yield('message')</p>
                <div class="mt-8">@yield('action')</div>
            </div>
        </div>
    </main>
</body>
</html>
