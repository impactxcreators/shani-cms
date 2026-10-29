<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Storage;

/** Shows whether the hPanel cron job is firing, and when the last database backup was made. */
class SchedulerStatus extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $file = storage_path('app/scheduler-heartbeat');
        $last = is_file($file) ? Carbon::parse(trim((string) file_get_contents($file))) : null;
        $running = $last && $last->gt(now()->subMinutes(5));

        $backups = collect(Storage::disk('local')->files('backups'))->sort()->values();
        $lastBackup = $backups->isNotEmpty() ? Carbon::createFromTimestamp(Storage::disk('local')->lastModified($backups->last())) : null;
        $backupOk = $lastBackup && $lastBackup->gt(now()->subHours(26));

        return [
            Stat::make('Scheduled tasks', $running ? 'Running' : 'Not running')
                ->description($last ? 'Last run '.$last->diffForHumans() : 'Never ran: check the hPanel cron job')
                ->color($running ? 'success' : 'danger'),
            Stat::make('Last database backup', $lastBackup ? $lastBackup->diffForHumans() : 'None yet')
                ->description($backupOk ? $backups->count().' backup(s) kept' : 'Backups run nightly at 01:30')
                ->color($backupOk ? 'success' : 'warning'),
        ];
    }
}
