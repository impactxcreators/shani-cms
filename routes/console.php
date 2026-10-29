<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Scheduled maintenance. Driven by one cron entry calling `php artisan schedule:run` each minute.
// Hostinger disables proc_open, so Schedule::command() (which starts a separate process) silently
// fails there. Every task therefore runs inside the scheduler itself via Schedule::call().
$task = fn (string $command) => fn () => Artisan::call($command);

// proves the cron job is firing; shown on the admin dashboard
Schedule::call(fn () => file_put_contents(storage_path('app/scheduler-heartbeat'), now()->toIso8601String()))
    ->name('heartbeat')->everyMinute();

Schedule::call($task('coupons:archive-expired'))->name('coupons:archive-expired')->dailyAt('02:00');
Schedule::call($task('clicks:prune'))->name('clicks:prune')->dailyAt('02:10');
Schedule::call($task('db:backup'))->name('db:backup')->dailyAt('01:30');
Schedule::call($task('push:send-scheduled'))->name('push:send-scheduled')->everyMinute()->withoutOverlapping();
Schedule::call($task('referers:check'))->name('referers:check')->hourly()->withoutOverlapping();
