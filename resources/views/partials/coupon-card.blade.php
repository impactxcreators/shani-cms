<div class="card">
    <div style="display:flex;justify-content:space-between;gap:10px">
        <div>
            @if($showStore ?? false)<div class="muted-sm">{{ $coupon->store->name }}</div>@endif
            <h3>{{ $coupon->title }}</h3>
        </div>
        @if($coupon->is_verified)<span class="pill">Verified</span>@endif
    </div>
    @if($coupon->description)<p class="muted-sm">{{ $coupon->description }}</p>@endif
    @if($coupon->code)
        <div class="codebox" x-data="{shown:false}">
            <a class="btn" href="{{ url('/go/coupon/'.$coupon->id) }}" target="_blank" rel="sponsored nofollow noopener"
               onclick="this.closest('.codebox').querySelector('.code').style.display='inline-block';this.style.display='none'">Show code</a>
            <span class="code" style="display:none">{{ $coupon->code }}</span>
        </div>
    @else
        <div style="margin-top:10px">
            <a class="btn" href="{{ url('/go/coupon/'.$coupon->id) }}" target="_blank" rel="sponsored nofollow noopener">Get deal</a>
        </div>
    @endif
</div>
