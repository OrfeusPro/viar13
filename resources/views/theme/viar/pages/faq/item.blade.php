<div class="question-page main-first">
    <div class="section-frame">
        <div class="question-page__inner">
            <h2 class="page-title question-page__title">
                @lang("pages.faq_title")
            </h2>
            <p>@lang("pages.faq_decr")</p>
            <div class="question-page__block">
                <div class="faq-list-frame">
                    <div class="faq-list">
                        @if ($faqs)
                            @foreach ($faqs as $faq)
                                <details class="faq-item">
                                    <summary class="faq-header">
                                        <span class="faq-icon"></span>
                                        <div class="h3_old">{!! $faq->getTranslatedAttribute('question') !!}</div>
                                    </summary>
                                    <div class="faq-body" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                                        <p>{!! $faq->getTranslatedAttribute('answer') !!}</p>
                                    </div>
                                </details>
                            @endforeach

                        @endif
                    </div>
                </div>
                <div class="question-page-info">
                    <p>@lang("pages.faq_info")</p>
                        <div class="contact-list">
                            <div class="contact-list__inner">
                                <a href="{{ setting('sots-seti.what_link') }}" target="_blank"><img width="40" height="40" src="{{ asset(env('THEME') . 'images') }}/question-all/whatsapp_icon-icons.com_65942 2.svg" alt=""></a>
                                <a href="{{ setting('sots-seti.viber_link') }}" target="_blank"><img width="40" height="40" src="{{ asset(env('THEME') . 'images') }}/question-all/Viber_icon-icons.com_66792 2.svg" alt=""></a>
                                <a href="https://www.instagram.com/viarcanvas/" target="_blank"><img width="40" height="40" src="{{ asset(env('THEME') . 'images') }}/question-all/instagram.svg" alt=""></a>
                            </div>
                            <div class="list-group">
                                <a href="tel:{{ trans('header_footer_new.footer_phone') }}">{{ trans('header_footer_new.footer_phone') }}</a>
                                <a href="tel:{{ trans('header_footer_new.footer_phone2') }}">{{ trans('header_footer_new.footer_phone2') }}</a>
                            </div>
                        </div>
                </div>
            </div>
            @php
                /** @var \Illuminate\Support\Collection|\App\Models\SiteImage[] $siteImages */
                $siteImages = $siteImages ?? \App\Models\SiteImage::where('is_show', true)->get();
                $faqImage = site_image_pair(
                    'faq_q1',
                    'faq_q1_mob',
                    env('THEME').'images/question-all/q1.png',
                    env('THEME').'images/question-all/q1Min.png',
                    ['collection' => $siteImages]
                );
            @endphp
            <div class="img">
                <picture>
                    @if(!empty($faqImage['mob']['src_webp']))
                        <source media="(max-width: 576px)" srcset="{{ $faqImage['mob']['src_webp'] }}" type="image/webp">
                    @endif
                    @if(!empty($faqImage['mob']['type']))
                        <source media="(max-width: 576px)" srcset="{{ $faqImage['mob']['src'] }}" type="{{ $faqImage['mob']['type'] }}">
                    @endif
                    @if(!empty($faqImage['desk']['src_webp']))
                        <source srcset="{{ $faqImage['desk']['src_webp'] }}" type="image/webp">
                    @endif
                    @if(!empty($faqImage['desk']['type']))
                        <source srcset="{{ $faqImage['desk']['src'] }}" type="{{ $faqImage['desk']['type'] }}">
                    @endif
                    <img width="380" height="640" src="{{ $faqImage['desk']['src'] }}" alt="{{ $faqImage['desk']['alt'] ?? '' }}" title="{{ $faqImage['desk']['title'] ?? '' }}">
                </picture>
            </div>
        </div>
    </div>
</div>

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

    if ($faqs) {
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
    }
@endphp

@if (!empty($faqSchema))
<script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqSchema,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
