<?php

namespace App\Support\Describer;

use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Rule-based copywriter. Builds several candidate descriptions from the facts,
 * scores each one (length, repetition, coverage, how different it is from your
 * other descriptions) and returns the best.
 */
class Writer
{
    private const TARGET = ['short' => [22, 50], 'medium' => [60, 115], 'long' => [110, 200]];

    private Randomizer $rng;

    private array $f;

    /** @param string[] $others existing descriptions to stay different from */
    public function write(array $facts, int $seed, array $others = [], int $candidates = 8): string
    {
        $best = null;
        $bestScore = -INF;
        for ($i = 0; $i < $candidates; $i++) {
            $this->rng = new Randomizer(new Mt19937($seed + $i * 7919));
            $this->f = $facts;
            $text = $this->render($this->compose());
            $score = $this->score($text, $others);
            if ($score > $bestScore) {
                [$best, $bestScore] = [$text, $score];
            }
        }

        return $best ?? '';
    }

    // ---------------------------------------------------------------- composition

    /** @return array{paras: array<int, string[]>, bullets: string[], heading: ?string} */
    private function compose(): array
    {
        return match ($this->f['type']) {
            'store' => $this->composeStore(),
            'coupon' => $this->composeCoupon(),
            'deal' => $this->composeDeal(),
            default => $this->composeProduct(),
        };
    }

    private function composeProduct(): array
    {
        $len = $this->f['length'];
        $open = $this->pick(array_merge(
            $this->custom('openings'),
            $this->f['angle'] && $this->f['audience'] ? [
                '{subject} is a great fit for {angle}.',
                'Made with {angle} in mind, {subject} keeps things simple from day one.',
                'If you\'re shopping for {angle}, {subject} is one of the easiest picks to recommend.',
                '{subject} is our top pick for {angle}, and for good reason.',
                'Few options suit {angle} as well as {subject}.',
            ] : [],
            $this->f['angle'] && ! $this->f['audience'] ? [
                'Looking for {angle}? {subject} is one of the strongest options we\'ve found.',
                '{subject} is a standout choice for {angle}.',
                'If you\'re after {angle}, {subject} deserves a closer look.',
                'When it comes to {angle}, {subject} gets a lot right.',
                'Need {angle}? {subject} makes the whole thing refreshingly simple.',
                '{subject} has earned its spot as a go-to option for {angle}.',
                'For {angle}, few options are as easy to recommend as {subject}.',
            ] : [],
            ! $this->f['angle'] && $this->f['offering'] ? [
                'Shopping for {offering}? {subject} is a name worth knowing.',
                '{subject} is a smart pick for anyone shopping for {offering}.',
                'If you\'re in the market for {offering}, start with {subject}.',
            ] : [],
            ! $this->f['angle'] && ! $this->f['offering'] && $this->f['category'] ? [
                '{subject} is one of our favourite picks in {category}.',
                '{subject} earns a place among our top {category} picks.',
            ] : [],
            // generic openings only when nothing more specific is known
            ! $this->f['angle'] && ! $this->f['offering'] && ! $this->f['category'] ? [
                '{subject} is one of our current top picks, and it\'s easy to see why.',
                '{subject} has quickly become one of our favourite recommendations.',
            ] : [],
        ));

        $about = $this->about();
        if (! $about && $this->f['angle']) {
            $about = [$this->pick($this->f['audience'] ? [
                'It covers the essentials {angle} care about most, without unnecessary extras.',
                'We picked it because it suits {angle} better than most of the alternatives we compared.',
            ] : [
                'We picked it because it focuses on exactly what matters when you need {angle}.',
                'It\'s worth comparing with the other options on this list, but for {angle} it\'s hard to beat.',
                'It keeps the focus on {angle} and does that job particularly well.',
            ])];
        }
        $perks = $this->perks();
        $facts = $this->productFacts();
        $proof = $this->proof();
        $cta = $this->cta();

        if ($len === 'short') {
            return ['paras' => [array_filter([$open, $about[0] ?? $cta])], 'bullets' => [], 'heading' => null];
        }
        if ($len === 'medium') {
            $p1 = array_filter([$open, $about[0] ?? null, $about[1] ?? null]);
            $p2 = array_filter([$perks, $facts[0] ?? null, $cta]);

            return ['paras' => array_values(array_filter([$p1, $p2])), 'bullets' => [], 'heading' => null];
        }

        $bullets = $this->bullets();

        return [
            'paras' => array_values(array_filter([
                array_filter([$open, ...$about]),
                array_filter([$perks, $proof, ...$facts]),
                [$cta],
            ])),
            'bullets' => $bullets,
            'heading' => $bullets ? $this->pick(['Why we like it:', 'What stands out:', 'Reasons to choose {subject}:', 'At a glance:']) : null,
        ];
    }

