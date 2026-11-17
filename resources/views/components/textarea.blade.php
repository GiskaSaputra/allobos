@props(['name', 'value' => null, 'rows' => 4, 'id' => null])
@php($fieldId = $id ?? 'f-'.str_replace(['[', ']', '.'], '-', $name))
<textarea id="{{ $fieldId }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->class(['field-input', 'border-danger' => $errors->has($name)]) }} @error($name) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @enderror>{{ old($name, $value) }}</textarea>
