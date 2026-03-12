<?php

namespace App\Console\Commands;

use App\Jobs\SendPeriodicReportEmailJob;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class SendWeeklyReportSummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:send-weekly-summary
                            {--date= : Referentni datum (Y-m-d). Podrazumijevano danas}
                            {--sync : Posalji odmah bez queue radnika}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Salje sedmicni izvjestaj za prethodnu sedmicu.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $referenceDate = $this->option('date')
            ? Carbon::parse((string) $this->option('date'))
            : Carbon::now();

        $startDate = $referenceDate->copy()->startOfWeek(Carbon::MONDAY)->subWeek()->startOfDay();
        $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        if ((bool) $this->option('sync')) {
            SendPeriodicReportEmailJob::dispatchSync(
                'weekly',
                $startDate->toDateString(),
                $endDate->toDateString()
            );
        } else {
            SendPeriodicReportEmailJob::dispatch(
                'weekly',
                $startDate->toDateString(),
                $endDate->toDateString()
            );
        }

        $this->info(
            'Sedmicni izvjestaj zakazan/poslan za period: '
            .$startDate->format('d.m.Y')
            .' - '
            .$endDate->format('d.m.Y')
        );

        return self::SUCCESS;
    }
}
