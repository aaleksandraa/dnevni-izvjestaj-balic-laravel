<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reports:send-weekly-summary')
    ->sundays()
    ->at('20:00');

Schedule::command('reports:send-monthly-summary')
    ->monthlyOn(1, '07:00');
