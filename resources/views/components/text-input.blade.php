@props(['name', 'value' => null, 'type' => 'text', 'id' => null, 'icon' => null])
@php
    $fieldId = $id ?? 'f-'.str_replace(['[', ']', '.'], '-', $name);
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $secret = $type === 'password';
@endphp
@if ($icon || $secret)
    <div class="relative" @if ($secret) x-data="{ show: false }" @endif>
        @if ($icon)<x-dynamic-component :component="$icon" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-fg-muted" aria-hidden="true" />@endif
        <input id="{{ $fieldId }}" name="{{ $name }}" type="{{ $type }}" value="{{ $secret ? '' : old($key, $value) }}"
               @if ($secret) x-bind:type="show ? 'text' : 'password'" @endif
               {{ $attributes->class(['field-input', 'pl-12' => $icon, 'pr-12' => $secret, 'border-danger' => $errors->has($key)]) }}
               @error($key) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @enderror>
        @if ($secret)
            <button type="button" class="absolute right-2 top-1/2 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-full text-fg-muted transition hover:bg-surface-alt hover:text-fg" x-on:click="show = !show" x-bind:aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'" aria-label="Tampilkan kata sandi" aria-controls="{{ $fieldId }}">
                <x-phosphor-eye class="h-5 w-5" x-show="!show" aria-hidden="true" />
                <x-phosphor-eye-slash class="h-5 w-5" x-show="show" x-cloak aria-hidden="true" />
            </button>
        @endif
    </div>
@else
    <input id="{{ $fieldId }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($key, $value) }}"
           {{ $attributes->class(['field-input', 'border-danger' => $errors->has($key)]) }}
           @error($key) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @enderror>
@endif
