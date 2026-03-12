<?php

namespace App\Http\Requests;

use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceCategoryRequest extends FormRequest
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
        /** @var ServiceCategory|null $serviceCategory */
        $serviceCategory = $this->route('service_category');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('service_categories', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($serviceCategory?->id),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
