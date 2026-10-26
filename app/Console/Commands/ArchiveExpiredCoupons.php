<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use Illuminate\Console\Command;

class ArchiveExpiredCoupons extends Command
{
    protected $signature = 'coupons:archive-expired';
    protected $description = 'Archive coupons whose expiry date has passed.';

    public function handle(): int
    {
        $count = Coupon::where('status', 'published')
            ->whereNotNull('expires_at')->where('expires_at', '<', now())
            ->update(['status' => 'archived']);

        $this->info("Archived {$count} expired coupon(s).");
        return self::SUCCESS;
    }
}
