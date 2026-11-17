@props(['label', 'name', 'hint' => null, 'id' => null, 'required' => false])
@php($fieldId = $id ?? 'f-'.str_replace(['[', ']', '.'], '-', $name))
<div {{ $attributes->class('space-y-2') }}>
    <label for="{{ $fieldId }}" class="block text-sm font-medium text-fg">{{ $label }}@if ($required)<span class="text-danger" aria-hidden="true"> *</span>@endif</label>
    {{ $slot }}
    @if ($hint)<p class="text-sm text-fg-muted" id="{{ $fieldId }}-hint">{{ $hint }}</p>@endif
    @error(str_replace(['[', ']'], ['.', ''], $name))<p class="text-sm text-danger" id="{{ $fieldId }}-error">{{ $message }}</p>@enderror
</div>
