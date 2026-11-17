@props(['show' => true])
@if ($show && config('features.demo'))
    <span {{ $attributes->class('badge badge-warning') }} title="Data contoh untuk demonstrasi, bukan data nyata">Demo</span>
@endif