    private function composeStore(): array
    {
        $len = $this->f['length'];
        $open = $this->pick(array_merge(
            $this->custom('openings'),
            $this->f['offering'] ? [
                '{subject} specialises in {offering}.',
                '{subject} is your go-to destination for {offering}.',
                'Looking for {offering}? {subject} has you covered.',
                'Shoppers turn to {subject} for {offering}, and it\'s easy to see why.',
            ] : [
                '{subject} is one of the stores we keep coming back to.',
                '{subject} has built a loyal following, and it\'s easy to see why.',
            ],
        ));
        $usedOffering = (bool) $this->f['offering'];
        $about = $this->about(skipOffering: $usedOffering);
        $perks = $this->perks();
        $proof = $this->proof();
        $cats = $this->fill($this->pick([
            'You\'ll find {subject} in our {categories} section.',
            '{subject} sits in our {categories} collection.',
        ]));
        $save = $this->fill($this->pick([
            'Browse the latest {subject} coupon codes and deals below to save on your next order.',
            'Check the verified {subject} discount codes on this page before you check out.',
            'Before you buy, grab one of the {subject} promo codes and deals listed below.',
        ]));

        if ($len === 'short') {
            return ['paras' => [array_filter([$open, $save])], 'bullets' => [], 'heading' => null];
        }
        if ($len === 'medium') {
            return ['paras' => array_values(array_filter([array_filter([$open, $about[0] ?? null]), array_filter([$perks, $save])])), 'bullets' => [], 'heading' => null];
        }
        $bullets = $this->bullets();

        return [
            'paras' => array_values(array_filter([array_filter([$open, ...$about]), array_filter([$perks, $proof, $cats]), [$save]])),
            'bullets' => $bullets,
            'heading' => $bullets ? $this->pick(['Why shoppers like {subject}:', 'What stands out:', 'At a glance:']) : null,
        ];
    }

    private function composeCoupon(): array
    {
        $adj = trim(($this->f['verified'] ? 'verified ' : '').($this->f['exclusive'] ? 'exclusive ' : ''));
        $this->f['adj'] = $adj !== '' ? $adj.' ' : '';
        $open = $this->f['discount'] ? $this->pick([
            'Save {discount} at {store} with this {adj}coupon.',
            'Use this {adj}{store} coupon to get {discount} on your order.',
            'Here\'s a {adj}way to get {discount} at {store}.',
            'Get {discount} at {store} with this {adj}deal.',
        ]) : $this->pick([
            'Here\'s a {adj}{store} offer worth grabbing: {title_soft}.',
            '{store} is running a {adj}offer right now: {title_soft}.',
        ]);
        $code = $this->f['code'] ? $this->pick([
            'Just enter {code} at checkout to unlock the discount.',
            'Copy the code {code} and paste it at checkout.',
            'Apply {code} at checkout and watch the price drop.',
        ]) : $this->pick([
            'No code needed: the discount is applied automatically when you shop through our link.',
            'There\'s no code to remember, as the saving is applied for you at checkout.',
        ]);
        $expires = $this->fill($this->pick([
            'It expires on {expires}, so use it while you can.',
            'Valid until {expires}.',
            'Don\'t wait too long, as this one ends on {expires}.',
        ]));
        $about = $this->f['length'] !== 'short' ? ($this->about()[0] ?? null) : null;
        $perks = $this->f['length'] === 'long' ? $this->perks() : null;

        return ['paras' => [array_filter([$this->fill($open), $this->fill($code), $expires, $about, $perks])], 'bullets' => [], 'heading' => null];
    }

