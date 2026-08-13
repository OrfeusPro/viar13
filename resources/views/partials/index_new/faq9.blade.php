<section class="faq">
    <div class="section-frame">
        <div class="page-title faq-title_tablet h2_old">{!! trans('homepage_new.faq_title') !!}</div>
        <div class="faq-content">
            <div class="faq-info">
                <div class="page-title h2_old">{!! trans('homepage_new.faq_title') !!}</div>
                <div class="faq-description">
                    <picture>
                        <source srcset="{{ asset('images/faq.webp') }}" type="image/webp">
                        <source srcset="{{ asset('images/faq.png') }}">
                        <img src="{{ asset('images/faq.png') }}" class="faq-photo" alt="img"
                            loading="lazy">
                    </picture>
                    <div class="h3_old">{!! trans('homepage_new.faq_whats_title') !!}</div>
                    <p>{!! trans('homepage_new.faq_whats_desc') !!}
                    </p>
                    <a href="{{ $bot_form['succ_whats_link'] }}" target="_blank">
                        <svg>
                            <use xlink:href="{{ asset(env('THEME').'sprite.svg#wh') }}"></use>
                        </svg>{!! trans('homepage_new.faq_write_whatsup') !!}
                    </a>
                </div>
            </div>
            <div class="faq-list-frame">
                <div class="faq-list">
                    @if ($faqs)
                        @foreach ($faqs as $faq)
                            <div class="faq-item @if($loop->index>8) faq-item_hide @endif">
                                <div class="faq-header">
                                    <span class="faq-icon"></span>
                                    <div class="h3_old">{!! $faq->getTranslatedAttribute('question') !!}</div>
                                </div>
                                <div class="faq-body">
                                    <p>{!! $faq->getTranslatedAttribute('answer')  !!}</p>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                @if(count($faqs)>8)
                    <a href="#" class="faq-all">
                        <picture>
                            <source srcset="{{ asset('images/icon/load-more.webp') }}" type="image/webp">
                            <source srcset="{{ asset('images/icon/load-more.png') }}">
                            <img src="{{ asset('images/icon/load-more.png') }}" alt="img" loading="lazy">
                        </picture>
                        <span>{!! trans('homepage_new.faq_show_more_btn_text') !!}</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>

@if ($faqs)
@php
    $cleanFaqSchemaText = function ($value) {
        $value = (string) $value;
        $value = preg_replace('/<\s*br\s*\/?\s*>/i', ' ', $value);
        $value = strip_tags($value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/[\x{200D}\x{FE0F}\x{1F1E6}-\x{1F1FF}\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', '', $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim($value);
    };

    $faqSchema = [];

    foreach ($faqs as $faq) {
        $faqSchema[] = [
            '@type' => 'Question',
            'name' => $cleanFaqSchemaText($faq->getTranslatedAttribute('question')),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $cleanFaqSchemaText($faq->getTranslatedAttribute('answer')),
            ],
        ];
    }
@endphp
<script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqSchema,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
