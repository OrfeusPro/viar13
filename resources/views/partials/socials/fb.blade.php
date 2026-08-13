@if (env('IS_LOCALHOST')!=1)

{{--
    <!-- Messenger Плагин чата Code -->
    <div id="fb-root"></div><!-- Your Плагин чата code -->
    <div id="fb-customer-chat" class="fb-customerchat"></div>

    @php
        $cur_loc_chat = app()->getLocale();
        if($cur_loc_chat == 'en'){
            $lang_url = 'en_GB';
        }else{
            $lang_url = app()->getLocale().'_'.strtoupper(app()->getLocale());
        }
    @endphp
    <script>
        var chatbox = document.getElementById('fb-customer-chat');
        chatbox.setAttribute("page_id", "242152845938593");
        chatbox.setAttribute("attribution", "biz_inbox");
        window.fbAsyncInit = function () {
            FB.init({
                xfbml: true,
                version: 'v10.0'
            });
        };

        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = 'https://connect.facebook.net/{{ $lang_url }}/sdk/xfbml.customerchat.js';
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
--}}

<div class="phone-mobile-btn js-phone-mobile-btn js-hidden-balls">
    <svg>
       <use xlink:href="{{ ver_asset(env('THEME').'img/cart/sprite.svg') }}#phone"></use>
    </svg>
    <div class="phone-mobile-btn__text js-active">
       <span><b>@lang('homepage_new.write_to_us')</b></span>
    </div>
    <div class="phone-mobile-btn__drop">
       <a href="{{ setting('sots-seti.what_link') }}" class="whatsapp">
          <svg>
             <use xlink:href="{{ ver_asset(env('THEME').'img/cart/sprite.svg') }}#whatsapp"></use>
          </svg>
       </a>
       <a href="{{ setting('sots-seti.viber_link') }}" class="viber">
          <svg>
             <use xlink:href="{{ ver_asset(env('THEME').'img/cart/sprite.svg') }}#viber"></use>
          </svg>
       </a>
       <a href="{{ setting('sots-seti.telegram_link') }}" class="telegram">
          <svg>
             <use xlink:href="{{ ver_asset(env('THEME').'img/cart/sprite.svg') }}#telegram"></use>
          </svg>
       </a>
    </div>
 </div>

	{{-- <a target="_blank" href="{{ setting('sots-seti.what_link') }}" class=" wht__link callback-bt whatsapp"
                style="
    left: 25px;
    background: #25D366;
    border: 2px solid #25D366;
">
        <div class="text-call">
            <svg style="width: 50%;" xmlns="http://www.w3.org/2000/svg" viewBox="0 -256 1792 1792">
                <path
                    d="M1567.458 997.017q0 27-10 70.5t-21 68.5q-21 50-122 106-94 51-186 51-27 0-52.5-3.5t-57.5-12.5q-32-9-47.5-14.5t-55.5-20.5q-40-15-49-18-98-35-175-83-128-79-264.5-215.5t-215.5-264.5q-48-77-83-175-3-9-18-49t-20.5-55.5q-5.5-15.5-14.5-47.5t-12.5-57.5q-3.5-25.5-3.5-52.5 0-92 51-186 56-101 106-122 25-11 68.5-21t70.5-10q14 0 21 3 18 6 53 76 11 19 30 54t35 63.5q16 28.5 31 53.5 3 4 17.5 25t21.5 35.5q7 14.5 7 28.5 0 20-28.5 50t-62 55q-33.5 25-62 53t-28.5 46q0 9 5 22.5t8.5 20.5q3.5 7 14 24t11.5 19q76 137 174 235t235 174q2 1 19 11.5t24 14q7 3.5 20.5 8.5t22.5 5q18 0 46-28.5t53-62q25-33.5 55-62t50-28.5q14 0 28.5 7t35.5 21.5q21 14.5 25 17.5 25 15 53.5 31t63.5 35q35 19 54 30 70 35 76 53 3 7 3 21z"
                    fill="#fff"></path>
            </svg>
        </div>
    </a>
    <style>
        /*кнопка звонка*/
        .whatsapp {
            -webkit-animation: hoverWaveWhat linear 1s infinite !important;
            animation: hoverWaveWhat linear 1s infinite !important;
        }

        .callback-bt {
            background: #38a3fd;
            border: 2px solid #38a3fd;
            border-radius: 50%;
            box-shadow: 0 8px 10px rgba(56, 163, 253, 0.3);
            cursor: pointer;
            height: 68px;
            text-align: center;
            width: 68px;
            position: fixed;
            left: 30px;
            bottom: 30px;
            z-index: 999;
            transition: .3s;
            -webkit-animation: hoverWave linear 1s infinite;
            animation: hoverWave linear 1s infinite;
        }

        @media (max-width: 600px) {
            .callback-bt {
                left: 20px !important;
                bottom: 20px !important;
                height: 60px !important;
                width: 60px !important;
            }

            .text-call {
                height: 60px !important;
                width: 60px !important;
            }
        }

        .callback-bt .text-call {
            height: 68px;
            width: 68px;
            border-radius: 50%;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .callback-bt .text-call span {
            text-align: center;
            color: #38a3fd;
            opacity: 0;
            font-size: 0;
            position: absolute;
            right: 4px;
            top: 22px;
            line-height: 14px;
            font-weight: 600;
            text-transform: uppercase;
            transition: opacity .3s linear;
            font-family: 'montserrat', Arial, Helvetica, sans-serif;
        }

        .callback-bt .text-call:hover span {
            opacity: 1;
            font-size: 11px;
        }

        .callback-bt i {
            color: #fff;
            font-size: 34px;
            transition: .3s;
            line-height: 66px;
            transition: .5s ease-in-out;
        }

        .callback-bt i {
            animation: 1200ms ease 0s normal none 1 running shake;
            animation-iteration-count: infinite;
            -webkit-animation: 1200ms ease 0s normal none 1 running shake;
            -webkit-animation-iteration-count: infinite;
        }

        @-webkit-keyframes hoverWaveWhat {
            0% {
                box-shadow: 0 8px 10px rgb(56 253 98 / 30%), 0 0 0 0 rgb(56 253 98 / 20%), 0 0 0 0 rgb(56 253 98 / 20%);
            }
            40% {
                box-shadow: 0 8px 10px rgb(56 253 98 / 30%), 0 0 0 15px rgb(56 253 98 / 20%), 0 0 0 0 rgb(56 253 98 / 20%);
            }
            80% {
                box-shadow: 0 8px 10px rgb(56 253 98 / 30%), 0 0 0 30px rgb(56 253 98 / 0%), 0 0 0 26.7px rgb(56 253 98 / 7%);
            }
            100% {
                box-shadow: 0 8px 10px rgb(56 253 98 / 30%), 0 0 0 30px rgb(56 253 98 / 0%), 0 0 0 40px rgb(56 253 98 / 0%);
            }
        }

        @keyframes hoverWave {
            0% {
                box-shadow: 0 8px 10px rgba(56, 163, 253, 0.3), 0 0 0 0 rgba(56, 163, 253, 0.2), 0 0 0 0 rgba(56, 163, 253, 0.2)
            }
            40% {
                box-shadow: 0 8px 10px rgba(56, 163, 253, 0.3), 0 0 0 15px rgba(56, 163, 253, 0.2), 0 0 0 0 rgba(56, 163, 253, 0.2)
            }
            80% {
                box-shadow: 0 8px 10px rgba(56, 163, 253, 0.3), 0 0 0 30px rgba(56, 163, 253, 0), 0 0 0 26.7px rgba(56, 163, 253, 0.067)
            }
            100% {
                box-shadow: 0 8px 10px rgba(56, 163, 253, 0.3), 0 0 0 30px rgba(56, 163, 253, 0), 0 0 0 40px rgba(56, 163, 253, 0.0)
            }
        }

        @-webkit-keyframes hoverWave {
            0% {
                box-shadow: 0 8px 10px rgba(56, 163, 253, 0.3), 0 0 0 0 rgba(56, 163, 253, 0.2), 0 0 0 0 rgba(56, 163, 253, 0.2)
            }
            40% {
                box-shadow: 0 8px 10px rgba(56, 163, 253, 0.3), 0 0 0 15px rgba(56, 163, 253, 0.2), 0 0 0 0 rgba(56, 163, 253, 0.2)
            }
            80% {
                box-shadow: 0 8px 10px rgba(56, 163, 253, 0.3), 0 0 0 30px rgba(56, 163, 253, 0), 0 0 0 26.7px rgba(56, 163, 253, 0.067)
            }
            100% {
                box-shadow: 0 8px 10px rgba(56, 163, 253, 0.3), 0 0 0 30px rgba(56, 163, 253, 0), 0 0 0 40px rgba(56, 163, 253, 0.0)
            }
        }

        @keyframes hoverWave {
            0% {
                box-shadow: 0 8px 10px rgba(56, 163, 253, 0.3), 0 0 0 0 rgba(56, 163, 253, 0.2), 0 0 0 0 rgba(56, 163, 253, 0.2)
            }
            40% {
                box-shadow: 0 8px 10px rgba(56, 163, 253, 0.3), 0 0 0 15px rgba(56, 163, 253, 0.2), 0 0 0 0 rgba(56, 163, 253, 0.2)
            }
            80% {
                box-shadow: 0 8px 10px rgba(56, 163, 253, 0.3), 0 0 0 30px rgba(56, 163, 253, 0), 0 0 0 26.7px rgba(56, 163, 253, 0.067)
            }
            100% {
                box-shadow: 0 8px 10px rgba(56, 163, 253, 0.3), 0 0 0 30px rgba(56, 163, 253, 0), 0 0 0 40px rgba(56, 163, 253, 0.0)
            }
        }

        /* animations icon */
        @keyframes shake {
            0% {
                transform: rotateZ(0deg);
                -ms-transform: rotateZ(0deg);
                -webkit-transform: rotateZ(0deg);
            }
            10% {
                transform: rotateZ(-30deg);
                -ms-transform: rotateZ(-30deg);
                -webkit-transform: rotateZ(-30deg);
            }
            20% {
                transform: rotateZ(15deg);
                -ms-transform: rotateZ(15deg);
                -webkit-transform: rotateZ(15deg);
            }
            30% {
                transform: rotateZ(-10deg);
                -ms-transform: rotateZ(-10deg);
                -webkit-transform: rotateZ(-10deg);
            }
            40% {
                transform: rotateZ(7.5deg);
                -ms-transform: rotateZ(7.5deg);
                -webkit-transform: rotateZ(7.5deg);
            }
            50% {
                transform: rotateZ(-6deg);
                -ms-transform: rotateZ(-6deg);
                -webkit-transform: rotateZ(-6deg);
            }
            60% {
                transform: rotateZ(5deg);
                -ms-transform: rotateZ(5deg);
                -webkit-transform: rotateZ(5deg);
            }
            70% {
                transform: rotateZ(-4.28571deg);
                -ms-transform: rotateZ(-4.28571deg);
                -webkit-transform: rotateZ(-4.28571deg);
            }
            80% {
                transform: rotateZ(3.75deg);
                -ms-transform: rotateZ(3.75deg);
                -webkit-transform: rotateZ(3.75deg);
            }
            90% {
                transform: rotateZ(-3.33333deg);
                -ms-transform: rotateZ(-3.33333deg);
                -webkit-transform: rotateZ(-3.33333deg);
            }
            100% {
                transform: rotateZ(0deg);
                -ms-transform: rotateZ(0deg);
                -webkit-transform: rotateZ(0deg);
            }
        }

        @-webkit-keyframes shake {
            0% {
                transform: rotateZ(0deg);
                -ms-transform: rotateZ(0deg);
                -webkit-transform: rotateZ(0deg);
            }
            10% {
                transform: rotateZ(-30deg);
                -ms-transform: rotateZ(-30deg);
                -webkit-transform: rotateZ(-30deg);
            }
            20% {
                transform: rotateZ(15deg);
                -ms-transform: rotateZ(15deg);
                -webkit-transform: rotateZ(15deg);
            }
            30% {
                transform: rotateZ(-10deg);
                -ms-transform: rotateZ(-10deg);
                -webkit-transform: rotateZ(-10deg);
            }
            40% {
                transform: rotateZ(7.5deg);
                -ms-transform: rotateZ(7.5deg);
                -webkit-transform: rotateZ(7.5deg);
            }
            50% {
                transform: rotateZ(-6deg);
                -ms-transform: rotateZ(-6deg);
                -webkit-transform: rotateZ(-6deg);
            }
            60% {
                transform: rotateZ(5deg);
                -ms-transform: rotateZ(5deg);
                -webkit-transform: rotateZ(5deg);
            }
            70% {
                transform: rotateZ(-4.28571deg);
                -ms-transform: rotateZ(-4.28571deg);
                -webkit-transform: rotateZ(-4.28571deg);
            }
            80% {
                transform: rotateZ(3.75deg);
                -ms-transform: rotateZ(3.75deg);
                -webkit-transform: rotateZ(3.75deg);
            }
            90% {
                transform: rotateZ(-3.33333deg);
                -ms-transform: rotateZ(-3.33333deg);
                -webkit-transform: rotateZ(-3.33333deg);
            }
            100% {
                transform: rotateZ(0deg);
                -ms-transform: rotateZ(0deg);
                -webkit-transform: rotateZ(0deg);
            }
        }

        /* конец кнопки звонка */
    </style> --}}

@endif
