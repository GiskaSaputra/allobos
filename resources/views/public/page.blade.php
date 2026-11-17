<x-layouts.public :title="$page->title">
    <section class="container-page pt-6">
        <article class="prose-body mx-auto max-w-3xl">
            <h1 class="text-4xl">{{ $page->title }}</h1>
            {!! Str::markdown($page->body, ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
        </article>
    </section>
</x-layouts.public>
