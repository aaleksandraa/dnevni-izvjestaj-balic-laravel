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
            'finding_payment_status' => ['required', 'string', Rule::in(['placeno', 'neplaceno', 'djelimicno_placeno'])],
            'finding_payment_method' => ['nullable', 'string', 'max:50'],
            'finding_paid_amount' => ['nullable', 'numeric', 'min:0'],
            'finding_unpaid_reason' => ['nullable', 'string'],
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
            'finding_payment_status' => trim((string) $this->input('finding_payment_status', '')),
            'finding_payment_method' => $this->filled('finding_payment_method')
                ? trim((string) $this->input('finding_payment_method'))
                : null,
            'finding_unpaid_reason' => $this->filled('finding_unpaid_reason')
                ? trim((string) $this->input('finding_unpaid_reason'))
                : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'finding_payment_status.in' => 'Status placanja mora biti placeno, neplaceno ili djelimicno_placeno.',
        ];
    }
}
