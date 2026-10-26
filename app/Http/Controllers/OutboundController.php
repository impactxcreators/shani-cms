<?php

namespace App\Http\Controllers;

use App\Models\ClickEvent;
use App\Models\Coupon;
use App\Models\Deal;
use App\Models\Store;
use Illuminate\Http\Request;

class OutboundController extends Controller
{
    public function go(Request $request, string $type, int $id)
    {
        [$entity, $destination] = $this->resolve($type, $id);
        abort_if(! $entity || ! $destination, 404);

        // Log the click (IP hashed for privacy).
        ClickEvent::create([
            'entity_type' => $type,
            'entity_id' => $id,
            'destination_url' => $destination,
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'referrer' => substr((string) $request->headers->get('referer'), 0, 1000),
            'created_at' => now(),
        ]);

        if ($type === 'coupon') {
            $entity->increment('used_count');
        }

        return redirect()->away($destination, 302);
    }

    private function resolve(string $type, int $id): array
    {
        return match ($type) {
            'coupon' => (function () use ($id) {
                $c = Coupon::with('store')->find($id);
                return [$c, $c?->destination_url ?: $c?->store?->affiliate_url ?: $c?->store?->website_url];
            })(),
            'deal' => (function () use ($id) {
                $d = Deal::with('store')->find($id);
                return [$d, $d?->destination_url ?: $d?->store?->affiliate_url ?: $d?->store?->website_url];
            })(),
            'store' => (function () use ($id) {
                $s = Store::find($id);
                return [$s, $s?->affiliate_url ?: $s?->website_url];
            })(),
            default => [null, null],
        };
    }
}
