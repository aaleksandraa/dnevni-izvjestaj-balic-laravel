<?php

namespace App\Http\Requests;

use App\Models\ReportEmailSetting;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReportEmailSettingRequest extends FormRequest
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
        /** @var ReportEmailSetting|null $setting */
        $setting = $this->route('report_email_setting');

        return [
            'report_type' => ['required', 'string', Rule::in(ReportEmailSetting::REPORT_TYPES)],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('report_email_settings', 'email')
                    ->where(function ($query): void {
                        $query->where('report_type', $this->input('report_type'));
                    })
                    ->ignore($setting?->id),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
