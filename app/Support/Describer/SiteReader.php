<?php

namespace App\Support\Describer;

use App\Models\Coupon;
use App\Models\Deal;
use App\Models\Product;
use App\Models\RefererLink;
use App\Models\Store;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Reads a shop's web page and pulls out what a copywriter would look at:
 * shop name, tagline, description, what it sells, headline features and perks.
 */
class SiteReader
{
    /** link-tracking networks: their links redirect to the shop */
    private const NETWORKS = ['awin1.com', 'pxf.io', 'sjv.io', '7eer.net', 'evyy.net', 'kqzyfj.com', 'anrdoezrs.net', 'jdoqocy.com',
        'tkqlhce.com', 'dpbolvw.net', 'shareasale.com', 'linksynergy.com', 'prf.hn', 'partnerize.com', 'tradedoubler.com', 'webgains.com',
        'amzn.to', 'go2cloud.org', 'clickbank.net', 'avantlink.com', 'pntra.com', 'pjtra.com', 'gopjn.com', 'pjatr.com', 'r-bloop.cc',
        'flexoffers.com', 'skimresources.com', 'refersion.com', 'rfrl.co', 'linkconnector.com', 'cj.com', 'emjcd.com', 'qksrv.net',
        'dpbolvw.net', 'impact.com', 'ojrq.net', 'tapfiliate.com', 'go.link', 'bit.ly', 'tinyurl.com'];

    /** query parameters that usually hold the real shop address inside a tracking link */
    private const DEST_PARAMS = ['ued', 'murl', 'url', 'u', 'dest', 'destination', 'dl', 'redirect', 'redirect_url', 'r', 'goto', 'link', 'target', 'k_site'];

    /** tracking parameters removed before visiting a shop, so no referral click is recorded */
    private const TRACKING = ['sca_ref', 'ref', 'aff', 'aff_id', 'affid', 'affiliate', 'a_aid', 'a_bid', 'irclickid', 'clickid', 'sscid',
        'gclid', 'fbclid', 'ref_id', 'refid', 'partner', 'awc', 'sv1', 'sv_campaign_id', 'ranmid', 'raneaid', 'ransiteid', 'tag', 'sc',
        'aid', 'aff_adid', 'subid', 'sub_id', 'click_id', 'tracking', 'campaign'];

    private const PERKS = [
        'free shipping' => '/\bfree (?:standard |express |uk |us |worldwide )?(?:shipping|delivery)\b/',
        'fast delivery' => '/\b(?:fast|express|next[- ]day|same[- ]day|quick) (?:shipping|delivery|dispatch)\b/',
        'worldwide shipping' => '/\b(?:worldwide|international|global) (?:shipping|delivery)\b|\bships? (?:worldwide|internationally)\b/',
        'returns' => '/\b(?:free|easy|hassle[- ]free) returns\b|\b(\d{2,3})[- ]day (?:free |easy )?returns?\b/',
        'guarantee' => '/\b(?:(\d{2,3})[- ]day )?money[- ]back guarantee\b|\bsatisfaction guarantee(?:d)?\b/',
        'round-the-clock support' => '/\b24\/7 (?:customer |live |expert )?(?:support|service|help)\b/',
        'secure checkout' => '/\bsecure (?:checkout|payments?)\b/',
        'a free trial' => '/\bfree trial\b/',
        'eco-friendly materials' => '/\beco[- ]friendly\b|\bsustainabl[ey]\b|\brecycled\b/',
        'handmade quality' => '/\bhand[- ]?made\b|\bhandcrafted\b/',
        'personalised options' => '/\bpersonali[sz]ed?\b|\bcustomi[sz]able\b/',
        'wholesale pricing' => '/\bwholesale\b|\bbulk (?:orders?|pricing|discounts?)\b/',
        'no minimum order' => '/\bno minimum(?: order)?\b/',
        'products made in the USA' => '/\bmade in (?:the )?usa\b|\bmade in america\b/',
        'warranty' => '/\b(\d{1,2})[- ](?:year|yr)s? warranty\b|\blifetime warranty\b/',
    ];

