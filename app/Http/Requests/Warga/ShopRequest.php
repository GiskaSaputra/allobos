<?php

namespace App\Http\Requests\Warga;

use Illuminate\Foundation\Http\FormRequest;

class ShopRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:80'],
            'description' => ['required', 'string', 'min:10', 'max:600'],
            'district_id' => ['required', 'exists:districts,id'],
            'business_field_id' => ['nullable', 'exists:business_fields,id'],
            'whatsapp' => ['required', 'regex:/^(\+?62|0)8[0-9]{7,12}$/'],
            'cover' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
        ];
    }

    public function messages(): array
    {
        return ['whatsapp.regex' => 'Nomor WhatsApp harus diawali 08 atau 62, contoh 081234567890.'];
    }
}
