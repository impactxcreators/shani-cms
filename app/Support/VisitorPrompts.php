<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Settings for the cookie banner and the deal-alert (push) card, with their defaults.
 * Edited in Settings → Banners & Pop-ups; read by partials/visitor-prompts.
 */
class VisitorPrompts
{
    /** key => [type, default] */
    public const FIELDS = [
        // cookie banner
        'cookie_banner_enabled' => ['bool', true],
        'cookie_banner_text' => ['text', 'We use essential cookies to run this site. With your OK we also use cookies to understand visits and improve our deals.'],
        'cookie_banner_delay' => ['string', '1'],
        'cookie_banner_position' => ['string', 'left'],
        'cookie_accept_label' => ['string', 'Accept'],
        'cookie_reject_label' => ['string', 'Essential only'],
        'cookie_show_reject' => ['bool', true],
        'cookie_consent_days' => ['string', '365'],
        'cookie_privacy_url' => ['string', '/privacy-policy'],
        'consent_scripts' => ['text', ''],
        // deal alerts (push)
        'push_enabled' => ['bool', true],
        'push_prompt_title' => ['string', 'Get deal alerts?'],
        'push_prompt_text' => ['text', 'Only the best coupons and price drops, a few times a week at most. Turn off anytime.'],
        'push_yes_label' => ['string', 'Yes, notify me'],
        'push_no_label' => ['string', 'Not now'],
        'push_welcome_text' => ['string', 'You’re in! We’ll only alert you about top deals.'],
        'push_prompt_delay' => ['string', '20'],
        'push_prompt_min_pages' => ['string', '2'],
        'push_prompt_scroll' => ['string', '0'],
        'push_prompt_position' => ['string', 'right'],
        'push_show_on_mobile' => ['bool', true],
        'push_snooze_days' => ['string', '14'],
        'push_denied_days' => ['string', '90'],
        'push_exclude_paths' => ['text', ''],
        'push_footer_link' => ['bool', true],
    ];

    public static function get(string $key)
    {
        return Setting::get($key, self::FIELDS[$key][1] ?? null);
    }

    public static function int(string $key, int $min, int $max): int
    {
        return max($min, min($max, (int) self::get($key)));
    }

    /** true when the current page is on the "don't ask on these pages" list */
    public static function pushExcludedHere(): bool
    {
        $path = '/'.ltrim(request()->path(), '/');
        foreach (preg_split('/\r\n|\r|\n/', (string) self::get('push_exclude_paths')) as $pattern) {
            $pattern = trim($pattern);
            if ($pattern !== '' && Str::is('/'.ltrim($pattern, '/'), $path)) {
                return true;
            }
        }

        return false;
    }
}
