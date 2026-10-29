<?php

namespace App\Console\Commands;

use App\Models\RefererDestination;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckRefererLinks extends Command
{
    protected $signature = 'referers:check {--limit=300 : How many destinations to check this run}';

    protected $description = 'Flag referer destinations that no longer load (404, 410, server errors, dead domains)';

    public function handle(): int
    {
        $dests = RefererDestination::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('checked_at')->orWhere('checked_at', '<', now()->subDay()))
            ->orderByRaw('checked_at is not null')->orderBy('checked_at')
            ->limit((int) $this->option('limit'))
            ->get();

        $broken = 0;
        foreach ($dests as $d) {
            try {
                $res = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36'])
                    ->connectTimeout(5)->timeout(12)
                    ->withOptions(['allow_redirects' => ['max' => 10], 'stream' => true])
                    ->get($d->url);
                $code = $res->status();
                // 403/429 etc. usually mean "no robots", not a dead link
                $status = in_array($code, [404, 410]) || $code >= 500 ? 'broken' : 'ok';
            } catch (\Throwable $e) {
                $code = null;
                $status = 'broken'; // domain gone, timeout, bad certificate…
            }

            $d->forceFill(['check_status' => $status, 'check_code' => $code, 'checked_at' => now()])->saveQuietly();
            $broken += $status === 'broken';
        }

        $this->info("Checked {$dests->count()} destination(s), {$broken} broken.");

        return self::SUCCESS;
    }
}
