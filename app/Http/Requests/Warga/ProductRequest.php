<?php

namespace App\Http\Requests\Warga;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'price' => ['required', 'integer', 'min:100', 'max:100000000'],
            'unit' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:600'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:999'],
            'hidden' => ['boolean'],
            'product_check_id' => ['nullable', 'integer'],
        ];
    }
}
