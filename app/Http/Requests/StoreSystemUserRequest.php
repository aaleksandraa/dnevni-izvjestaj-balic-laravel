<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSystemUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        return $user?->hasAnyRole(['glavni_admin', 'administrator_klinike']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['glavni_admin', 'administrator_klinike', 'medicinska_sestra'])],
            'phone' => ['nullable', 'string', 'max:60'],
            'is_active' => ['sometimes', 'boolean'],
            'can_submit_report' => ['sometimes', 'boolean'],
            'can_change_submitter' => ['sometimes', 'boolean'],
            'location_ids' => ['required', 'array', 'min:1'],
            'location_ids.*' => ['integer', 'distinct', 'exists:locations,id'],
        ];
    }
}
