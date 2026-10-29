<?php

namespace App\Support\Describer;

/** Small language helpers used by the description writer. */
final class Text
{
    public const STOP = ['the', 'and', 'for', 'with', 'you', 'your', 'are', 'this', 'that', 'from', 'its', 'our', 'their', 'all', 'can',
        'has', 'have', 'into', 'more', 'most', 'just', 'than', 'them', 'they', 'will', 'what', 'when', 'who', 'why', 'how', 'out', 'any',
        'but', 'not', 'one', 'also', 'get', 'it\'s', 'it’s', 'on', 'of', 'to', 'in', 'a', 'an', 'is', 'or', 'at', 'by', 'be', 'as', 'it', 'we'];

    public static function clean(?string $s): string
    {
        $s = html_entity_decode(strip_tags((string) $s), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $s = preg_replace('/\s+/u', ' ', $s);
        $s = preg_replace('/\s+([,.!?;:])/u', '$1', $s);

        return trim((string) $s, " \t\n\r\0\x0B-–—|·•");
    }

    /** @return string[] */
    public static function sentences(?string $s): array
    {
        $s = self::clean($s);
        if ($s === '') {
            return [];
        }
        $parts = preg_split('/(?<=[.!?])\s+(?=[\p{Lu}0-9“"#])/u', $s) ?: [];

        return array_values(array_filter(array_map('trim', $parts), fn ($p) => $p !== ''));
    }

    public static function end(string $s): string
    {
        $s = rtrim(trim($s), ' ,;:-–—');

        return $s === '' || preg_match('/[.!?]$/u', $s) ? $s : $s.'.';
    }

    public static function ucfirst(string $s): string
    {
        return mb_strtoupper(mb_substr($s, 0, 1)).mb_substr($s, 1);
    }

    /**
     * "Custom Kraft Boxes" → "custom kraft boxes", but keeps brands, acronyms and
     * words with inner capitals ("SUNLU", "3D", "WordPress", "iPhone") as they are.
     */
    public static function soften(string $s, array $keep = []): string
    {
        $keepLower = array_map(fn ($k) => mb_strtolower($k), array_filter($keep));

        return preg_replace_callback('/[\p{L}0-9][\p{L}0-9\'’.&+-]*/u', function ($m) use ($keepLower) {
            $w = $m[0];
            if (in_array(mb_strtolower($w), $keepLower, true)) {
                return $w;
            }
            if (preg_match('/\d/', $w) || mb_strtoupper($w) === $w && mb_strlen($w) > 1 || preg_match('/.\p{Lu}/u', $w)) {
                return $w;
            }

            return mb_strtolower($w);
        }, $s);
    }

    public static function lcfirstSoft(string $s): string
    {
        // lower the first letter unless the first word looks like a name or acronym
        if (preg_match('/^([\p{L}0-9\'’]+)/u', $s, $m)) {
            $w = $m[1];
            if (mb_strtoupper($w) === $w || preg_match('/.\p{Lu}/u', $w)) {
                return $s;
            }
        }

        return mb_strtolower(mb_substr($s, 0, 1)).mb_substr($s, 1);
    }

    public static function listJoin(array $items, string $and = 'and'): string
    {
        $items = array_values(array_filter(array_map('trim', $items), fn ($i) => $i !== ''));

        return match (count($items)) {
            0 => '',
            1 => $items[0],
            2 => $items[0].' '.$and.' '.$items[1],
            default => implode(', ', array_slice($items, 0, -1)).' '.$and.' '.end($items),
        };
    }

    public static function an(string $next): string
    {
        $w = mb_strtolower(ltrim($next, '"“\''));
        if (preg_match('/^(uni|use|usu|eu|one|once|ur)/', $w)) {
            return 'a';
        }
        if (preg_match('/^(hour|honest|honou?r|heir)/', $w) || preg_match('/^(8|11|18)(?!\d)|^8\d/', $w) || preg_match('/^[aeiou]/', $w)) {
            return 'an';
        }

        return 'a';
    }

    /** third-person form of a verb: sell → sells, carry → carries, ship → ships */
    public static function thirdPerson(string $verb): string
    {
        $v = mb_strtolower($verb);
        $irregular = ['have' => 'has', 'do' => 'does', 'go' => 'goes', 'be' => 'is', 'are' => 'is'];
        if (isset($irregular[$v])) {
            return $irregular[$v];
        }
        if (preg_match('/[^aeiou]y$/', $v)) {
            return substr($v, 0, -1).'ies';
        }
        if (preg_match('/(s|sh|ch|x|z|o)$/', $v)) {
            return $v.'es';
        }

        return $v.'s';
    }

    /** @return string[] lowercase content words */
    public static function words(string $s): array
    {
        preg_match_all('/[\p{L}0-9][\p{L}0-9\'’-]*/u', mb_strtolower(strip_tags($s)), $m);

        return $m[0];
    }

    public static function contentWords(string $s): array
    {
        return array_values(array_filter(self::words($s), fn ($w) => mb_strlen($w) > 3 && ! in_array($w, self::STOP, true)));
    }

    /** 0..1 overlap of 3-word sequences: how alike two texts read */
    public static function similarity(string $a, string $b): float
    {
        $grams = function (string $s) {
            $w = self::words($s);
            $g = [];
            for ($i = 0; $i + 2 < count($w); $i++) {
                $g[$w[$i].' '.$w[$i + 1].' '.$w[$i + 2]] = true;
            }

            return $g;
        };
        $ga = $grams($a);
        $gb = $grams($b);
        if (! $ga || ! $gb) {
            return 0.0;
        }

        return count(array_intersect_key($ga, $gb)) / count($ga + $gb);
    }

    /** share of words that start with a capital letter: high = title-like fragment, not a sentence */
    public static function capitalRatio(string $s): float
    {
        preg_match_all('/[\p{L}][\p{L}\'’-]*/u', $s, $m);
        $words = array_filter($m[0], fn ($w) => mb_strlen($w) > 3);
        if (! $words) {
            return 0.0;
        }

        return count(array_filter($words, fn ($w) => preg_match('/^\p{Lu}/u', $w))) / count($words);
    }

    public static function limit(string $s, int $chars): string
    {
        if (mb_strlen($s) <= $chars) {
            return $s;
        }
        $cut = mb_substr($s, 0, $chars);

        return rtrim(mb_substr($cut, 0, (int) mb_strrpos($cut, ' ')), ' ,;:-').'…';
    }
}
