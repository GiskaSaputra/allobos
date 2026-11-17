<?php

namespace App\Http\Requests\Pengelola;

use App\Enums\ProgramKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'kind' => ['required', Rule::enum(ProgramKind::class)],
            'business_field_id' => ['nullable', 'exists:business_fields,id'],
            'description' => ['required', 'string', 'max:3000'],
            'location' => ['required', 'string', 'max:200'],
            'quota' => ['required', 'integer', 'min:1', 'max:5000'],
            'is_official' => ['boolean'],
            'is_demo' => ['boolean'],
            'materials' => ['nullable', 'string', 'max:3000'],
            'speakers' => ['nullable', 'array'],
            'speakers.*' => ['integer', 'exists:speakers,id'],
            'sessions' => ['required', 'array', 'min:1', 'max:20'],
            'sessions.*.title' => ['required', 'string', 'max:120'],
            'sessions.*.date' => ['required', 'date'],
            'sessions.*.start' => ['required', 'date_format:H:i'],
            'sessions.*.end' => ['required', 'date_format:H:i', 'after:sessions.*.start'],
        ];
    }

    public function messages(): array
    {
        return [
            'sessions.required' => 'Tambahkan minimal satu jadwal sesi.',
            'sessions.*.end.after' => 'Jam selesai sesi harus setelah jam mulai.',
        ];
    }

    /** Baris "Judul | https://..." menjadi daftar materi. */
    public function materials(): array
    {
        return collect(preg_split('/\R/', (string) $this->input('materials')))
            ->map(fn ($line) => array_map('trim', explode('|', $line, 2)))
            ->filter(fn ($parts) => count($parts) === 2 && filter_var($parts[1], FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $parts[1]))
            ->map(fn ($parts) => ['title' => $parts[0], 'url' => $parts[1]])->values()->all();
    }
}
