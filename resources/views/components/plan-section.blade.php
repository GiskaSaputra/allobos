@props(['key', 'label', 'value', 'hint' => null])
<section class="card" x-data="{ edit: {{ $errors->any() && old('section') === $key ? 'true' : 'false' }} }">
    <div class="flex items-center justify-between gap-2">
        <h2 class="text-xl">{{ $label }}</h2>
        <button type="button" class="btn btn-ghost btn-sm" @click="edit = !edit" x-text="edit ? 'Batal' : 'Ubah'" :aria-expanded="edit" aria-label="Ubah bagian {{ $label }}">Ubah</button>
    </div>
    <div class="mt-3" x-show="!edit">{{ $slot }}</div>
    <form x-show="edit" x-cloak method="POST" action="{{ route('warga.plan.update') }}" class="mt-3 space-y-3">
        @csrf @method('PATCH')
        <input type="hidden" name="section" value="{{ $key }}">
        <label for="bagian-{{ $key }}" class="sr-only">{{ $label }}</label>
        <textarea id="bagian-{{ $key }}" name="value" rows="5" maxlength="2000" class="field-input">{{ old('section') === $key ? old('value') : $value }}</textarea>
        @if ($hint)<p class="text-sm text-fg-muted">{{ $hint }}</p>@endif
        @if (old('section') === $key)@error('value')<p class="text-sm text-danger">{{ $message }}</p>@enderror @endif
        <x-button variant="primary" size="sm" loading="Menyimpan">Simpan Perubahan</x-button>
    </form>
</section>
