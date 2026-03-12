<?php

namespace App\Http\Requests;

use App\Models\ReportEmailSetting;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportEmailSettingRequest extends FormRequest
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
            'report_type' => ['required', 'string', Rule::in(ReportEmailSetting::REPORT_TYPES)],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('report_email_settings', 'email')->where(function ($query): void {
                    $query->where('report_type', $this->input('report_type'));
                }),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
