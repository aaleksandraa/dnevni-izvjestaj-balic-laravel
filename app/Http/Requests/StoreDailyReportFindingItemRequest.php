<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDailyReportFindingItemRequest extends FormRequest
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
            'medicinska_sestra',
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
            'finding_id' => ['required', 'integer', 'exists:findings,id'],
            'finding_patient_id' => [
                'nullable',
                'integer',
                Rule::exists('patients', 'id')->where('is_active', true),
            ],
            'finding_patient_name' => ['nullable', 'string', 'min:2', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'finding_patient_id' => $this->filled('finding_patient_id')
                ? (int) $this->input('finding_patient_id')
                : null,
            'finding_patient_name' => $this->filled('finding_patient_name')
                ? trim((string) $this->input('finding_patient_name'))
                : null,
        ]);
    }
}
