<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield("title", \App\Models\Setting::get("site_name", "Shani CMS"))</title>
    @if($meta = \App\Models\Setting::get("site_description"))<meta name="description" content="@yield("meta_description", $meta)">@endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg:#0a0b10;--bg2:#0e1018;--card:#14161f;--card2:#1b1e29;--fg:#eceef4;--muted:#98a1b2;
            --brand:#f59e0b;--brand2:#fb923c;--brand-ink:#1b1205;--line:#262a37;--ring:rgba(245,158,11,.35);
            --shadow:0 1px 2px rgba(0,0,0,.35),0 12px 30px -14px rgba(0,0,0,.6);
            --radius:16px;
        }
        @media (prefers-color-scheme: light){:root{
            --bg:#f6f7f9;--bg2:#eef1f6;--card:#ffffff;--card2:#f7f9fc;--fg:#141821;--muted:#5b6474;
            --line:#e6e9ef;--brand-ink:#3a2708;--shadow:0 1px 2px rgba(16,24,40,.06),0 14px 34px -16px rgba(16,24,40,.22);
        }}
        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{margin:0;font-family:"Inter",ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
             background:var(--bg);color:var(--fg);line-height:1.6;-webkit-font-smoothing:antialiased;
             background-image:radial-gradient(1200px 500px at 50% -200px,rgba(245,158,11,.10),transparent 70%);background-attachment:fixed}
        .wrap{max-width:1140px;margin:0 auto;padding:0 20px}
        a{color:var(--brand);text-decoration:none}

        /* Header */
        header.site{position:sticky;top:0;z-index:50;background:color-mix(in srgb,var(--bg) 80%,transparent);
                    backdrop-filter:saturate(140%) blur(12px);border-bottom:1px solid var(--line)}
        .headbar{display:flex;align-items:center;gap:14px 22px;padding:14px 20px;flex-wrap:wrap}
        .logo{font-weight:800;font-size:21px;letter-spacing:-.03em;color:var(--fg);white-space:nowrap}
        .logo span{background:linear-gradient(135deg,var(--brand),var(--brand2));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
        .mainnav{display:flex;gap:4px 6px;align-items:center}
        .mainnav a{color:var(--muted);font-weight:600;font-size:.94rem;padding:8px 12px;border-radius:9px;transition:.15s}
        .mainnav a:hover{color:var(--fg);background:var(--card)}
        .mainnav a.active{color:var(--brand)}
        .searchform{display:flex;gap:8px;margin-left:auto;max-width:300px;width:100%}
        .searchform input{flex:1;min-width:0;background:var(--card);border:1px solid var(--line);color:var(--fg);border-radius:11px;padding:10px 14px;font-size:.95rem;transition:.15s}
        .searchform input::placeholder{color:var(--muted)}
        .searchform input:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 3px var(--ring)}

        main{padding:34px 0 56px}
        h1{font-size:2.3rem;line-height:1.12;letter-spacing:-.03em;font-weight:800;margin:0 0 .4rem}
        main h2{font-size:1.4rem;font-weight:800;letter-spacing:-.02em;margin:40px 0 18px;display:flex;align-items:center;gap:11px}
        main h2::before{content:"";width:4px;height:1.05em;border-radius:3px;background:linear-gradient(var(--brand),var(--brand2))}
        .card h2::before,.prose h2::before{display:none}
        .muted{color:var(--muted)}.muted-sm{color:var(--muted);font-size:.9rem}
        .prose{max-width:760px}.prose p{margin:1rem 0}.prose h2{margin:1.6rem 0 .8rem}

        /* Grid + cards */
        .grid{display:grid;gap:18px}.grid.cols2{grid-template-columns:repeat(auto-fill,minmax(270px,1fr))}
        .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);transition:transform .15s,border-color .15s}
        a.card:hover,.card.searchresult:hover{border-color:var(--brand);transform:translateY(-2px)}
        .card h3{margin:.1rem 0 .4rem;font-size:1.06rem;font-weight:700;letter-spacing:-.01em}

        /* Buttons */
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;background:linear-gradient(135deg,var(--brand),var(--brand2));
             color:var(--brand-ink);font-weight:700;padding:11px 18px;border-radius:11px;border:none;cursor:pointer;font-size:.95rem;
             box-shadow:0 6px 16px -6px rgba(245,158,11,.55);transition:.15s}
        .btn:hover{transform:translateY(-1px);filter:brightness(1.06)}
        .btn.alt{background:transparent;color:var(--fg);border:1px solid var(--line);box-shadow:none}
        .btn.alt:hover{border-color:var(--brand);color:var(--brand)}

        /* Pills + price + code */
        .pill{display:inline-flex;align-items:center;gap:5px;font-size:.68rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
              color:var(--brand);background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);padding:3px 9px;border-radius:999px}
        .price{font-weight:800;font-size:1.25rem}.was{color:var(--muted);text-decoration:line-through;margin-left:7px;font-weight:500}
        .codebox{display:flex;gap:8px;align-items:center;margin-top:12px}
        .code{font-family:ui-monospace,Menlo,monospace;border:1px dashed var(--brand);border-radius:9px;padding:9px 14px;letter-spacing:.1em;background:rgba(245,158,11,.08);font-weight:700}
        .storehead{display:flex;align-items:center;gap:18px;margin-bottom:10px}
        .storehead img{width:72px;height:72px;object-fit:contain;border-radius:16px;background:#fff;padding:8px;box-shadow:var(--shadow)}

        /* Hero search on home */
        .homesearch{margin:30px auto 10px;text-align:center}
        .searchform.big{max-width:640px;margin:0 auto}
        .searchform.big input{padding:15px 20px;font-size:1.05rem;border-radius:14px}
        .homesearch .hint{color:var(--muted);font-size:.9rem;margin-top:12px}

        /* Footer */
        .site-footer{border-top:1px solid var(--line);margin-top:40px;background:var(--bg2)}
        .site-footer .wrap{padding-top:30px;padding-bottom:34px}
        .footnav{margin:0 0 12px;display:flex;flex-wrap:wrap;gap:6px 4px}
        .footnav a{color:var(--muted);margin-right:4px;font-weight:600;font-size:.92rem}
        .footnav a:hover{color:var(--brand)}
        .site-footer .copy{color:var(--muted);font-size:.86rem;margin:0}

        @media(max-width:820px){
            .headbar{gap:12px}
            .searchform{order:2;max-width:none;flex:1 1 140px}
            .mainnav{order:3;width:100%;overflow-x:auto;flex-wrap:nowrap;gap:2px;padding-bottom:2px;scrollbar-width:none}
            .mainnav::-webkit-scrollbar{display:none}
            .mainnav a{white-space:nowrap}
            h1{font-size:1.85rem}
        }
    </style>
    <link rel="canonical" href="@yield("canonical", url()->current())">
    <meta property="og:site_name" content="{{ \App\Models\Setting::get("site_name", "Shani CMS") }}">
    <meta property="og:title" content="@yield("title", \App\Models\Setting::get("site_name", "Shani CMS"))">
    <meta property="og:description" content="@yield("meta_description", \App\Models\Setting::get("site_description", ""))">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection("og_image")<meta property="og:image" content="@yield("og_image")">@endif
    <meta name="twitter:card" content="summary_large_image">
    @php
        $__at = chr(64);
        $__sn = \App\Models\Setting::get("site_name", "Shani CMS");
        $__ld = json_encode([
            $__at."context" => "https://schema.org",
            $__at."graph" => [
                [$__at."type" => "Organization", "name" => $__sn, "url" => url("/")],
                [$__at."type" => "WebSite", "name" => $__sn, "url" => url("/"),
                 "potentialAction" => [$__at."type" => "SearchAction", "target" => url("/")."/search?q={search_term_string}", "query-input" => "required name=search_term_string"]],
            ],
        ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $__ld !!}</script>
    @hasSection("robots")<meta name="robots" content="@yield("robots")">@endif
    @stack("jsonld")
</head>
<body>
    <header class="site">
        <div class="wrap headbar">
            <a href="{{ url("/") }}" class="logo">{!! str_replace(" ", " <span>", e(\App\Models\Setting::get("site_name","Shani CMS"))) !!}@if(str_contains(\App\Models\Setting::get("site_name","Shani CMS")," "))</span>@endif</a>
            <nav class="mainnav">
                <a href="{{ url("/store") }}" class="{{ request()->is("store","store/*") ? "active" : "" }}">Stores</a>
                <a href="{{ url("/categories") }}" class="{{ request()->is("categories","category/*") ? "active" : "" }}">Categories</a>
                <a href="{{ url("/coupons") }}" class="{{ request()->is("coupons") ? "active" : "" }}">Coupons</a>
                <a href="{{ url("/deals") }}" class="{{ request()->is("deals") ? "active" : "" }}">Deals</a>
                <a href="{{ url("/best") }}" class="{{ request()->is("best") ? "active" : "" }}">Best</a>
                <a href="{{ url("/blog") }}" class="{{ request()->is("blog","blog/*") ? "active" : "" }}">Blog</a>
            </nav>
            @include("partials.search-form")
        </div>
    </header>
    <main class="wrap">@yield("content")</main>
    <footer class="site-footer">
        <div class="wrap">
            <p class="footnav">
                <a href="{{ url("/store") }}">Stores</a> ·
                <a href="{{ url("/categories") }}">Categories</a> ·
                <a href="{{ url("/coupons") }}">Coupons</a> ·
                <a href="{{ url("/deals") }}">Deals</a> ·
                <a href="{{ url("/best") }}">Best picks</a> ·
                <a href="{{ url("/blog") }}">Blog</a> ·
                <a href="{{ url("/sitemap") }}">Sitemap</a>
            </p>
            <p class="copy">&copy; {{ date("Y") }} {{ \App\Models\Setting::get("site_name","Shani CMS") }}. {{ \App\Models\Setting::get("affiliate_disclosure") }}</p>
        </div>
    </footer>
</body>
</html>
