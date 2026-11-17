@props(['name', 'value' => null, 'type' => 'text', 'id' => null])
@php($fieldId = $id ?? 'f-'.str_replace(['[', ']', '.'], '-', $name))
<input id="{{ $fieldId }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : old(str_replace(['[', ']'], ['.', ''], $name), $value) }}"
       {{ $attributes->class(['field-input', 'border-danger' => $errors->has(str_replace(['[', ']'], ['.', ''], $name))]) }}
       @error(str_replace(['[', ']'], ['.', ''], $name)) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @enderror>
