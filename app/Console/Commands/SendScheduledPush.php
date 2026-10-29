<?php

namespace App\Console\Commands;

use App\Models\PushMessage;
use App\Services\PushSender;
use Illuminate\Console\Command;

class SendScheduledPush extends Command
{
    protected $signature = 'push:send-scheduled';

    protected $description = 'Send push campaigns whose scheduled time has arrived';

    public function handle(PushSender $sender): int
    {
        $due = PushMessage::whereNull('sent_at')->whereNotNull('scheduled_at')->where('scheduled_at', '<=', now())->get();

        foreach ($due as $message) {
            $result = $sender->send($message);
            if ($result) {
                $this->info("Sent \"{$message->title}\" to {$result['sent']} subscriber(s).");
            }
        }

        return self::SUCCESS;
    }
}
