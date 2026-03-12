<?php

namespace App\Console\Commands;

use App\Jobs\SendPeriodicReportEmailJob;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class SendMonthlyReportSummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:send-monthly-summary
                            {--month= : Ciljani mjesec u formatu YYYY-MM. Podrazumijevano prethodni mjesec}
                            {--sync : Posalji odmah bez queue radnika}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Salje mjesecni izvjestaj za prethodni mjesec.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('month')) {
            $month = Carbon::createFromFormat('Y-m', (string) $this->option('month'))->startOfMonth();
        } else {
            $month = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        }

        $startDate = $month->copy()->startOfMonth()->startOfDay();
        $endDate = $month->copy()->endOfMonth()->endOfDay();

        if ((bool) $this->option('sync')) {
            SendPeriodicReportEmailJob::dispatchSync(
                'monthly',
                $startDate->toDateString(),
                $endDate->toDateString()
            );
        } else {
            SendPeriodicReportEmailJob::dispatch(
                'monthly',
                $startDate->toDateString(),
                $endDate->toDateString()
            );
        }

        $this->info(
            'Mjesecni izvjestaj zakazan/poslan za period: '
            .$startDate->format('d.m.Y')
            .' - '
            .$endDate->format('d.m.Y')
        );

        return self::SUCCESS;
    }
}
