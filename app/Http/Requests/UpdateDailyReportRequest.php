<?php

namespace App\Http\Requests;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDailyReportRequest extends FormRequest
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
        /** @var DailyReport|null $dailyReport */
        $dailyReport = $this->route('daily_report');

        return [
            'report_date' => ['required', 'date'],
            'location_id' => [
                'required',
                'integer',
                'exists:locations,id',
                Rule::unique('daily_reports')
                    ->where(function ($query): void {
                        $query
                            ->where('report_date', $this->input('report_date'))
                            ->whereNull('deleted_at');
                    })
                    ->ignore($dailyReport?->id),
            ],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'string', Rule::in(['u_radu', 'podnesen', 'zakljucan'])],
        ];
    }
}