    public function __construct(private int $timeout = 20)
    {
    }

    /**
     * @return array{ok:bool, url:string, note:?string, shop:?string, tagline:?string, description:?string,
     *               sentences:array, offering:?string, features:array, perks:array, customers:?string, since:?string,
     *               product:array}
     */
    public function read(?string $link): array
    {
        $link = trim((string) $link);
        if ($link === '') {
            return $this->empty('', 'No link entered.');
        }

        [$url, $note] = $this->resolve($link);
        if ($url === null) {
            return $this->empty($link, $note ?? 'Could not work out which website this link goes to.');
        }

        $key = 'describer:site:'.sha1($url);
        $info = Cache::get($key);
        if ($info === null) {
            $info = $this->fetchAndParse($url);
            Cache::put($key, $info, $info['ok'] ? now()->addDays(7) : now()->addHour());
        }
        if ($note && $info['ok']) {
            $info['note'] = $note;
        }

        return $info;
    }

    /** Turns any link (our /ref/ and /go/ links, tracking links) into the shop address to read. */
    public function resolve(string $link): array
    {
        if (! preg_match('#^https?://#i', $link)) {
            $link = 'https://'.ltrim($link, '/');
        }
        $host = strtolower((string) parse_url($link, PHP_URL_HOST));
        $path = (string) parse_url($link, PHP_URL_PATH);
        $ownHost = strtolower((string) parse_url(url('/'), PHP_URL_HOST));

        // our own short links: look up the destination instead of "clicking" them
        if ($host === $ownHost || $host === 'www.'.$ownHost) {
            if (preg_match('#^/ref/(.+)$#', $path, $m)) {
                $dest = RefererLink::with('destinations')->where('code', $m[1])->first()?->destinations->first()?->url;

                return $dest ? $this->resolve($dest) : [null, 'This short link has no destination.'];
            }
            if (preg_match('#^/go/(coupon|deal|store|product)/(\d+)#', $path, $m)) {
                $dest = match ($m[1]) {
                    'coupon' => ($c = Coupon::with('store')->find($m[2])) ? ($c->destination_url ?: $c->store?->website_url ?: $c->store?->affiliate_url) : null,
                    'deal' => ($d = Deal::with('store')->find($m[2])) ? ($d->destination_url ?: $d->store?->website_url ?: $d->store?->affiliate_url) : null,
                    'product' => Product::find($m[2])?->affiliate_url,
                    'store' => ($s = Store::find($m[2])) ? ($s->website_url ?: $s->affiliate_url) : null,
                };

                return $dest ? $this->resolve($dest) : [null, 'That link has no destination.'];
            }

            return [null, 'This link points to your own site, not a shop.'];
        }

        // tracking links often carry the shop address inside them
        parse_str((string) parse_url($link, PHP_URL_QUERY), $query);
        foreach (self::DEST_PARAMS as $p) {
            $v = $query[$p] ?? null;
            if (is_string($v)) {
                $v = rawurldecode($v);
                if (preg_match('#^https?://[^/]+\.[a-z]{2,}#i', $v) && ! str_contains($v, $host)) {
                    return $this->resolve($v);
                }
            }
        }

        $isNetwork = (bool) array_filter(self::NETWORKS, fn ($n) => $host === $n || str_ends_with($host, '.'.$n));
        if ($isNetwork) {
            return [$link, 'Read through the tracking link (it may count as one click in your affiliate stats).'];
        }

        return [$this->stripTracking($link), null];
    }

    private function stripTracking(string $url): string
    {
        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $q);
        $q = array_filter($q, fn ($v, $k) => ! in_array(strtolower((string) $k), self::TRACKING, true) && ! str_starts_with(strtolower((string) $k), 'utm_'), ARRAY_FILTER_USE_BOTH);

