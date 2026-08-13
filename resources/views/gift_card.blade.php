@extends('layots.common')

@section('title', $data['meta_title'])

@section('og_tags')
    <meta property="og:title" content="{{ $data['meta_title'] }}" />
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/gift-card.css') }}">
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css">
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    @php
    $date_form_now = Carbon::now();
    @endphp
    <script>
        $(document).ready(function() {
            var mnths = '{{ trans('gl.picker_mnth') }}';
            var days = '{{  trans('gl.picker_days') }}';

            $('.js_date').datepicker({
                dateFormat: 'mm/dd/yy',
                minDate: {{ $date_form_now->format('d/m/Y') }},
                monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август',
                    'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
                ],
                dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
            });
        });

    </script>
@endsection

@section('content')
    <link rel="stylesheet" href="{{ asset('css/gift-card.css') }}">

    <div class="bread-crumbs">
        <i class="icon-icon3"></i>
        <ul vocab="https://schema.org/" typeof="BreadcrumbList">
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage" href="{{ url('/') }}">
                    <span property="name">@lang('account.index1')</span></a>
                <meta property="position" content="1">
            </li>
            <li property="itemListElement" typeof="ListItem">
                <span property="name">{{ $data['title'] }}</span>
                <meta property="position" content="2">
            </li>
        </ul>
    </div>

    <section class="gift-card">
        <div class="title">
            <h2>{{ $data['title'] }}</h2>
        </div>
        <div class="title-p">
            {!! $data['desc'] !!}
        </div>
        <div class="gift-card-content">
            <div class="card clearfix">
                <div class="card-item">
                    <img src="{{ asset('img/front_') . app()->getLocale() . '.png' }}" alt="">
                </div>
                <div class="card-item">
                    <img src="{{ asset('img/back_') . app()->getLocale() . '.png' }}" alt="">
                </div>
            </div>
            <div class="card-form">
                <form class="js_gift_form" data-name="{{ $data['title'] }}">
                    <div class="form-content clearfix">
                        <div class="form-item">
                            <div class="input">
                                <div class="select clearfix">
                                    <div>
                                        <label>{!! $data['nom_title'] !!}</label>
                                        <select class="input-select js_summ"
                                            data-jcf='{"wrapNative": false, "wrapNativeOnMobile": false, "fakeDropInBody": false, "useCustomScroll": false}'>
                                            @foreach ($noms as $nom)
                                                <option>{{ $nom['text'] }}</option>
                                            @endforeach
                                            <option value="0">{{ $data['custom_summ'] }}</option>
                                        </select>
                                        <input class="input-sum js_custom_summ" type="text">
                                        <i>{{ $data['or_enter_custom_summ'] }}</i>
                                    </div>
                                    <div class="noms__list">
                                        <label>{{ $data['when_send_title'] }}</label>
                                        @php
                                            $date_form_now = Carbon::now();
                                        @endphp
                                        <input class="js_date" autocomplete="off" required type="text" name="when_send">
                                    </div>
                                </div>
                                <div class="input-c">
                                    <label>{{ $data['hide_nominal'] }}</label>
                                    <input type="checkbox" name="hide_nom" class="js_hide_nom">
                                </div>
                            </div>
                            <div class="input-name clearfix">
                                <div>
                                    <label>{{ $data['sender'] }}</label>
                                    <input required type="text" placeholder="" class="js_sender">
                                    <i>{{ $data['from_title'] }}</i>
                                </div>
                                <div>
                                    <label>{{ $data['receiver'] }}</label>
                                    <input required type="text" placeholder="" class="js_receiver">
                                    <i>{{ $data['for_title'] }}</i>
                                </div>
                            </div>
                            <div class="checkbox">
                                <label>{{ $data['card_el_type_title'] }}</label>
                                <input name="el_type" value="{{ $data['card_el_type_title'] }}" type="radio" checked>
                            </div>
                            <div class="checkbox">
                                <label>{{ $data['card_conv_title'] }}</label>
                                <input name="el_type" value="{{ $data['card_conv_title'] }}" type="radio">
                            </div>
                        </div>
                        <div class="form-item">
                            <div class="input">
                                <label>{{ $data['torj_title'] }}</label>
                                <input required type="text" class="js_torj_title">
                                <i>{{ $data['torj_desc_title'] }}</i>
                            </div>
                            <div class="input">
                                <label>{{ $data['grats_text'] }}</label>
                                <textarea required class="js_torj_text"></textarea>
                            </div>
                        </div>
                    </div>
                    <button><span>{{ $data['of_pol_title'] }}</span></button>
                    {!! $data['of_opl_after_text'] !!}
                </form>
            </div>
        </div>
    </section>

    <section class="benefits-card">
        <div class="title">
            <h2>{!! $data['adv_title'] !!}</h2>
        </div>
        <div class="benefits-content">
            <div class="benefits-items clearfix">
                <div class="benefits-item">
                    <div class="text">
                        <img src="{{ asset('img/benefits-img1.png') }}" alt="">
                        <p>{!! $data['adv1_text'] !!}</p>
                    </div>
                </div>
                <div class="benefits-item">
                    <div class="text">
                        <img src="{{ asset('img/benefits-img2.png') }}" alt="">
                        <p> {!! $data['adv2_text'] !!}</p>
                    </div>
                </div>
                <div class="benefits-item">
                    <div class="text">
                        <img src="{{ asset('img/benefits-img3.png') }}" alt="">
                        <p> {!! $data['adv3_text'] !!}</p>
                    </div>
                </div>
                <div class="benefits-item">
                    <div class="text">
                        <img src="{{ asset('img/benefits-img4.png') }}" alt="">
                        <p> {!! $data['adv4_text'] !!}</p>
                    </div>
                </div>
                <div class="benefits-item">
                    <div class="text">
                        <img src="{{ asset('img/benefits-img5.png') }}" alt="">
                        <p> {!! $data['adv5_text'] !!}</p>
                    </div>
                </div>
                <div class="benefits-item">
                    <div class="text">
                        <img src="{{ asset('img/benefits-img6.png') }}" alt="">
                        <p> {!! $data['adv6_text'] !!}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="rules-card">
        <div class="title">
            <h2>{!! $data['rules_title'] !!}</h2>
        </div>
        <div class="rules-content">
            {!! $data['rules_list'] !!}
        </div>
    </section>

    <script src="{{ asset('js/jcf.min.js') }}"></script>
    <script src="{{ asset('js/jcf.radio.min.js') }}"></script>
    <script src="{{ asset('js/jcf.select.min.js') }}"></script>
    <script src="{{ asset('js/jcf.checkbox.min.js') }}"></script>
    <script src="{{ asset('js/gift-card.min.js') }}"></script>
@endsection
