@props(['show' => true])
@if ($show)
    <span {{ $attributes->class('badge badge-warning') }} title="Data contoh untuk demonstrasi, bukan data nyata">Demo</span>
@endif
