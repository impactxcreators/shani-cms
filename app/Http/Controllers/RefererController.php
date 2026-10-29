<?php

namespace App\Http\Controllers;

use App\Models\RefererClick;
use App\Models\RefererLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefererController extends Controller
{
    private const HEADERS = ['X-Robots-Tag' => 'noindex, nofollow', 'Cache-Control' => 'no-store, max-age=0'];

    public function go(Request $request, string $code)
    {
        $link = RefererLink::with('destinations')->where('code', $code)->first();
        if (! $link) {
            return redirect('/', 302)->withHeaders(self::HEADERS);
        }

        $fallback = $link->fallback_url ?: url('/');
        $dest = $link->isLive() ? $link->pickDestination() : null;
        if (! $dest) {
            return redirect()->away($fallback, 302)->withHeaders(self::HEADERS);
        }

        if ($request->isMethod('GET') && ! $this->isBot($request)) {
            $link->increment('click_count', 1, ['last_clicked_at' => now()]);
            $dest->increment('click_count');
            RefererClick::upsert(
                [['referer_link_id' => $link->id, 'date' => now()->toDateString(), 'clicks' => 1]],
                ['referer_link_id', 'date'],
                ['clicks' => DB::raw('clicks + 1')]
            );
        }

        if ($link->type === 'blank') {
            // the destination sees no referrer, so it cannot tell the visitor came from us
            return response()->view('referers.blank', ['url' => $dest->url])
                ->withHeaders(self::HEADERS + ['Referrer-Policy' => 'no-referrer']);
        }

        return redirect()->away($dest->url, 302)->withHeaders(self::HEADERS);
    }

    private function isBot(Request $request): bool
    {
        return (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|embedly|preview|curl|wget|python|httpclient|headless|monitor|scan/i', (string) $request->userAgent());
    }
}
