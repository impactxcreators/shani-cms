<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="refresh" content="0;url={{ $url }}">
    <title>Redirecting…</title>
</head>
<body style="font-family:system-ui,sans-serif;color:#555;text-align:center;padding-top:20vh">
    <script>location.replace(@json($url));</script>
    <p>Redirecting… <a href="{{ $url }}" rel="noreferrer noopener">continue</a></p>
</body>
</html>
