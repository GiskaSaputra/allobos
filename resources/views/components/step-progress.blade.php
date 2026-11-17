@props(['steps', 'current'])
<ol class="flex gap-1.5" aria-label="Langkah {{ $current }} dari {{ count($steps) }}">
    @foreach ($steps as $number => $label)
        <li class="flex-1">
            <span @class(['block h-1.5 rounded-full', 'bg-brand' => $number <= $current, 'bg-line' => $number > $current])></span>
            <span @class(['mt-2 hidden text-xs sm:block', 'font-medium text-fg' => $number === $current, 'text-fg-muted' => $number !== $current])>{{ $number }}. {{ $label }}</span>
            <span class="sr-only">{{ $number }}. {{ $label }}@if ($number < $current) (selesai)@elseif ($number === $current) (sekarang)@endif</span>
        </li>
    @endforeach
</ol>
