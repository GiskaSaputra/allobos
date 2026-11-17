<?php

namespace App\Http\Requests\Warga;

use Illuminate\Foundation\Http\FormRequest;

class PhotoRequest extends FormRequest
{
    public function rules(): array
    {
        return ['photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:15360']];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Pilih atau ambil foto produk dulu.',
            'photo.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto paling besar 15 MB. Kecilkan foto atau ambil ulang dengan resolusi lebih rendah.',
        ];
    }
}
