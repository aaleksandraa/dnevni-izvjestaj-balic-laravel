<?php

namespace App\Http\Requests;

use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffMemberRequest extends FormRequest
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
        /** @var StaffMember|null $staffMember */
        $staffMember = $this->route('staff_member');

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'role_type' => ['required', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:120'],
            'specialty' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'internal_code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('staff_members', 'internal_code')
                    ->whereNull('deleted_at')
                    ->ignore($staffMember?->id),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'location_ids' => ['required', 'array', 'min:1'],
            'location_ids.*' => ['integer', 'distinct', 'exists:locations,id'],
        ];
    }
}
