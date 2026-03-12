<?php

namespace App\Jobs;

use App\Mail\DailyReportSubmittedMail;
use App\Models\DailyReport;
use App\Models\ReportEmailSetting;
use App\Services\ReportSummaryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendDailyReportEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $dailyReportId
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(ReportSummaryService $reportSummaryService): void
    {
        $dailyReport = DailyReport::query()
            ->with(['location', 'submittedBy', 'createdBy'])
            ->find($this->dailyReportId);

        if (! $dailyReport) {
            return;
        }

        $recipients = ReportEmailSetting::query()
            ->where('report_type', 'daily')
            ->where('is_active', true)
            ->pluck('email')
            ->unique()
            ->values()
            ->all();

        if (count($recipients) === 0) {
            return;
        }

        $summary = $reportSummaryService->daily($dailyReport);

        foreach ($recipients as $recipient) {
            Mail::to($recipient)->send(
                new DailyReportSubmittedMail($dailyReport, $summary)
            );
        }
    }
}
