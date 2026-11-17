<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'whatsapp' => ['nullable', 'regex:/^(\+?62|0)8[0-9]{7,12}$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
        ];
    }

    public function messages(): array
    {
        return ['whatsapp.regex' => 'Nomor WhatsApp harus diawali 08 atau 62, contoh 081234567890.'];
    }
}