    private function composeDeal(): array
    {
        $open = $this->pick(array_merge(
            $this->f['price'] && $this->f['oldPrice'] ? [
                'Grab {title_soft} for just {price}, down from {oldPrice}. That\'s {saving} off.',
                '{title} is down to {price} (was {oldPrice}), a saving of {saving}.',
                'Price drop alert: {title_soft} is now {price}, down from {oldPrice}.',
            ] : [],
            $this->f['price'] && ! $this->f['oldPrice'] ? ['{title} is now available for just {price}.', 'Get {title_soft} for {price} while the deal lasts.'] : [],
            ['{title} is one of the hottest deals at {store} right now.', 'Don\'t miss this one: {title_soft} at {store}.'],
        ));
        $expires = $this->fill($this->pick(['This deal ends on {expires}, so don\'t wait too long.', 'Hurry, the offer runs until {expires}.']));
        $about = $this->f['length'] !== 'short' ? ($this->about()[0] ?? null) : null;
        $perks = $this->f['length'] === 'long' ? $this->perks() : null;
        $cta = $this->fill($this->pick(['Tap through to grab it at {shop} before it\'s gone.', 'Head to {shop} to claim the deal.', 'Check it out on {shop} while stock lasts.']));

        return ['paras' => [array_filter([$open, $about, $perks, $expires, $cta])], 'bullets' => [], 'heading' => null];
    }

    // ---------------------------------------------------------------- building blocks

    /** what the shop is about: an offering line and/or one rewritten line from the shop itself */
    private function about(bool $skipOffering = false): array
    {
        $out = [];
        if ($this->f['offering'] && ! $skipOffering && mb_strtolower((string) $this->f['offering']) !== mb_strtolower((string) $this->f['angle'])) {
            $out[] = $this->fill($this->pick([
                '{shop} specialises in {offering}.',
                '{shop} focuses on {offering}, and it shows.',
                'At {shop}, you\'ll find {offering}.',
                '{shop} is known for {offering}.',
                'The team at {shop} is all about {offering}.',
            ]));
        }
        $pool = $this->f['shopSentences'];
        $this->rng->shuffleArray($pool);
        foreach ($pool as $s) {
            if (count($out) >= 2) {
                break;
            }
            $s = $this->thirdPerson($s);
            $known = array_merge(Text::contentWords(implode(' ', $out)), Text::contentWords((string) $this->f['offering']));
            $new = array_diff(array_unique(Text::contentWords((string) $s)), $known);
            if ($s && count($new) >= 4 && ! array_filter($out, fn ($o) => Text::similarity($o, $s) > 0.25)) {
                $out[] = $s;
            }
        }
        if (! $out && $this->f['tagline'] && preg_match('/^(the|a|an|#1|no\.? ?1)\b/iu', $this->f['tagline'])) {
            $out[] = $this->fill('{shop} bills itself as {tagline_soft}.');
        }

        return array_values(array_filter($out));
    }

    private function perks(): ?string
    {
        $perks = $this->f['perks'];
        if (! $perks) {
            return null;
        }
        $this->rng->shuffleArray($perks);
        $this->f['perkList'] = Text::listJoin(array_slice($perks, 0, 3));

        return $this->fill($this->pick([
            'Shoppers also get {perkList}.',
            'Extras like {perkList} make the decision even easier.',
            'You can also count on {perkList}.',
            'On top of that, {shop} offers {perkList}.',
            'Better still, you get {perkList}.',
        ]));
    }

    private function proof(): ?string
    {
        return $this->fill($this->pick(array_merge(
            $this->f['customers'] && $this->f['since'] ? ['With {customers} happy customers and roots going back to {since}, {shop} knows what it\'s doing.'] : [],
            $this->f['customers'] ? ['Trusted by {customers} customers, {shop} has built a solid reputation.', 'More than {customers} customers already shop with {shop}.'] : [],
            $this->f['since'] ? ['{shop} has been doing this since {since}, so you\'re in experienced hands.', 'Around since {since}, {shop} has had plenty of time to get things right.'] : [],
            [null],
        )));
    }

