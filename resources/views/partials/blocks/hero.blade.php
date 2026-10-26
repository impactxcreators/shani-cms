<div class="grid" style="margin-bottom:22px">
    @foreach($banners as $b)
        <a href="{{ $b->link_url ?: '#' }}" class="card" style="padding:0;overflow:hidden;display:block;text-decoration:none">
            @if($b->image)<img src="{{ $b->image }}" alt="{{ $b->headline }}" style="width:100%;display:block">@endif
            @if($b->headline || $b->subhead)
                <div style="padding:16px">
                    @if($b->headline)<h2 style="margin:.2rem 0">{{ $b->headline }}</h2>@endif
                    @if($b->subhead)<p class="muted-sm">{{ $b->subhead }}</p>@endif
                </div>
            @endif
        </a>
    @endforeach
</div>
