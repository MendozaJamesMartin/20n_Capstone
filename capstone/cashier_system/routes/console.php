<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule auto backup daily
Schedule::command('app:backup-database')->dailyAt('00:00');
// Schedule auto disapprove transactions
Schedule::command('app:auto-disapprove-transactions')->dailyAt('00:00');