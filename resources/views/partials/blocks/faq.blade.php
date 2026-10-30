@php
    $faqs = collect($block->config['faqs'] ?? [])->filter(fn ($f) => filled($f['question'] ?? null) && filled($f['answer'] ?? null))->values();
    if ($faqs->isEmpty()) {
        $faqs = collect(\App\Support\HomeDefaults::FAQ);
    }
@endphp
@push('jsonld')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $faqs->map(fn ($f) => ['@type' => 'Question', 'name' => $f['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']]])->all(),
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
<section style="margin:22px 0">
    <h2>{{ $block->title ?: 'Frequently asked questions' }}</h2>
    <div class="faqlist">
        @foreach($faqs as $f)
            <details class="smnode faq">
                <summary><span class="smname">{{ $f['question'] }}</span></summary>
                <div class="smbody"><p>{!! nl2br(e($f['answer'])) !!}</p></div>
            </details>
        @endforeach
    </div>
</section>
