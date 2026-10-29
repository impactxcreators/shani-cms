<?php

use Illuminate\Support\Facades\Schedule;

// Scheduled maintenance. Driven by one cron entry calling `php artisan schedule:run` each minute.
Schedule::command('coupons:archive-expired')->dailyAt('02:00');
Schedule::command('clicks:prune')->dailyAt('02:10');
Schedule::command('db:backup')->dailyAt('01:30');
Schedule::command('push:send-scheduled')->everyMinute()->withoutOverlapping();
Schedule::command('referers:check')->hourly()->withoutOverlapping();
