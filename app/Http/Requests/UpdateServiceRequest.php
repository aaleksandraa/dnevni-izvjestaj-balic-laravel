<?php

namespace App\Http\Requests;

use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
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
        /** @var Service|null $service */
        $service = $this->route('service');

        return [
            'service_category_id' => ['required', 'integer', 'exists:service_categories,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'name')
                    ->where(function ($query): void {
                        $query
                            ->where('service_category_id', $this->input('service_category_id'))
                            ->whereNull('deleted_at');
                    })
                    ->ignore($service?->id),
            ],
            'base_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('services', 'code')
                    ->whereNull('deleted_at')
                    ->ignore($service?->id),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
