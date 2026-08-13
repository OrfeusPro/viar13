<div class="new-painters">
    <div class="section-frame">
        <div class="new-painters__inner">
            <div class="paintners-title page-title">
                @lang("gallery.rep_new_in_painters")
            </div>
            <div class="cats-slider">
                <div class="cats-slider__wrapper swiper-wrapper">
				
					@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_one_item', ['items' => $rep_last_items, 'type' => $type, 'slide' => true])					

                </div>
                <div class="swiper-button swiper-prev">
                    <svg width="12" height="19" viewBox="0 0 12 19" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 18L2 9.5L11 1" stroke="#1E2533" stroke-width="2" />
                    </svg>
                </div>
                <div class="swiper-button swiper-next">
                    <svg width="12" height="19" viewBox="0 0 12 19" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.999999 0.999999L10 9.5L1 18" stroke="white" stroke-width="2" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>