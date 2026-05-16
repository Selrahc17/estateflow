<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-cancel expired reservations every day at 01:00
Schedule::command('reservations:auto-cancel')->dailyAt('01:00');

// Run data retention wipe every day at midnight
Schedule::command('retention:wipe')->dailyAt('00:00');

// Send appointment reminders every day at 8am
Schedule::command('reservations:send-reminders')->dailyAt('08:00');
