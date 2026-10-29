<?php

namespace App\Services;

use App\Models\PushMessage;
use App\Models\PushSubscription;
use Illuminate\Database\Eloquent\Builder;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Sends a PushMessage to subscribed browsers. Browsers that have
 * unsubscribed (expired endpoints) are removed automatically.
 */
class PushSender
{
    /** Send to everyone. Returns null if the campaign was already sent (e.g. double click). */
    public function send(PushMessage $message): ?array
    {
        // claim the campaign first so it can never go out twice
        if (! PushMessage::whereKey($message->getKey())->whereNull('sent_at')->update(['sent_at' => now()])) {
            return null;
        }

        $result = $this->deliver($message, PushSubscription::query());
        $message->update(['sent_at' => now(), 'sent_count' => $result['sent'], 'failed_count' => $result['failed']]);

        return $result;
    }

    /** Send only to the browsers this admin turned alerts on in. Counts are not recorded. */
    public function sendTest(PushMessage $message, int $userId): array
    {
        return $this->deliver($message, PushSubscription::where('user_id', $userId));
    }

    private function deliver(PushMessage $message, Builder $subscriptions): array
    {
        $sent = 0;
        $failed = 0;

        if (! (clone $subscriptions)->exists()) {
            return ['sent' => 0, 'failed' => 0];
        }

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
            // opens through the click counter, which then redirects to the campaign url
            'url' => route('push.click', $message),
            'tag' => 'msg-'.$message->id,
        ]);

        $subscriptions->chunkById(500, function ($subs) use ($webPush, $payload, &$sent, &$failed) {
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

        return ['sent' => $sent, 'failed' => $failed];
    }
}
