{{-- Cookie banner + deal-alert (push) card. Both are small, never cover the page,
     never show at the same time, and remember the visitor's choice.
     Settings: admin → Settings → Banners & Pop-ups (App\Support\VisitorPrompts). --}}
@php
    use App\Support\VisitorPrompts as VP;
    $vpCookieOn = (bool) VP::get('cookie_banner_enabled');
    $vpPushKey = (string) config('cms.push.public_key');
    $vpPushOn = (bool) VP::get('push_enabled') && $vpPushKey !== '';
    $vpPushCard = $vpPushOn && ! VP::pushExcludedHere();
    $vpScripts = trim((string) VP::get('consent_scripts'));
    $vpCookiePos = in_array(VP::get('cookie_banner_position'), ['left', 'right', 'bar']) ? VP::get('cookie_banner_position') : 'left';
    $vpPushPos = VP::get('push_prompt_position') === 'left' ? 'left' : 'right';
    $vpPrivacy = trim((string) VP::get('cookie_privacy_url'));
    $vpConfig = [
        'cookieDelay' => VP::int('cookie_banner_delay', 0, 60) * 1000,
        'consentDays' => VP::int('cookie_consent_days', 1, 730),
        'pushKey' => $vpPushOn ? $vpPushKey : '',
        'pushDelay' => VP::int('push_prompt_delay', 0, 600) * 1000,
        'minPages' => VP::int('push_prompt_min_pages', 1, 20),
        'scroll' => VP::int('push_prompt_scroll', 0, 100),
        'mobile' => (bool) VP::get('push_show_on_mobile'),
        'snoozeDays' => VP::int('push_snooze_days', 1, 365),
        'deniedDays' => VP::int('push_denied_days', 1, 730),
        'welcome' => (string) VP::get('push_welcome_text'),
    ];
@endphp
<style>
    .vp{position:fixed;z-index:60;bottom:16px;max-width:400px;background:var(--card);color:var(--fg);border:1px solid var(--line);
        border-radius:14px;box-shadow:var(--shadow);padding:14px 16px;font-size:.88rem;line-height:1.5;
        opacity:0;transform:translateY(12px);transition:opacity .25s ease,transform .25s ease}
    .vp.in{opacity:1;transform:none}
    .vp[hidden]{display:none}
    .vp p{margin:0 0 10px;color:var(--muted)}
    .vp p a{color:var(--brand);text-decoration:underline}
    .vp-left{left:16px}
    .vp-right{right:16px}
    .vp-bar{left:0;right:0;bottom:0;max-width:none;border-radius:0;border-width:1px 0 0;display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;padding:12px 20px}
    .vp-bar p{margin:0;max-width:760px}
    .vp-push{display:flex;gap:12px;padding-right:34px}
    .vp-push strong{display:block;margin-bottom:2px}
    .vp-ico{font-size:1.4rem;line-height:1}
    .vp-actions{display:flex;gap:8px;flex-wrap:wrap}
    .vp-btn{font:inherit;font-weight:600;font-size:.85rem;border-radius:9px;padding:7px 14px;cursor:pointer;
            border:1px solid var(--brand);background:var(--brand);color:var(--brand-ink)}
    .vp-btn.ghost{background:transparent;color:var(--fg);border-color:var(--line)}
    .vp-btn:focus-visible,.vp-x:focus-visible{outline:2px solid var(--brand);outline-offset:2px}
    .vp-x{position:absolute;top:6px;right:8px;background:none;border:0;color:var(--muted);font-size:1.2rem;cursor:pointer;line-height:1;padding:4px}
    .vp-toast{right:16px;max-width:320px}
    @media (max-width:560px){.vp{left:10px;right:10px;bottom:10px;max-width:none}.vp-bar{left:0;right:0;bottom:0}}
    @media (prefers-reduced-motion:reduce){.vp{transition:none}}
</style>

@if($vpCookieOn)
<div id="vp-cookie" class="vp vp-{{ $vpCookiePos }}" role="region" aria-label="Cookie notice" hidden>
    <p>{{ VP::get('cookie_banner_text') }}
        @if($vpPrivacy !== '')<a href="{{ str_starts_with($vpPrivacy, 'http') ? $vpPrivacy : url($vpPrivacy) }}">Privacy policy</a>@endif
    </p>
    <div class="vp-actions">
        @if(VP::get('cookie_show_reject'))
            <button type="button" class="vp-btn ghost" data-consent="essential">{{ VP::get('cookie_reject_label') }}</button>
        @endif
        <button type="button" class="vp-btn" data-consent="all">{{ VP::get('cookie_accept_label') }}</button>
    </div>
