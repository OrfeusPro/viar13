@extends('layots.common')
@section('title', $data['meta_title'])

@section('og_tags')
    <meta property="og:title" content="{{ $data['meta_title'] }}" />
    <meta property="og:image" content="{{ \URL::to('/') }}/img/logo.png" />
@endsection

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/basket.css') }}">
    <script src="{{ asset('js/jquery3.min.js') }}"></script>
@endsection

<style>
    .contacts {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media(min-width:1024px) {
        .contacts {
            display: block;
            height: auto;
            margin-top: 300px;
            margin-bottom: 300px;
        }
    }

    .office.off {
        display: none;
    }

    .sbm__total {
        z-index: 1;
        color: #ffffff;
        font-size: 16px;
        font-weight: 400;
        line-height: 18px;
        position: relative;
        padding: 20px 55px;
        display: inline-block;
        letter-spacing: 0.25px;
        border: none;
        background: #ff8340;
        text-transform: uppercase;
        text-shadow: 1px 1px 4px #c05d1d;
        transition: all 0.5s;
    }

</style>

@if (\Session::has('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementsByClassName('popup-rev-ok')[0];
            el.classList.add('active');
        }, false);
    </script>
@endif

@section('content')
    <section class="checkout">
        <div class="container">
            <div class="checkout-content">
                <div class="checkout-form">
                    <br><br><br>
                    <h1 style="text-align: center;font-size: calc(4rem - 15px);">{{ $data['meta_title'] }}</h1>
                    <br><br>
                    @auth
                        <form action="{{ route('user_send_rev', app()->getLocale()) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="input">
                                <label>
                                    <span>{{ $data['u_name'] }}</span>
                                    <input required type="text" placeholder="{{ $data['u_name'] }}" name="name">
                                </label>
                            </div>
                            <div class="input">
                                <label>
                                    <span>{{ $data['u_mail'] }}</span>
                                    <input required type="email" placeholder="{{ $data['u_mail'] }}" name="email"
                                        value="{{ Auth::user()->email }}">
                                </label>
                            </div>

                            <div class="input">
                                <label>
                                    <span>{{ $data['u_ava'] }}</span>
                                    <input required type="file" placeholder="{{ $data['u_ava'] }}" name="avatar_photo"
                                        accept="image/*,image/heif,image/heic">
                                </label>
                            </div>

                            <div class="input">
                                <label for="">
                                    <span>{{ $data['u_promo'] }}</span>
                                    <input required type="file" placeholder="{{ $data['u_promo'] }}" name="promo_foto"
                                        accept="image/*,image/heif,image/heic">
                                </label>
                            </div>

                            <div class="input">
                                <label for="">
                                    <span>{{ $data['u_audio'] }}</span>
                                    <input type="file" placeholder="{{ $data['u_audio'] }}" name="audio_file"
                                        accept="audio/*">
                                </label>
                            </div>

                            <div class="textarea">
                                <label for="">
                                    <span>{{ $data['u_text'] }}</span>
                                    <textarea required name="review_text" id="" cols="30" rows="10"></textarea>
                                </label>
                            </div>
                            <button class="sbm__total" type="submit">{{ $data['u_send'] }}</button>
                        </form>
                    @else
                        <p>{{ $data['need_reg'] }}
                            <a style="display:inline-block;color:#ad692d;" href="javascript:void(0)"
                                class="office off">{{ __('header.account_form_button') }}</a>
                        </p>
                    @endauth
                </div>
            </div>
        </div>
    </section>
@endsection
