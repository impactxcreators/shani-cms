@php
    $host = parse_url(url('/'), PHP_URL_HOST);
    $opens = filled($url) ? $url : url('/');
@endphp
<div style="font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif">
    <div style="font-size:12px;font-weight:600;color:#6b7280;margin-bottom:8px;text-transform:uppercase;letter-spacing:.04em">Preview</div>
    <div style="max-width:360px;background:#fff;color:#111827;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 8px 24px -10px rgba(0,0,0,.25);overflow:hidden">
        <div style="display:flex;gap:10px;padding:12px 14px">
            <img src="{{ url('/favicon.ico') }}" alt="" style="width:36px;height:36px;border-radius:8px;flex:none;background:#f3f4f6">
            <div style="min-width:0">
                <div style="font-size:11px;color:#6b7280">{{ $host }} · now</div>
                <div style="font-weight:700;font-size:14px;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ filled($title) ? $title : 'Your title' }}</div>
                <div style="font-size:13px;color:#374151;line-height:1.4;margin-top:2px">{{ filled($body) ? $body : 'Your message' }}</div>
            </div>
        </div>
        @if(filled($image))
            <img src="{{ $image }}" alt="" style="display:block;width:100%;aspect-ratio:2/1;object-fit:cover;background:#f3f4f6">
        @endif
    </div>
    <div style="font-size:12px;color:#6b7280;margin-top:8px;word-break:break-all">Opens: <a href="{{ $opens }}" target="_blank" rel="noopener" style="color:#d97706">{{ $opens }}</a></div>
</div>
