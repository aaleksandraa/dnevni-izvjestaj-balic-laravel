<?php

namespace App\Mail;

use App\Models\DailyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyReportSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public DailyReport $dailyReport,
        public array $summary
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $locationName = $this->dailyReport->location?->name ?? '-';
        $reportDate = $this->dailyReport->report_date->format('d.m.Y');

        return new Envelope(
            subject: "Dnevni izvjestaj podnesen - {$locationName} - {$reportDate}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-report-submitted',
        );
    }
}
