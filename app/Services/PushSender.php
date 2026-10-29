<?php

namespace App\Services;

use App\Models\PushMessage;
use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Sends a PushMessage to every subscribed browser. Browsers that have
 * unsubscribed (expired endpoints) are removed automatically.
 */
class PushSender
{
    public function send(PushMessage $message): array
    {
        $sent = 0;
        $failed = 0;

        if (PushSubscription::exists()) {
            $webPush = new WebPush(['VAPID' => [
                'subject' => config('cms.push.subject'),
                'publicKey' => config('cms.push.public_key'),
                'privateKey' => config('cms.push.private_key'),
            ]], ['TTL' => 86400, 'urgency' => 'normal']);

            $payload = json_encode([
                'title' => $message->title,
                'body' => $message->body,
                'image' => $message->image_url ?: null,
                'icon' => url('/favicon.ico'),
                // opens through the click counter, which then redirects to the message url
                'url' => route('push.click', $message),
                'tag' => 'msg-'.$message->id,
            ]);

            PushSubscription::query()->chunkById(500, function ($subs) use ($webPush, $payload, &$sent, &$failed) {
                foreach ($subs as $sub) {
                    $webPush->queueNotification(new Subscription(
                        $sub->endpoint, $sub->public_key, $sub->auth_token, $sub->content_encoding
                    ), $payload);
                }

                foreach ($webPush->flush() as $report) {
                    if ($report->isSuccess()) {
                        $sent++;
                        continue;
                    }
                    $failed++;
                    if ($report->isSubscriptionExpired()) {
                        PushSubscription::where('endpoint_hash', PushSubscription::hashEndpoint($report->getEndpoint()))->delete();
                    }
                }
            });
        }

        $message->update(['sent_at' => now(), 'sent_count' => $sent, 'failed_count' => $failed]);

        return ['sent' => $sent, 'failed' => $failed];
    }
}
