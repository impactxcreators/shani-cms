<section style="margin:22px 0">
    @if($block->title)<h2>{{ $block->title }}</h2>@endif
    <div class="prose">{!! nl2br(e($block->config['text'] ?? '')) !!}</div>
</section>
