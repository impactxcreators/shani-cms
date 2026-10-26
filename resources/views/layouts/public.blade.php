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
.grid{display:grid;gap:16px}.grid.cols2{grid-template-columns:repeat(auto-fill,minmax(300px,1fr))}
.card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:18px}
.card h3{margin:.2rem 0 .4rem;font-size:1.05rem}
.btn{display:inline-block;background:var(--brand);color:#111;font-weight:700;text-decoration:none;padding:10px 16px;border-radius:10px;border:none;cursor:pointer;font-size:.95rem}
.btn.alt{background:transparent;color:var(--brand);border:1px solid var(--brand)}
.pill{display:inline-block;font-size:.72rem;letter-spacing:.06em;text-transform:uppercase;color:var(--brand);border:1px solid rgba(245,158,11,.4);padding:3px 9px;border-radius:999px}
.price{font-weight:800;font-size:1.2rem}.was{color:var(--muted);text-decoration:line-through;margin-left:6px}
.codebox{display:flex;gap:8px;align-items:center;margin-top:10px}
.code{font-family:ui-monospace,Menlo,monospace;border:1px dashed var(--brand);border-radius:8px;padding:8px 12px;letter-spacing:.08em;background:rgba(245,158,11,.08)}
.storehead{display:flex;align-items:center;gap:16px;margin-bottom:8px}.storehead img{width:64px;height:64px;object-fit:contain;border-radius:12px;background:#fff;padding:6px}
.muted-sm{color:var(--muted);font-size:.9rem}
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
