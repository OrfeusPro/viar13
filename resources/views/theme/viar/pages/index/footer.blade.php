<footer class="vz-art vz-footer footer_index_new">
        <div class="section-frame">
            <div class="vz-art footer-bar">
                <div class="vz-art footer-info" style="position: relative">
                    <div class="vz-art footer-logo">
                        <a href="{{ route('home') }}" class="vz-art footer-logo__item" aria-label="footer logo">
                            <img src="{{ asset('images/logo.svg') }}" alt="viarcanvas" loading="lazy">
                        </a>
                        <div class="vz-art footer-social footer-social_mob">
                            <ul>
                                <li>
                                    <a href="https://www.facebook.com/viarcanvas/" target="_blank" aria-label="footer social" rel="noopener noreferrer">
                                        <i class="fa-facebook"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://www.instagram.com/viarcanvas/" target="_blank" aria-label="footer social" rel="noopener noreferrer">
                                        <i class="fa-instagram"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://www.tiktok.com/@viar.canvas" target="_blank" aria-label="TikTok" rel="noopener noreferrer">
                                        <i class="fab fa-tiktok"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://www.pinterest.com/viarcanvas/" target="_blank" aria-label="Pinterest" rel="noopener noreferrer">
                                        <i class="fab fa-pinterest-p"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://www.youtube.com/playlist?list=PLYJu8JvexuaLFo04c6SOJOnKw6jAKQq5F" target="_blank" aria-label="Pinterest" rel="noopener noreferrer">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="vz-art footer-contact">

                        <a href="tel:{{ trans('header_footer_new.footer_phone_clean') }}" class="vz-art footer-contact__item">
                            <img src="{{ asset('img/icons/phone-footer.svg') }}" alt="" class="img-svg">
                            <span>
                                <b>{{ trans('header_footer_new.footer_phone') }}</b>
                            </span>
                        </a>

                        <a href="tel:{{ trans('header_footer_new.footer_phone_clean2') }}" class="vz-art footer-contact__item">
                            <img src="{{ asset('img/icons/mail-footer.svg') }}" alt="" class="img-svg">
                            <span>
                                <b>{{ trans('header_footer_new.footer_phone2') }}</b>
                            </span>
                        </a>

                        <a href="tel:+37064749413" class="vz-art footer-contact__item">
                            <img src="{{ asset('img/icons/phone-footer.svg') }}" alt="" class="img-svg">
                            <span style="font-size: 13px;">
                                Lithuania: <b>+370 64749413</b>
                            </span>
                        </a>

                        <a href="tel:+37255553615" class="vz-art footer-contact__item">
                            <img src="{{ asset('img/icons/phone-footer.svg') }}" alt="" class="img-svg">
                            <span style="font-size: 13px;">
                                Estonia: <b>+372 55553615</b>
                            </span>
                        </a>

                        <a href="mailto:orders@viarcanvas.com" class="vz-art footer-contact__item">
                            <img src="{{ asset('img/icons/mail-footer.svg') }}" alt="" class="img-svg">
                            <span>orders@viarcanvas.com</span>
                        </a>

                        <a href="{{ trans('header_footer_new.footer_addr1_link') }}" target="_blank" class="vz-art footer-contact__item" rel="noopener noreferrer">
                            <img src="{{ asset('img/icons/location.svg') }}" alt="" class="img-svg">
                            <span>{{ trans('header_footer_new.footer_addr1') }}</span>
                        </a>

                        <a href="{{ trans('header_footer_new.footer_addr2_link') }}" target="_blank" class="vz-art footer-contact__item" rel="noopener noreferrer">
                            <img src="{{ asset('img/icons/location.svg') }}" alt="" class="img-svg">
                            <span>{{ trans('header_footer_new.footer_addr2') }}</span>
                        </a>
                    </div>


                    <div class="vz-art footer-social footer-social_pc">
                        <div class="h3_old">{{ trans('header_footer_new.we_in_socs') }}</div>
                        <ul>
                            <li>
                                <a href="https://www.facebook.com/viarcanvas/" target="_blank" aria-label="footer social facebok" rel="noopener noreferrer">
                                    <i class="fa-facebook"></i>
                                </a>
                            </li>
                            <li>
                                <a href="https://www.instagram.com/viarcanvas/" target="_blank" aria-label="footer social instagram" rel="noopener noreferrer">
                                    <i class="fa-instagram"></i>
                                </a>
                            </li>
                            <li>
                                <a href="https://www.tiktok.com/@viar.canvas" target="_blank" aria-label="TikTok" rel="noopener noreferrer">
                                    <i class="fab fa-tiktok"></i>
                                </a>
                            </li>
                            <li>
                                <a href="https://www.pinterest.com/viarcanvas/" target="_blank" aria-label="Pinterest" rel="noopener noreferrer">
                                    <i class="fab fa-pinterest-p"></i>
                                </a>
                            </li>
                            <li>
                                <a href="https://www.youtube.com/playlist?list=PLYJu8JvexuaLFo04c6SOJOnKw6jAKQq5F" target="_blank" aria-label="Pinterest" rel="noopener noreferrer">
                                    <i class="fab fa-youtube"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="vz-art footer-list">
                    <div class="h3_old"><span class="vz-art faq-icon"></span> {{ trans('header_footer_new.footer_col1_name') }}</div>
                    <ul>
                        @if ($menu_items1_bot)
                            @foreach ($menu_items1_bot as $item)
								@if($item->is_show)
                                <li>
                                    <a href="{{ storefront_url($item->getTranslatedAttribute('link')) }}">
                                        {{ $item->getTranslatedAttribute('title') }}
                                    </a>
                                </li>
								@endif
                            @endforeach
                        @endif
                    </ul>
                </div>
                <div class="vz-art footer-list">
                    <div class="h3_old"><span class="vz-art faq-icon"></span> {{ trans('header_footer_new.footer_col2_name') }}</div>
                    <ul>
                        @if ($menu_items2_bot)
                            @foreach ($menu_items2_bot as $item)
								@if($item->is_show)
                                <li>
                                    <a href="{{ storefront_url($item->getTranslatedAttribute('link')) }}">
                                        {{ $item->getTranslatedAttribute('title') }}
                                    </a>
                                </li>
								@endif
                            @endforeach
                        @endif
                    </ul>
                </div>
                <div class="vz-art footer-list">
                    <div class="h3_old"><span class="vz-art faq-icon"></span> {{ trans('header_footer_new.footer_col3_name') }}</div>
                    <ul>
                        @if ($menu_items3_bot)
                            @foreach ($menu_items3_bot as $item)
								@if($item->is_show)
                                    <li>
                                        <a href="{{ storefront_url($item->getTranslatedAttribute('link')) }}">
                                            {{ $item->getTranslatedAttribute('title') }}
                                        </a>
                                    </li>
								@endif
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>


        @php
        $footerCopyright = setting('site.footer_copyright');
        $footerCopyright2 = setting('site.footer_copyright2');
        $footerPaysera = setting('site.footer_paysera');
        @endphp

        <div class="vz-art footer-copy">
            <div class="section-frame">
                <div class="vz-art footer-copy__content">
                    <p class="vz-art copy-title">© {{ now()->year }} VIARCANVAS ® @if(!is_null($footerCopyright2) && $footerCopyright2 === "1") <span style="font-size: 14px; text-transform: initial;">| {{ trans('header_footer_new.footer_copyright2') }}</span>@endif
                  <br>
                    <a href="{{ storefront_url('/condition') }}" style="color:white; text-decoration: underline;">{{ trans('header_footer_new.terms_and_conditions') }}</a>  </p>

                    @if(Route::currentRouteName() == 'home')
                    <a href="https://www.salidzini.lv/" target="_blank" style="padding-right: 5px"><img border="0" alt="Salidzini.lv logotips" title="Interneta veikali. Labākā cena" src="https://static.salidzini.lv/images/logo_button.gif"/></a>

                    <a href="https://www.kurpirkt.lv" title="Meklē preces Latvijas interneta veikalos" style="padding-right: 5px"><img style="Border:none;" alt="Meklē preces Latvijas interneta veikalos" src="//www.kurpirkt.lv/media/kurpirkt120.gif" width=120 height=40></a>
                    @endif
                    <div class="vz-art footer-pay">
                        <img style="height: 40px;" src="{{ asset('images/icon/visa_logo_white.svg') }}" alt="visa" loading="lazy">
                        <img style="height: 76px;" src="{{ asset('images/icon/mc_symbol.svg') }}" alt="mastercard" loading="lazy">
                    </div>
                </div>
            </div>
        </div>


        <div class="eraf">
            <div class="media-wrapper">
                <div class="magnetic-logo"></div>
                <div class="liaa-logo"></div>
                <div class="eraf-logo"></div>
            </div>
            @if(!is_null($footerPaysera) && $footerPaysera === "1") <div class="liaa-text"><span>{{ trans('header_footer_new.footer_paysera') }}</span></div> @endif
            @if(!is_null($footerCopyright) && $footerCopyright === "1") <div class="liaa-text"><span>{{ trans('header_footer_new.footer_copyright') }}</span></div> @endif

        </div>

    </footer>