    private function productFacts(): array
    {
        $out = [];
        if ($this->f['highlights']) {
            $this->f['highlightList'] = Text::listJoin(array_map(fn ($h) => Text::lcfirstSoft($h), array_slice($this->f['highlights'], 0, 3)));
            $out[] = $this->fill($this->pick(['Highlights include {highlightList}.', 'Standout features include {highlightList}.', 'We especially like the {highlightList}.']));
        }
        if ($this->f['rating']) {
            $this->f['reviewsPart'] = $this->f['reviews'] ? ', backed by '.$this->f['reviews'].' customer reviews' : '';
            $out[] = $this->fill($this->pick(['We rate it {rating}{reviewsPart}.', 'It scores {rating} in our review{reviewsPart}.']));
        }
        if ($this->f['price']) {
            $out[] = $this->fill($this->pick(['Prices start from {price}.', 'It\'s currently listed at {price}.', 'At {price}, it offers solid value for what you get.']));
        }
        $this->rng->shuffleArray($out);

        return array_slice(array_values(array_filter($out)), 0, $this->f['length'] === 'long' ? 3 : 1);
    }

    private function cta(): string
    {
        return (string) $this->fill($this->pick(array_merge($this->custom('ctas'), [
            'Tap through to check today\'s price on the {shop} website.',
            'Head over to {shop} to see the latest prices and options.',
            'Visit {shop} to explore the full range and find the right fit.',
            'Check current prices and availability on {shop}.',
            'Ready to try it? See the full details on {shop}.',
        ])));
    }

    private function bullets(): array
    {
        $items = [];
        foreach ($this->f['highlights'] as $h) {
            $items[] = Text::ucfirst(Text::clean($h));
        }
        $features = $this->f['features'];
        $this->rng->shuffleArray($features);
        foreach ($features as $feat) {
            $items[] = Text::ucfirst(Text::soften($feat, [$this->f['shop'], $this->f['subject']]));
        }
        foreach ($this->f['perks'] as $p) {
            $items[] = Text::ucfirst(preg_replace('/^(a|an) /', '', $p));
        }
        $out = [];
        foreach ($items as $it) {
            $it = rtrim($it, '.');
            if ($it !== '' && ! array_filter($out, fn ($o) => Text::similarity($o.' x y', $it.' x y') > 0.3 || mb_strtolower($o) === mb_strtolower($it))) {
                $out[] = $it;
            }
            if (count($out) >= 5) {
                break;
            }
        }

        return count($out) >= 3 ? $out : [];
    }

    /** turn the shop's own "we / our / buy now" wording into a neutral third-person line */
    private function thirdPerson(string $s): ?string
    {
        $shop = (string) $this->f['shop'];
        $s = Text::clean($s);
        if (preg_match('/^(welcome|home|click|join|shop now|sign|log|copyright|call us|email us|contact)/i', $s) || str_word_count($s) < 6) {
            return null;
        }
        $s = preg_replace('/^we\s*(?:are|\'re|’re)\b/i', $shop.' is', $s);
        $s = preg_replace_callback('/^we\s+([a-z]+)(?:(\s+(?:and|&)\s+)([a-z]+))?\b/i', fn ($m) => $shop.' '.Text::thirdPerson($m[1]).(isset($m[3]) ? $m[2].Text::thirdPerson($m[3]) : ''), $s);
        $s = preg_replace('/^our\b/i', 'Its', $s);
        $s = preg_replace('/^(buy|shop|discover|explore|find|get|order|browse|choose|enjoy|create|design|experience|elevate|save on|upgrade)\b/i', 'Here you can $1', $s, 1, $imperative);
        if ($imperative) {
            $s = preg_replace_callback('/^Here you can (\w)/u', fn ($m) => 'Here you can '.mb_strtolower($m[1]), $s);
        }
        $s = preg_replace(['/\bour\b/i', '/\bwe(\'|’)ve\b/i', '/\bwe(\'|’)ll\b/i', '/\bwe(\'|’)re\b/i', '/\bwe\b/i', '/\bus\b/'], ['its', 'they have', 'they will', 'they are', 'they', 'the team'], $s);
        $s = preg_replace('/\s+/', ' ', $s);

        return Text::end(Text::ucfirst(Text::limit($s, 220)));
    }

    // ---------------------------------------------------------------- helpers

    private function custom(string $which): array
    {
        return $this->f['custom'][$which] ?? [];
    }

