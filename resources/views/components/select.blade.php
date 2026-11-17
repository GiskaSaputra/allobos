@props(['name', 'options' => [], 'value' => null, 'placeholder' => null, 'id' => null])
@php($fieldId = $id ?? 'f-'.str_replace(['[', ']', '.'], '-', $name))
@php($current = (string) old($name, $value instanceof \BackedEnum ? $value->value : $value))
<select id="{{ $fieldId }}" name="{{ $name }}" {{ $attributes->class(['field-input', 'border-danger' => $errors->has($name)]) }} @error($name) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @enderror>
    @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
    @foreach ($options as $key => $label)
        <option value="{{ $key }}" @selected($current === (string) $key)>{{ $label }}</option>
    @endforeach
</select>
