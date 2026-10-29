<?php

namespace App\Http\Controllers;

use App\Models\PushMessage;
use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'starts_with:https://', 'max:2000'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'contentEncoding' => ['nullable', 'in:aesgcm,aes128gcm'],
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => PushSubscription::hashEndpoint($data['endpoint'])],
            [
                'endpoint' => $data['endpoint'],
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => $data['contentEncoding'] ?? 'aes128gcm',
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request)
    {
        $endpoint = (string) $request->input('endpoint');
        if ($endpoint !== '') {
            PushSubscription::where('endpoint_hash', PushSubscription::hashEndpoint($endpoint))->delete();
        }

        return response()->json(['ok' => true]);
    }

    /** Counts the click, then sends the visitor to the message's page. */
    public function click(PushMessage $message)
    {
        $message->increment('click_count');

        $url = $message->url ?: url('/');
        $url .= (str_contains($url, '?') ? '&' : '?').'utm_source=push&utm_medium=notification&utm_campaign=msg-'.$message->id;

        return redirect()->away($url);
    }
}
