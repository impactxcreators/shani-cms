<details class="smnode">
    <summary>
        @if($node["url"])<a class="smname" href="{{ $node["url"] }}">{{ $node["name"] }}</a>@else<span class="smname">{{ $node["name"] }}</span>@endif
        @php($cnt = count($node["pages"]))
        @if($cnt)<span class="smcount">{{ $cnt }}</span>@endif
    </summary>
    <div class="smbody">
        @if($node["url"])
            <p class="smview"><a href="{{ $node["url"] }}">Open {{ $node["name"] }} page →</a></p>
        @endif
        @if(count($node["pages"]))
            <ul class="smlist">
                @foreach($node["pages"] as $p)
                    <li><a href="{{ $p["url"] }}">{{ $p["label"] }}</a></li>
                @endforeach
            </ul>
        @endif
        @foreach($node["children"] as $child)
            @include("partials.sitemap-node", ["node" => $child])
        @endforeach
        @if(! count($node["pages"]) && ! count($node["children"]))
            <p class="muted-sm">No pages here yet.</p>
        @endif
    </div>
</details>
