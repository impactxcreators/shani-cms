<?php

namespace App\Console\Commands;

use App\Models\ClickEvent;
use Illuminate\Console\Command;

class PruneClickEvents extends Command
{
    protected $signature = 'clicks:prune {--days=90}';
    protected $description = 'Delete click events older than the given number of days (default 90).';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $count = ClickEvent::where('created_at', '<', now()->subDays($days))->delete();
        $this->info("Pruned {$count} click event(s) older than {$days} days.");
        return self::SUCCESS;
    }
}
