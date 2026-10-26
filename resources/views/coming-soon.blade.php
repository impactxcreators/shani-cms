<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ \App\Models\Setting::get('site_name','Shani CMS') }} — Coming soon</title>
    <style>
        :root{--bg:#0b0c10;--fg:#e8eaf0;--muted:#9aa3b2;--brand:#f59e0b}
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:grid;place-items:center;font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
            background:radial-gradient(1200px 600px at 50% -10%,#1b1e2b,#0b0c10);color:var(--fg);text-align:center;padding:24px}
        .box{max-width:560px}
        .badge{display:inline-block;font-size:.8rem;letter-spacing:.14em;text-transform:uppercase;color:var(--brand);border:1px solid rgba(245,158,11,.4);padding:6px 12px;border-radius:999px;margin-bottom:22px}
        h1{font-size:2.6rem;letter-spacing:-.03em;margin:0 0 14px}
        h1 b{color:var(--brand)}
        p{color:var(--muted);font-size:1.1rem;margin:0 auto;max-width:42ch}
        .dots{margin-top:30px;display:flex;gap:8px;justify-content:center}
        .dots i{width:8px;height:8px;border-radius:50%;background:var(--brand);opacity:.5;animation:b 1.2s infinite}
        .dots i:nth-child(2){animation-delay:.2s}.dots i:nth-child(3){animation-delay:.4s}
        @keyframes b{0%,100%{opacity:.3;transform:translateY(0)}50%{opacity:1;transform:translateY(-5px)}}
    </style>
</head>
<body>
    <div class="box">
        <div class="badge">Launching soon</div>
        <h1>{{ \App\Models\Setting::get('site_name','Shani CMS') }}</h1>
        <p>{{ $message ?? 'We will be back soon.' }}</p>
        <div class="dots"><i></i><i></i><i></i></div>
    </div>
</body>
</html>
