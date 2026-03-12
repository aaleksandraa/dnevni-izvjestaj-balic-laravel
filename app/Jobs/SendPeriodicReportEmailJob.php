<?php

namespace App\Jobs;

use App\Mail\PeriodicReportSummaryMail;
use App\Models\ReportEmailSetting;
use App\Services\ReportSummaryService;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendPeriodicReportEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $reportType,
        public string $startDate,
        public string $endDate
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(ReportSummaryService $reportSummaryService): void
    {
        $reportType = trim(strtolower($this->reportType));

        if (! in_array($reportType, ['weekly', 'monthly'], true)) {
            return;
        }

        $recipients = ReportEmailSetting::query()
            ->where('report_type', $reportType)
            ->where('is_active', true)
            ->pluck('email')
            ->unique()
            ->values()
            ->all();

        if (count($recipients) === 0) {
            return;
        }

        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        $summary = $reportSummaryService->period($startDate, $endDate);

        foreach ($recipients as $recipient) {
            Mail::to($recipient)->send(
                new PeriodicReportSummaryMail($reportType, $startDate, $endDate, $summary)
            );
        }
    }
}