</div>
@endif

@if($vpPushOn)
<div id="vp-push" class="vp vp-push vp-{{ $vpPushPos }}" role="dialog" aria-labelledby="vp-push-title" data-auto="{{ $vpPushCard ? '1' : '0' }}" hidden>
    <button type="button" class="vp-x" data-push="later" aria-label="Close">&times;</button>
    <div class="vp-ico" aria-hidden="true">&#128276;</div>
    <div>
        <strong id="vp-push-title">{{ VP::get('push_prompt_title') }}</strong>
        <p>{{ VP::get('push_prompt_text') }}</p>
        <div class="vp-actions">
            <button type="button" class="vp-btn ghost" data-push="later">{{ VP::get('push_no_label') }}</button>
            <button type="button" class="vp-btn" data-push="yes">{{ VP::get('push_yes_label') }}</button>
        </div>
    </div>
</div>
@endif

<div id="vp-toast" class="vp vp-toast" role="status" aria-live="polite" hidden></div>

@if($vpScripts !== '')
<template id="vp-consent-scripts">{!! $vpScripts !!}</template>
@endif

<script>
(function () {
    var C = @json($vpConfig);
    var store = {
        get: function (k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
        set: function (k, v) { try { localStorage.setItem(k, v); } catch (e) {} },
        del: function (k) { try { localStorage.removeItem(k); } catch (e) {} }
    };
    var DAY = 86400000;

    // ?vp-reset=1 forgets this browser's choices so the site owner can see the banners again
    if (/[?&]vp-reset=1\b/.test(location.search)) {
        ['vp_views', 'vp_push', 'vp_push_snooze'].forEach(store.del);
        document.cookie = 'cookie_consent=; max-age=0; path=/';
        history.replaceState(null, '', location.pathname + location.search.replace(/[?&]vp-reset=1\b/, '').replace(/^&/, '?') + location.hash);
    }

    var cookieBar = document.getElementById('vp-cookie');
    var pushCard = document.getElementById('vp-push');
    var toastEl = document.getElementById('vp-toast');

    function show(el) { if (!el) return; el.hidden = false; requestAnimationFrame(function () { el.classList.add('in'); }); }
    function hide(el) { if (!el) return; el.classList.remove('in'); setTimeout(function () { el.hidden = true; }, 260); }
    function toast(msg) { if (!msg) return; toastEl.textContent = msg; show(toastEl); setTimeout(function () { hide(toastEl); }, 4000); }

    /* ---------- cookies ---------- */
    function consent() { var m = document.cookie.match(/(?:^|; )cookie_consent=([^;]*)/); return m ? m[1] : null; }
    function setConsent(v) { document.cookie = 'cookie_consent=' + v + '; max-age=' + (C.consentDays * 86400) + '; path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : ''); }
    function loadConsentScripts() {
        var t = document.getElementById('vp-consent-scripts');
        if (!t || t.dataset.loaded) return;
        t.dataset.loaded = '1';
        var frag = t.content.cloneNode(true);
        frag.querySelectorAll('script').forEach(function (old) {
            var s = document.createElement('script');
            for (var i = 0; i < old.attributes.length; i++) s.setAttribute(old.attributes[i].name, old.attributes[i].value);
            s.text = old.textContent;
            old.replaceWith(s);
        });
        document.body.appendChild(frag);
    }
    function cookieBannerPending() { return !!cookieBar && !consent(); }

    if (consent() === 'all') loadConsentScripts();
    if (cookieBannerPending()) setTimeout(function () { if (cookieBannerPending()) show(cookieBar); }, C.cookieDelay);
    if (cookieBar) cookieBar.addEventListener('click', function (e) {
        var v = e.target.getAttribute('data-consent');
        if (!v) return;
        setConsent(v);
        hide(cookieBar);
        if (v === 'all') loadConsentScripts();
        tryPush();
    });
    document.querySelectorAll('[data-cookie-settings]').forEach(function (a) {
        if (!cookieBar) { a.hidden = true; return; }
        a.addEventListener('click', function (e) { e.preventDefault(); hide(pushCard); show(cookieBar); });
    });

    /* ---------- deal alerts (push) ---------- */
    var supported = !!pushCard && !!C.pushKey && window.isSecureContext && 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
    var isPhone = window.matchMedia('(max-width: 768px)').matches;
    var views = parseInt(store.get('vp_views') || '0', 10) + 1;
    store.set('vp_views', String(views));

    var waited = false;
    var scrolled = C.scroll <= 0;

    function snooze(days) { store.set('vp_push_snooze', String(Date.now() + days * DAY)); }
    function canAsk() {
        return supported && pushCard.dataset.auto === '1' && (C.mobile || !isPhone)
            && Notification.permission === 'default' && store.get('vp_push') !== 'on'
            && Date.now() > parseInt(store.get('vp_push_snooze') || '0', 10)
            && views >= C.minPages && !cookieBannerPending();
    }
    function tryPush() { if (waited && scrolled && canAsk() && !document.hidden && pushCard.hidden) show(pushCard); }

    if (canAsk() || cookieBannerPending()) {
        setTimeout(function () { waited = true; tryPush(); }, C.pushDelay);
        if (!scrolled) window.addEventListener('scroll', function onScroll() {
            var h = document.documentElement;
            var pct = (h.scrollTop + window.innerHeight) / Math.max(1, h.scrollHeight) * 100;
            if (pct >= C.scroll) { scrolled = true; window.removeEventListener('scroll', onScroll); tryPush(); }
        }, { passive: true });
    }

    function keyBytes(b64) {
        var pad = '='.repeat((4 - b64.length % 4) % 4);
        var raw = atob((b64 + pad).replace(/-/g, '+').replace(/_/g, '/'));
        var out = new Uint8Array(raw.length);
        for (var i = 0; i < raw.length; ++i) out[i] = raw.charCodeAt(i);
        return out;
    }
    function post(path, body) {
        return fetch(path, { method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(body) });
    }
    function subscribe() {
        return navigator.serviceWorker.register('/sw.js')
            .then(function () { return navigator.serviceWorker.ready; })
            .then(function (reg) {
                return reg.pushManager.getSubscription().then(function (s) {
                    return s || reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: keyBytes(C.pushKey) });
                });
            })
            .then(function (sub) {
                var j = sub.toJSON();
                var enc = (window.PushManager && PushManager.supportedContentEncodings) || ['aesgcm'];
                j.contentEncoding = enc.indexOf('aes128gcm') !== -1 ? 'aes128gcm' : 'aesgcm';
                return post('/push/subscribe', j);
            })
            .then(function (r) {
                if (!r.ok) throw new Error('subscribe failed');
                store.set('vp_push', 'on');
                toast(C.welcome);
            })
            .catch(function () { toast('Sorry, deal alerts could not be turned on in this browser.'); });
    }
    function unsubscribe() {
        return navigator.serviceWorker.getRegistration('/sw.js').then(function (reg) {
            return reg ? reg.pushManager.getSubscription() : null;
        }).then(function (sub) {
            if (!sub) return;
            var endpoint = sub.endpoint;
            return sub.unsubscribe().then(function () { return post('/push/unsubscribe', { endpoint: endpoint }); });
        }).then(function () {
            store.set('vp_push', 'off');
            snooze(365);
            toast('Deal alerts are off.');
        });
    }

    if (pushCard) pushCard.addEventListener('click', function (e) {
        var a = e.target.getAttribute('data-push');
        if (!a) return;
        hide(pushCard);
        if (a === 'later') { snooze(C.snoozeDays); return; }
        Notification.requestPermission().then(function (p) {
            if (p === 'granted') subscribe();
            else snooze(C.deniedDays);
        });
    });

    // footer "Deal alerts" link: lets visitors turn alerts on or off whenever they like
    document.querySelectorAll('[data-push-manage]').forEach(function (a) {
        if (!supported) return;
        a.hidden = false;
        a.addEventListener('click', function (e) {
            e.preventDefault();
            if (Notification.permission === 'denied') { toast('Notifications are blocked for this site in your browser settings.'); return; }
            if (store.get('vp_push') === 'on' && Notification.permission === 'granted') {
                if (confirm('Turn off deal alerts?')) unsubscribe();
                return;
            }
            hide(cookieBar);
            show(pushCard);
        });
    });
})();
</script>
