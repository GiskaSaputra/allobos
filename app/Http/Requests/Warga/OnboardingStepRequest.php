<?php

namespace App\Http\Requests\Warga;

use App\Models\BusinessField;
use App\Models\ResidentProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardingStepRequest extends FormRequest
{
    public function rules(): array
    {
        return match ((int) $this->route('step')) {
            1 => ['district_id' => ['required', 'exists:districts,id']],
            2 => [
                'business_status' => ['required', Rule::in(['belum', 'sudah'])],
                'existing_business' => ['nullable', 'required_if:business_status,sudah', 'string', 'max:120'],
            ],
            3 => [
                'interests' => ['required', 'array', 'min:1', 'max:3'],
                'interests.*' => ['integer', Rule::exists(BusinessField::class, 'id')->where('kind', 'usaha')],
            ],
            4 => [
                'capital_range' => ['required', Rule::in(array_keys(ResidentProfile::CAPITAL))],
                'free_time' => ['required', Rule::in(array_keys(ResidentProfile::FREE_TIME))],
            ],
            5 => ['skills' => ['required', 'string', 'min:3', 'max:500']],
            default => abort(404),
        };
    }

    public function messages(): array
    {
        return [
            'existing_business.required_if' => 'Tulis nama atau jenis usaha yang sudah berjalan.',
            'interests.max' => 'Pilih paling banyak tiga bidang.',
            'interests.required' => 'Pilih minimal satu bidang.',
        ];
    }
}
