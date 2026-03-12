<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFindingRequest extends FormRequest
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
        $finding = $this->route('finding');

        return [
            'finding_category_id' => ['nullable', 'integer', 'exists:finding_categories,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('findings', 'name')
                    ->where(function ($query): void {
                        $query
                            ->where('finding_category_id', $this->input('finding_category_id'))
                            ->whereNull('deleted_at');
                    })
                    ->ignore($finding?->id),
            ],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