    private function pick(array $options): ?string
    {
        $usable = array_values(array_filter($options, fn ($o) => $o === null || $this->fill($o) !== null));
        if (! $usable) {
            return null;
        }
        $choice = $usable[$this->rng->getInt(0, count($usable) - 1)];

        return $choice === null ? null : $this->fill($choice);
    }

    /** fill {placeholders}; null when a needed fact is missing */
    private function fill(?string $tpl): ?string
    {
        if ($tpl === null) {
            return null;
        }
        $missing = false;
        $out = preg_replace_callback('/\{(\w+)\}/', function ($m) use (&$missing) {
            $v = $this->f[$m[1]] ?? null;
            if ($v === null || $v === '' || $v === []) {
                $missing = true;

                return '';
            }

            return is_array($v) ? Text::listJoin($v) : (string) $v;
        }, $tpl);

        if ($missing) {
            return null;
        }
        // "a eco-friendly" → "an eco-friendly"
        $out = preg_replace_callback('/\b(a|an) ([\p{L}0-9#"“][\p{L}0-9\'’-]*)/u', fn ($m) => (ctype_upper($m[1][0]) ? Text::ucfirst(Text::an($m[2])) : Text::an($m[2])).' '.$m[2], $out);

        return Text::ucfirst(trim(preg_replace('/\s+/', ' ', $out)));
    }

    private function render(array $c): string
    {
        $html = $this->f['html'];
        $paras = array_values(array_filter(array_map(fn ($p) => trim(implode(' ', array_map(fn ($s) => Text::end((string) $s), array_filter($p)))), $c['paras'])));
        // keep the call to action last when there are bullets
        $last = $c['bullets'] && count($paras) > 1 ? array_pop($paras) : null;

        $out = [];
        foreach ($paras as $p) {
            $out[] = $html ? '<p>'.e($p).'</p>' : $p;
        }
        if ($c['bullets']) {
            $heading = $this->fill($c['heading']);
            if ($html) {
                $out[] = ($heading ? '<p><strong>'.e($heading).'</strong></p>' : '').'<ul>'.implode('', array_map(fn ($b) => '<li>'.e($b).'</li>', $c['bullets'])).'</ul>';
            } else {
                $out[] = ($heading ? $heading."\n" : '').implode("\n", array_map(fn ($b) => '• '.$b, $c['bullets']));
            }
        }
        if ($last) {
            $out[] = $html ? '<p>'.e($last).'</p>' : $last;
        }

        return implode($html ? '' : "\n\n", $out);
    }

    private function score(string $text, array $others): float
    {
        $plain = Text::clean(str_replace(['</p>', '</li>'], ' ', $text));
        $words = str_word_count($plain);
        [$min, $max] = self::TARGET[$this->f['length']] ?? self::TARGET['medium'];
        $score = 0.0;
        $score -= $words < $min ? ($min - $words) * 0.6 : 0;
        $score -= $words > $max ? ($words - $max) * 0.4 : 0;

        // repeated words read badly
        foreach (array_count_values(Text::contentWords($plain)) as $w => $n) {
            $allowed = in_array($w, Text::words((string) $this->f['subject'].' '.$this->f['shop']), true) ? 3 : 2;
            $score -= max(0, $n - $allowed) * 2;
        }
        // varied sentence openings
        $starts = array_map(fn ($s) => mb_strtolower(strtok($s, ' ') ?: ''), Text::sentences($plain));
        $score -= (count($starts) - count(array_unique($starts))) * 1.5;

        // coverage of the useful facts
        foreach (['angle', 'offering', 'shop'] as $k) {
            if ($this->f[$k] && mb_stripos($plain, (string) $this->f[$k]) !== false) {
                $score += 2;
            }
        }
        foreach ($this->f['perks'] as $p) {
            $score += mb_stripos($plain, $p) !== false ? 0.5 : 0;
        }

        // stay different from the descriptions you already have
        $max = 0.0;
        foreach ($others as $o) {
            $max = max($max, Text::similarity($plain, Text::clean((string) $o)));
        }
        $score -= $max > 0.2 ? ($max - 0.2) * 40 : 0;

        return $score + $this->rng->getFloat(0, 0.5);
    }
}
