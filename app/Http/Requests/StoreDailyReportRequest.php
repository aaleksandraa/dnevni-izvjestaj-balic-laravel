<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDailyReportRequest extends FormRequest
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
            'report_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:today'],
            'location_id' => [
                'required',
                'integer',
                'exists:locations,id',
                Rule::unique('daily_reports')->where(function ($query): void {
                    $query
                        ->where('report_date', $this->input('report_date'))
                        ->whereNull('deleted_at');
                }),
            ],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'report_date.after_or_equal' => 'Dnevni izvjestaj se moze kreirati samo za danasnji datum.',
            'report_date.before_or_equal' => 'Dnevni izvjestaj se moze kreirati samo za danasnji datum.',
        ];
    }
}
