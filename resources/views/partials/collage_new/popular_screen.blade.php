<!-- popular collage -->

      <div class="collage-popular__screen">
        <div class="section-frame">
          <div class="page-title page-collage-title">
            {!! trans('collage_new.z1_popular_screen_title') !!}
          </div>
		  @if($ACollagePopularScreen)
          <div class="collage-pop__slider">
            <div class="collage-pop__slider--inner">
			@foreach($ACollagePopularScreen as $popular_screen)
              <a class="examples-slide">
                <div class="examples-slide__photo" data-fancybox="examples">
                  <img src="{{ Voyager::image($popular_screen->image) }}" @altAttrs($popular_screen, 'image', data_get($popular_screen, 'image')) loading="lazy" />
                </div>
                <div class="examples-slide__title">{!! $popular_screen->getTranslatedAttribute('title')!!}</div>
                <div class="examples-size">
                  <svg class="happy-item__icon">
                    <use xlink:href="{{ asset(env('THEME').'sprite.svg#size') }}"></use>
                  </svg>
                  <p>{!! $popular_screen->size !!}</p>
                </div>
                <div class="example-price">
                  {!! trans('collage_new.z1_popular_screen_ot') !!} {!! $popular_screen->getTranslatedAttribute('price') !!}€
                </div>
                <div class="examples-slide__btn js-examples" data-catid="2" data-style="{!! $popular_screen->getTranslatedAttribute('title')!!}" data-size="{!! $popular_screen->size !!}">{!! $popular_screen->getTranslatedAttribute('btn_text')!!}</div>
              </a>
			@endforeach
            </div>
            <div class="examples-arrow">
              <a href="#" class="examples-prev examples-prev-col" aria-label="examples prev">
                <svg width="48" height="51" viewBox="0 0 48 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M27.2335 31.1145C27.334 31.0141 27.3842 30.8986 27.3842 30.768C27.3842 30.6374 27.334 30.5219 27.2335 30.4215L21.3122 24.5001L27.2335 18.5788C27.334 18.4784 27.3842 18.3629 27.3842 18.2323C27.3842 18.1017 27.334 17.9862 27.2335 17.8857L26.4802 17.1324C26.3797 17.0319 26.2642 16.9817 26.1336 16.9817C26.0031 16.9817 25.8876 17.0319 25.7871 17.1324L18.7659 24.1536C18.6655 24.254 18.6152 24.3696 18.6152 24.5001C18.6152 24.6307 18.6655 24.7462 18.7659 24.8467L25.7871 31.8679C25.8876 31.9683 26.0031 32.0186 26.1336 32.0186C26.2642 32.0186 26.3797 31.9683 26.4802 31.8679L27.2335 31.1145Z"
                    fill="#fff"></path>
                </svg>
              </a>
              <a href="#" class="examples-next examples-next-col" aria-label="examples next">
                <svg width="48" height="51" viewBox="0 0 48 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M20.7665 31.1145C20.666 31.0141 20.6158 30.8986 20.6158 30.768C20.6158 30.6374 20.666 30.5219 20.7665 30.4215L26.6878 24.5001L20.7665 18.5788C20.666 18.4784 20.6158 18.3629 20.6158 18.2323C20.6158 18.1017 20.666 17.9862 20.7665 17.8857L21.5198 17.1324C21.6203 17.0319 21.7358 16.9817 21.8664 16.9817C21.9969 16.9817 22.1124 17.0319 22.2129 17.1324L29.2341 24.1536C29.3345 24.254 29.3848 24.3696 29.3848 24.5001C29.3848 24.6307 29.3345 24.7462 29.2341 24.8467L22.2129 31.8679C22.1124 31.9683 21.9969 32.0186 21.8664 32.0186C21.7358 32.0186 21.6203 31.9683 21.5198 31.8679L20.7665 31.1145Z"
                    fill="#fff"></path>
                </svg>
              </a>
            </div>
          </div>
		  @endif
        </div>
      </div>
