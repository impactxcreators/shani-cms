<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', \App\Models\Setting::get('site_name', 'Shani CMS'))</title>
    @if($meta = \App\Models\Setting::get('site_description'))<meta name="description" content="@yield('meta_description', $meta)">@endif
    <style>
        :root{--bg:#0b0c10;--card:#15171f;--fg:#e8eaf0;--muted:#9aa3b2;--brand:#f59e0b;--line:#242734}
        @media (prefers-color-scheme: light){:root{--bg:#f6f7f9;--card:#ffffff;--fg:#1a1d26;--muted:#5b6472;--line:#e7e9ee}}
        *{box-sizing:border-box}
        body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:var(--bg);color:var(--fg);line-height:1.6}
        .wrap{max-width:860px;margin:0 auto;padding:40px 20px}
        header.site{display:flex;align-items:center;gap:12px;padding:20px 0;border-bottom:1px solid var(--line)}
        .logo{font-weight:800;font-size:20px;letter-spacing:-.02em}
        .logo span{color:var(--brand)}
        main{padding:32px 0}
        h1{font-size:2rem;line-height:1.2;letter-spacing:-.02em;margin:0 0 .5rem}
        .prose{color:var(--fg)}
        .prose p{margin:1rem 0}
        .muted{color:var(--muted)}
        footer.site{border-top:1px solid var(--line);padding:24px 0;color:var(--muted);font-size:.9rem}
        a{color:var(--brand)}
    </style>
</head>
<body>
    <div class="wrap">
        <header class="site">
            <div class="logo">{!! str_replace(' ', ' <span>', e(\App\Models\Setting::get('site_name','Shani CMS'))) !!}@if(str_contains(\App\Models\Setting::get('site_name','Shani CMS'),' '))</span>@endif</div>
        </header>
        <main>@yield('content')</main>
        <footer class="site">
            <p class="muted">&copy; {{ date('Y') }} {{ \App\Models\Setting::get('site_name','Shani CMS') }}. {{ \App\Models\Setting::get('affiliate_disclosure') }}</p>
        </footer>
    </div>
</body>
</html>
