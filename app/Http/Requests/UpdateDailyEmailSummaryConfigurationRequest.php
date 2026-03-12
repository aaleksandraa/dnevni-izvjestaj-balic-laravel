<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDailyEmailSummaryConfigurationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        return $user?->hasAnyRole([
            'glavni_admin',
            'administrator_klinike',
        ]) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', Rule::exists('services', 'id')->where('is_active', true)],

            'collaborator_ids' => ['nullable', 'array'],
            'collaborator_ids.*' => [
                'integer',
                Rule::exists('staff_members', 'id')
                    ->where('is_active', true)
                    ->where('role_type', 'saradnik'),
            ],

            'lead_doctor_ids' => ['nullable', 'array'],
            'lead_doctor_ids.*' => [
                'integer',
                Rule::exists('staff_members', 'id')
                    ->where('is_active', true)
                    ->whereIn('role_type', ['primarni_doktor', 'sekundarni_doktor']),
            ],

            'include_new_patients' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'service_ids' => array_values((array) $this->input('service_ids', [])),
            'collaborator_ids' => array_values((array) $this->input('collaborator_ids', [])),
            'lead_doctor_ids' => array_values((array) $this->input('lead_doctor_ids', [])),
            'include_new_patients' => $this->boolean('include_new_patients'),
        ]);
    }
}
