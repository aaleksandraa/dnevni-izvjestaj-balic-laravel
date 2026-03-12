<?php

namespace App\Mail;

use Illuminate\Support\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PeriodicReportSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $reportType,
        public Carbon $startDate,
        public Carbon $endDate,
        public array $summary
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $label = $this->reportType === 'weekly' ? 'Sedmicni' : 'Mjesecni';
        $period = $this->startDate->format('d.m.Y').' - '.$this->endDate->format('d.m.Y');

        return new Envelope(
            subject: "{$label} izvjestaj - {$period}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.periodic-report-summary',
        );
    }
}
