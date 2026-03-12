<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDailyReportItemRequest extends FormRequest
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
            'patient_id' => [
                'nullable',
                'integer',
                Rule::exists('patients', 'id')->where('is_active', true),
                'required_without:patient_name',
            ],
            'patient_name' => ['nullable', 'string', 'min:2', 'max:255', 'required_without:patient_id'],
            'is_new_patient' => ['sometimes', 'boolean'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'doctor_id' => ['nullable', 'integer', 'exists:staff_members,id'],
            'item_price' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'string', Rule::in(['placeno', 'neplaceno', 'djelimicno_placeno'])],
            'payment_method' => [
                'nullable',
                'string',
                Rule::in(['fiskalno', 'nefiskalno', 'karticno', 'ziralno']),
            ],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'unpaid_reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'patient_id' => $this->filled('patient_id')
                ? (int) $this->input('patient_id')
                : null,
            'patient_name' => $this->filled('patient_name')
                ? trim((string) $this->input('patient_name'))
                : null,
            'is_new_patient' => $this->boolean('is_new_patient'),
            'payment_status' => trim((string) $this->input('payment_status', '')),
            'payment_method' => $this->filled('payment_method')
                ? trim((string) $this->input('payment_method'))
                : null,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'patient_id.required_without' => 'Odaberite postojeceg pacijenta ili unesite novo ime pacijenta.',
            'patient_name.required_without' => 'Unesite ime pacijenta ili odaberite postojeceg pacijenta.',
            'patient_name.min' => 'Ime pacijenta mora imati najmanje 2 karaktera.',
            'payment_status.in' => 'Status placanja mora biti placeno, neplaceno ili djelimicno_placeno.',
        ];
    }
}