        return ($parts['scheme'] ?? 'https').'://'.$parts['host'].($parts['path'] ?? '/').($q ? '?'.http_build_query($q) : '');
    }

    private function fetch(string $url, bool $retry = true): ?array
    {
        try {
            $res = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->connectTimeout(6)->timeout($this->timeout)
                ->withOptions(['allow_redirects' => ['max' => 10, 'track_redirects' => true]])
                ->get($url);
        } catch (\Throwable $e) {
            return $retry ? $this->fetch($url, false) : null;
        }
        if ($retry && $res->status() >= 500) {
            return $this->fetch($url, false);
        }
        $history = $res->getHeader('X-Guzzle-Redirect-History');

        return ['status' => $res->status(), 'body' => substr($res->body(), 0, 1_500_000), 'final' => $history ? end($history) : $url];
    }

    private function fetchAndParse(string $url): array
    {
        $page = $this->fetch($url);
        if (! $page || $page['status'] >= 400 || strlen($page['body']) < 200) {
            // product page blocked or gone: try the shop's home page
            $home = preg_replace('#^(https?://[^/]+).*$#', '$1/', $page['final'] ?? $url);
            if ($home !== $url) {
                $page = $this->fetch($home);
            }
        }
        if (! $page || $page['status'] >= 400 || strlen($page['body']) < 200) {
            $why = ! $page ? 'the site did not respond' : ($page['status'] === 403 || $page['status'] === 429 ? 'the site blocks automatic visits' : 'the page could not be loaded (error '.$page['status'].')');

            return $this->empty($url, 'Could not read the website: '.$why.'. The description was written from the title and your details only.');
        }

        $info = $this->parse($page['body'], $page['final']);

        // thin product page: also read the home page for the shop's own story
        if (! $info['description'] && ! $info['offering']) {
            $home = preg_replace('#^(https?://[^/]+).*$#', '$1/', $page['final']);
            if ($home !== $page['final'] && ($h = $this->fetch($home)) && $h['status'] < 400) {
                $homeInfo = $this->parse($h['body'], $h['final']);
                foreach (['shop', 'tagline', 'description', 'offering', 'customers', 'since'] as $k) {
                    $info[$k] = $info[$k] ?: $homeInfo[$k];
                }
                $info['sentences'] = $info['sentences'] ?: $homeInfo['sentences'];
                $info['features'] = array_values(array_unique(array_merge($info['features'], $homeInfo['features'])));
                $info['perks'] = array_values(array_unique(array_merge($info['perks'], $homeInfo['perks'])));
            }
        }

        return $info;
    }

    public function parse(string $html, string $url): array
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        $xp = new DOMXPath($dom);

        $meta = function (string $name) use ($xp): ?string {
            foreach (["//meta[@property='$name']", "//meta[@name='$name']"] as $q) {
                $n = $xp->query($q)->item(0);
                if ($n && trim($n->getAttribute('content')) !== '') {
                    return Text::clean($n->getAttribute('content'));
                }
            }

            return null;
        };
        $text = fn (string $q) => ($n = $xp->query($q)->item(0)) ? Text::clean($n->textContent) : null;

        $ld = $this->jsonLd($xp);
        $title = $text('//title') ?? '';
        $host = preg_replace('/^www\./', '', strtolower((string) parse_url($url, PHP_URL_HOST)));
        $stem = preg_replace('/[^a-z0-9]/', '', explode('.', $host)[0] ?? '');

        // shop name: og:site_name → structured data → the title part matching the domain → the domain
        $segments = array_values(array_filter(array_map(fn ($s) => Text::clean($s), preg_split('/\s+[|–—:-]\s+|\s+›\s+/u', $title) ?: [])));
        $shop = $meta('og:site_name') ?: ($ld['org'] ?? null) ?: $meta('application-name');
        if (! $shop) {
            foreach ($segments as $seg) {
                $flat = preg_replace('/[^a-z0-9]/', '', strtolower($seg));
                if ($stem !== '' && ($flat === $stem || str_contains($flat, $stem) || str_contains($stem, $flat) && strlen($flat) > 3)) {
                    $shop = $seg;
                    break;
                }
            }
        }
        $shop = $shop ?: ucfirst($stem);
        if (preg_match('/\s[|–—:]\s|\s-\s/u', $shop)) {
            $parts = array_values(array_filter(array_map(fn ($s) => trim($s), preg_split('/\s+[|–—:-]\s+/u', $shop) ?: [])));
            $match = array_values(array_filter($parts, fn ($p) => $stem !== '' && str_contains(preg_replace('/[^a-z0-9]/', '', strtolower($p)), $stem)));
            $shop = $match[0] ?? $parts[0] ?? $shop;
        }
        $shop = Text::limit(preg_replace('/\s+(official (online )?(store|site|shop)|online store|home)$/i', '', $shop), 40);

        // tagline: the remaining title part, else the h1
        $tagline = null;
        foreach ($segments as $seg) {
            if (mb_strtolower($seg) !== mb_strtolower($shop) && mb_strlen($seg) > 12 && ! preg_match('/^(home|welcome|official site)/i', $seg)) {
                $tagline = $seg;
                break;
            }
        }
        $h1 = $text('//h1');
        $tagline = $tagline ?: ($h1 && mb_strlen($h1) > 12 && mb_strlen($h1) < 110 ? $h1 : null);

        // remove page chrome before reading body text
        foreach (['//script', '//style', '//noscript', '//nav', '//footer', '//header', '//form', '//svg', '//iframe', "//*[contains(@class,'cookie')]", "//*[contains(@id,'cookie')]"] as $q) {
            foreach (iterator_to_array($xp->query($q)) as $n) {
                $n->parentNode?->removeChild($n);
            }
        }

        $paragraphs = [];
        foreach ($xp->query('//p') as $p) {
            $t = Text::clean($p->textContent);
            if (mb_strlen($t) >= 50 && mb_strlen($t) <= 400 && ! $this->isJunk($t)) {
                $paragraphs[] = $t;
            }
            if (count($paragraphs) >= 6) {
                break;
            }
        }

        // description: the best of the page's own summaries
        $candidates = array_filter([$meta('description'), $meta('og:description'), $meta('twitter:description'), $ld['description'] ?? null, $paragraphs[0] ?? null]);
        $description = null;
        foreach ($candidates as $c) {
            if (mb_strlen($c) >= 40 && ! $this->isJunk($c) && mb_strtolower($c) !== mb_strtolower($title)) {
                $description = $description && mb_strlen($description) >= mb_strlen($c) ? $description : $c;
            }
        }

        $sentences = [];
        foreach (array_merge(Text::sentences($description), ...array_map(fn ($p) => Text::sentences($p), array_slice($paragraphs, 0, 3))) as $s) {
            if (mb_strlen($s) >= 35 && mb_strlen($s) <= 260 && ! $this->isJunk($s) && Text::capitalRatio($s) < 0.6 && ! in_array($s, $sentences, true) && $this->hasVerb($s) && preg_match_all('/\b(you|your|you\'ll|you’ll|you\'re|you’re)\b/i', $s) < 3) {
                $sentences[] = $s;
            }
        }

        // features: short headings and list items, the way a shop lists its strengths
        $features = [];
        foreach ($xp->query('//h2 | //h3 | //li') as $n) {
            if ($n->nodeName === 'li' && $n->childNodes->length === 1 && $n->firstChild?->nodeName === 'a') {
                continue; // menu item
            }
            $t = Text::clean($n->textContent);
            $words = str_word_count($t);
            if ($words >= 2 && $words <= 8 && mb_strlen($t) <= 60 && ! $this->isJunk($t) && ! preg_match('/[?$£€:]|\d{3,}/u', $t)) {
                $features[mb_strtolower($t)] = $t;
            }
            if (count($features) >= 14) {
                break;
            }
        }

        $body = mb_strtolower(Text::clean($xp->query('//body')->item(0)?->textContent ?? ''));
        $body = mb_substr($body.' '.mb_strtolower((string) $description), 0, 80000);

        $perks = [];
        foreach (self::PERKS as $label => $re) {
            if (preg_match($re, $body, $m)) {
                $perks[] = match ($label) {
                    'returns' => isset($m[1]) && $m[1] !== '' ? "{$m[1]}-day returns" : 'easy returns',
                    'guarantee' => isset($m[1]) && $m[1] !== '' ? "a {$m[1]}-day money-back guarantee" : 'a money-back guarantee',
                    'warranty' => isset($m[1]) && $m[1] !== '' ? "a {$m[1]}-year warranty" : 'a lifetime warranty',
                    default => $label,
                };
            }
        }
        $customers = preg_match('/\b(\d[\d,.]*\s*(?:k|m|million|thousand)?\+?)\s+(?:happy |satisfied |loyal )?(?:customers|clients|users|shoppers)\b/', $body, $m) ? trim($m[1]) : null;
        $since = preg_match('/\b(?:since|established in|est\.|founded in)\s+(19[5-9]\d|20[0-2]\d)\b/', $body, $m) ? $m[1] : null;

        return [
            'ok' => true,
            'url' => $url,
            'note' => null,
            'shop' => $shop,
            'tagline' => $tagline,
            'description' => $description,
            'sentences' => array_slice($sentences, 0, 6),
            'offering' => $this->offering(array_values(array_unique(array_filter([$description, ...$candidates, $ld['slogan'] ?? null, $tagline, ...$paragraphs]))), $shop),
            'features' => array_slice(array_values($features), 0, 10),
            'perks' => $perks,
            'customers' => $customers,
            'since' => $since,
            'product' => $ld['product'] ?? [],
        ];
    }

    /** What the shop sells, as a short noun phrase ("funny college dorm room flags"). */
    private function offering(array $texts, string $shop): ?string
    {
        $ok = fn (string $o) => ! str_contains($o, ':') && str_word_count($o) >= 2 && str_word_count($o) <= 14
            && ! preg_match('/\b(is|are|was|were|has|have|had|will|can|could|would|should|you|your|we|our|my|i|it|they|their|owner|story|now|today|here|there|more|most)\b/i', $o);
        $nounPhrase = function (string $t) use ($ok, $shop): ?string {
            // "Affordable web hosting, reseller hosting, VPS… in Nigeria." → a list of what they sell
            $t = rtrim(preg_replace('/\s+in\s+\p{Lu}[\p{L} ]*\.?$/u', '', $t), ' .');
            if (mb_strlen($t) <= 110 && ! str_contains($t, '. ') && ! preg_match('/^(the |a |an |#|welcome|home|best\b|official)/i', $t) && $ok($t)) {
                return self::tidyOffering(Text::soften($t, [$shop]), $shop);
            }

            return null;
        };
        // the shop's main description first: "we sell X" or a plain list of what it sells
        if ($first = Text::clean($texts[array_key_first($texts)] ?? '')) {
            if ($o = $this->offeringByVerb($first, $shop, $ok)) {
                return $o;
            }
            if ($o = $nounPhrase($first)) {
                return $o;
            }
        }
        foreach ($texts as $t) {
            if ($o = $this->offeringByVerb(Text::clean($t), $shop, $ok)) {
                return $o;
            }
        }
        foreach ($texts as $t) {
            if ($o = $nounPhrase(Text::clean($t))) {
                return $o;
            }
        }

        return null;
    }

    public static function tidyOffering(?string $o, string $shop): ?string
    {
        if ($o === null) {
            return null;
        }
        $base = preg_replace('/\.[a-z]{2,6}$/i', '', $shop);
        $o = trim(preg_replace('/^(?:'.preg_quote($shop, '/').'|'.preg_quote($base, '/').')(?:\'s|’s)?\s+/iu', '', $o));
        if (str_word_count($o) < 2) {
            return null;
        }
        if (preg_match('/^(impressive|wide|huge|great|large|vast|broad|full|curated|growing|unique)?\s*(variety|range|selection|collection|choice|line|lineup|assortment)\b/i', $o)) {
            $o = Text::an($o).' '.$o;
        }

        return $o;
    }

    private function offeringByVerb(string $t, string $shop, \Closure $ok): ?string
    {
        $verbs = 'sells?|offers?|provides?|makes?|creates?|designs?|crafts?|produces?|carr(?:y|ies)|stocks?|specializ(?:es|ing) in|specialis(?:es|ing) in|buy|shop(?: for)?|order';
        {
            if (preg_match('/\b(?:'.$verbs.')(?:\s+(?:and|&)\s+[a-z]+)?\s+(?:a |an |the |our |premium |top[- ]quality )?(.{6,90}?)(?=[.!?;]|,? (?:and more|online|for |in (?:\p{Lu}|the)|at |from |with |that |so |to )|$)/u', $t, $m)) {
                $o = trim($m[1], " ,.-");
                if ($ok($o) && ! preg_match('/\b(support|service|team|experience|help)$/i', $o) && ($o = self::tidyOffering(Text::soften($o, [$shop]), $shop))) {
                    return $o;
                }
            }
        }

        return null;
    }

    private function hasVerb(string $s): bool
    {
        return (bool) preg_match('/\b(is|are|was|were|be|been|has|have|had|can|will|lets|helps|gives|offers|sells|makes|provides|brings|creates|designs|ships|delivers|includes|comes|keeps|turns|combines|fuses|blends|takes|ensures|allows|won\'t|you\'ll|made|built|designed|crafted|printed|founded|trusted|loved|backed)\b/i', $s);
    }

    private function jsonLd(DOMXPath $xp): array
    {
        $out = [];
        foreach ($xp->query("//script[@type='application/ld+json']") as $s) {
            $data = json_decode(trim($s->textContent), true);
            if (! is_array($data)) {
                continue;
            }
            $items = isset($data['@graph']) ? $data['@graph'] : (array_is_list($data) ? $data : [$data]);
            foreach ($items as $it) {
                $type = (array) ($it['@type'] ?? []);
                if (array_intersect($type, ['Organization', 'Store', 'OnlineStore', 'Corporation', 'LocalBusiness', 'WebSite'])) {
                    $out['org'] ??= is_string($it['name'] ?? null) ? Text::clean($it['name']) : null;
                    $out['slogan'] ??= is_string($it['slogan'] ?? null) ? Text::clean($it['slogan']) : null;
                    $out['description'] ??= is_string($it['description'] ?? null) ? Text::clean($it['description']) : null;
                }
                if (in_array('Product', $type, true)) {
                    $offer = $it['offers'] ?? [];
                    $offer = array_is_list((array) $offer) ? ($offer[0] ?? []) : $offer;
                    $out['product'] = array_filter([
                        'name' => is_string($it['name'] ?? null) ? Text::clean($it['name']) : null,
                        'description' => is_string($it['description'] ?? null) ? Text::clean($it['description']) : null,
                        'price' => $offer['price'] ?? ($offer['lowPrice'] ?? null),
                        'currency' => $offer['priceCurrency'] ?? null,
                        'rating' => $it['aggregateRating']['ratingValue'] ?? null,
                        'reviews' => $it['aggregateRating']['reviewCount'] ?? ($it['aggregateRating']['ratingCount'] ?? null),
                    ]);
                    $out['description'] ??= $out['product']['description'] ?? null;
                }
            }
        }

        return $out;
    }

    private function isJunk(string $t): bool
    {
        return (bool) preg_match('/cookie|privacy|javascript|all rights reserved|©|copyright|newsletter|subscribe|sign up|sign in|log ?in|password|your cart|add to cart|checkout now|captcha|enable js|browser|terms (of|&)|accept all|skip to|menu|lorem ipsum|^home\.?$|^shop now|^learn more|^read more|^view all|^contact|^about us|^faq/i', $t);
    }

    private function empty(string $url, string $note): array
    {
        return ['ok' => false, 'url' => $url, 'note' => $note, 'shop' => null, 'tagline' => null, 'description' => null, 'sentences' => [],
            'offering' => null, 'features' => [], 'perks' => [], 'customers' => null, 'since' => null, 'product' => []];
    }
}
