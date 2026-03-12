<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientPaymentRequest extends FormRequest
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
            'report_date' => ['required', 'string'],
            'location_id' => ['required', 'integer', Rule::exists('locations', 'id')->where('is_active', true)],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'doctor_id' => ['nullable', 'integer', Rule::exists('staff_members', 'id')->where('is_active', true)],
            'is_new_patient' => ['sometimes', 'boolean'],
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
            'report_date' => trim((string) $this->input('report_date', '')),
            'is_new_patient' => $this->boolean('is_new_patient'),
            'payment_status' => trim((string) $this->input('payment_status', '')),
            'payment_method' => $this->filled('payment_method')
                ? trim((string) $this->input('payment_method'))
                : null,
        ]);
    }
}
