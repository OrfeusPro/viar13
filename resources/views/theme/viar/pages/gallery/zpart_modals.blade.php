<div class="js-popup target-box popup-delivery popup-module">
            <div class="popup-delivery--inner popup-module--inner">
                <i class="vz-art fa-close popup-close"></i>
                <div class="pd-title">
                    @lang('gallery.photo_item_block_about_delivery_t1_1')
                </div>
                <div class="pd-content">
                    <div class="pd-top-row">
                        <div class="pd-top-col">
                            <p>@lang('gallery.photo_item_block_about_delivery_t1_2')</p>
                            <p>@lang('gallery.photo_item_block_about_delivery_t1_3')</p>
                        </div>
                        <div class="pd-top-col">
                            <p>@lang('gallery.photo_item_block_about_delivery_t1_4')</p>
                            <p>@lang('gallery.photo_item_block_about_delivery_t1_5')</p>
                        </div>
                    </div>
                    <div class="pd-m-row">
                        <p>@lang('gallery.photo_item_block_about_delivery_t1_6')</p>
                        <div class="img-row">
                            <img width="87" height="47" src="{{ asset(env('THEME') . 'images') }}/module/1.svg" loading="lazy" alt="">
                            <img width="92" height="27" src="{{ asset(env('THEME') . 'images') }}/module/2.svg" loading="lazy" alt="">
                            <img width="75" height="32" src="{{ asset(env('THEME') . 'images') }}/module/3.svg" loading="lazy" alt="">
                        </div>
                    </div>
                    <div class="pd-b-row">
                        <ul>
                            <li>
                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12.5" cy="12.5" r="12.5" fill="white"/>
                                    <circle cx="12.5" cy="12.5" r="12.5" fill="#FC8C5F"/>
                                    <g clip-path="url(#clip0_305_12)">
                                    <path d="M11.3582 17.986C11.0865 17.986 10.8241 17.8837 10.6234 17.6994L6.60142 14.0039C6.1594 13.5978 6.13051 12.9105 6.53661 12.4677C6.94348 12.0273 7.62995 11.9976 8.07275 12.4029L11.2879 15.3581L16.857 9.21271C17.2599 8.76756 17.948 8.73398 18.3931 9.13696C18.8375 9.53993 18.8718 10.2272 18.4681 10.6723L12.1642 17.6299C11.9689 17.8438 11.698 17.9719 11.4082 17.986C11.391 17.986 11.3746 17.986 11.3582 17.986Z" fill="white"/>
                                    </g>
                                    <defs>
                                    <clipPath id="clip0_305_12">
                                    <rect width="12.5" height="9.13095" fill="white" transform="translate(6.25 8.85449)"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                                <p>@lang('gallery.photo_item_block_about_delivery_t1_7')</p>
                            </li>
                            <li>
                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12.5" cy="12.5" r="12.5" fill="white"/>
                                    <circle cx="12.5" cy="12.5" r="12.5" fill="#FC8C5F"/>
                                    <g clip-path="url(#clip0_305_18)">
                                    <path d="M11.3582 17.986C11.0865 17.986 10.8241 17.8837 10.6234 17.6994L6.60142 14.0039C6.1594 13.5978 6.13051 12.9105 6.53661 12.4677C6.94348 12.0273 7.62995 11.9976 8.07275 12.4029L11.2879 15.3581L16.857 9.21271C17.2599 8.76756 17.948 8.73398 18.3931 9.13696C18.8375 9.53993 18.8718 10.2272 18.4681 10.6723L12.1642 17.6299C11.9689 17.8438 11.698 17.9719 11.4082 17.986C11.391 17.986 11.3746 17.986 11.3582 17.986Z" fill="white"/>
                                    </g>
                                    <defs>
                                    <clipPath id="clip0_305_18">
                                    <rect width="12.5" height="9.13095" fill="white" transform="translate(6.25 8.85449)"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                                <p>@lang('gallery.photo_item_block_about_delivery_t1_8')</p>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ setting('sots-seti.what_link') }}" class="pp-whatsapp">
                        <picture>
                            {{-- <source srcset="{{ asset(env('THEME') . 'images') }}/module/1 (1).webp" type="image/webp"> --}}
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module/1.png" type="image/png">
                            <img src="{{ asset(env('THEME') . 'images') }}/module/1.png" loading="lazy" alt="WhatsApp">
                        </picture>
                        <b>@lang('popup.target-box_3_btn')</b>
                    </a>
                </div>
            </div>
        </div>
        <div class="js-popup target-box popup-payment popup-module">
            <div class="popup-payment--inner popup-module--inner">
                <i class="vz-art fa-close popup-close"></i>
                <div class="pd-title">
                    @lang('gallery.photo_item_block_about_pay')
                </div>
                <div class="pd-content">
                    <div class="pp-t-row">
                        <ul>
                            <li>
                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12.5" cy="12.5" r="12.5" fill="white"/>
                                    <circle cx="12.5" cy="12.5" r="12.5" fill="#FC8C5F"/>
                                    <g clip-path="url(#clip0_305_12)">
                                    <path d="M11.3582 17.986C11.0865 17.986 10.8241 17.8837 10.6234 17.6994L6.60142 14.0039C6.1594 13.5978 6.13051 12.9105 6.53661 12.4677C6.94348 12.0273 7.62995 11.9976 8.07275 12.4029L11.2879 15.3581L16.857 9.21271C17.2599 8.76756 17.948 8.73398 18.3931 9.13696C18.8375 9.53993 18.8718 10.2272 18.4681 10.6723L12.1642 17.6299C11.9689 17.8438 11.698 17.9719 11.4082 17.986C11.391 17.986 11.3746 17.986 11.3582 17.986Z" fill="white"/>
                                    </g>
                                    <defs>
                                    <clipPath id="clip0_305_12">
                                    <rect width="12.5" height="9.13095" fill="white" transform="translate(6.25 8.85449)"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                                <p>@lang('gallery.photo_item_block_about_pay_t1_1')</p>
                            </li>
                            <li>
                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12.5" cy="12.5" r="12.5" fill="white"/>
                                    <circle cx="12.5" cy="12.5" r="12.5" fill="#FC8C5F"/>
                                    <g clip-path="url(#clip0_305_18)">
                                    <path d="M11.3582 17.986C11.0865 17.986 10.8241 17.8837 10.6234 17.6994L6.60142 14.0039C6.1594 13.5978 6.13051 12.9105 6.53661 12.4677C6.94348 12.0273 7.62995 11.9976 8.07275 12.4029L11.2879 15.3581L16.857 9.21271C17.2599 8.76756 17.948 8.73398 18.3931 9.13696C18.8375 9.53993 18.8718 10.2272 18.4681 10.6723L12.1642 17.6299C11.9689 17.8438 11.698 17.9719 11.4082 17.986C11.391 17.986 11.3746 17.986 11.3582 17.986Z" fill="white"/>
                                    </g>
                                    <defs>
                                    <clipPath id="clip0_305_18">
                                    <rect width="12.5" height="9.13095" fill="white" transform="translate(6.25 8.85449)"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                                <p>@lang('gallery.photo_item_block_about_pay_t1_2')</p>
                            </li>
                        </ul>
                        <div class="pp-text">
                            <p>@lang('gallery.photo_item_block_about_pay_t1_3')</p>
                            <p>@lang('gallery.photo_item_block_about_pay_t1_4')</p>
                        </div>
                    </div>
                    <a href="{{ setting('sots-seti.what_link') }}" class="pp-whatsapp">
                        <picture>
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module/1 (1).webp" type="image/webp">
                            <source srcset="{{ asset(env('THEME') . 'images') }}/module/1.png" type="image/png">
                            <img src="{{ asset(env('THEME') . 'images') }}/module/1.png" loading="lazy" alt="WhatsApp">
                        </picture>
                        <b>@lang('popup.target-box_3_btn')</b>
                    </a>
                </div>
            </div>
        </div>
        <div class="js-popup target-box popup-rframe">
            <div class="popup-rframe--inner">
                <i class="vz-art fa-close popup-close"></i>
                <div class="pf-title">
                    Детали Рамы
                </div>
                <div class="pf-content">
                    <div class="pf-img">
                        <picture>
                             <source loading="lazy" class="lozad" srcset="{{ asset(env('THEME') . 'images') }}/cardreproduction/frame.webp" type="image/webp">
                            <source loading="lazy" class="lozad" srcset="{{ asset(env('THEME') . 'images') }}/cardreproduction/frame.jpg" type="image/jpeg">
                            <img loading="lazy" class="lozad" width="418" height="414" src="{{ asset(env('THEME') . 'images') }}/cardreproduction/frame.jpg" alt="ViarCanvas">
                        </picture>
                    </div>
                    <div class="pf-info">
                        <ul>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_261_7572)">
                                    <path d="M19.7747 2.94941C19.4743 2.64902 18.9872 2.64902 18.6868 2.94941L6.21765 15.4186L1.31315 10.5141C1.01279 10.2136 0.525724 10.2136 0.225293 10.5141C-0.0750978 10.8145 -0.0750978 11.3015 0.225293 11.6019L5.67378 17.0504C5.97405 17.3507 6.46128 17.3508 6.76163 17.0504L19.7747 4.03727C20.0751 3.73684 20.0751 3.24981 19.7747 2.94941Z" fill="#1F9750"/>
                                    </g>
                                    <defs>
                                    <clipPath id="clip0_261_7572">
                                    <rect width="20" height="20" fill="white"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                                <span>@lang('cart_new.in_stock')</span>
                            </li>
                            <li>
                                <span>Код:</span>
                                <span>256788</span>
                            </li>
                            <li>
                                <span>@lang("gallery.material"):</span>
                                <span>Пластик</span>
                            </li>
                            <li>
                                <span>Оттенок:</span>
                                <span>Бронзовый</span>
                            </li>
                            <li>
                                <span>Ширина:</span>
                                <span>5.2 см.</span>
                            </li>
                            <li>
                                <span>Высота:</span>
                                <span>5.2 см.</span>
                            </li>
                            <li>
                                <span>Цена за погонный метр (с роботой):</span>
                                <span>15 €</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
