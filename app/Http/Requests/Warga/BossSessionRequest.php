<?php

namespace App\Http\Requests\Warga;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BossSessionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'topic' => ['required_without:custom_topic', 'nullable', 'string', 'max:160'],
            'custom_topic' => ['nullable', 'string', 'max:160'],
            'mode' => ['required', Rule::in(['suara', 'teks'])],
        ];
    }

    public function messages(): array
    {
        return ['topic.required_without' => 'Pilih topik atau tulis topikmu sendiri.'];
    }

    public function topic(): string
    {
        return trim((string) ($this->input('custom_topic') ?: $this->input('topic')));
    }
}
