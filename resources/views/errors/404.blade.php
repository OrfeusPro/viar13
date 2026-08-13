@php
    $title = trans('gl.err_title');
    $meta_desc = trans('gl.err_title');
    $creepingLine = collect();
@endphp

@extends(config('theme.resource') . '.layouts.app')

@section('content')
    <section class="section-frame not-found">
        <div class="not-found__inner">
            {{-- <div class="not-found__visual">
                <span class="not-found__code">404</span>
                <picture>
                    <img src="{{ asset('img/banner-img.png') }}" alt="@lang('settings.site_name')" loading="lazy">
                </picture>
            </div> --}}
            <div class="not-found__text">
                <img alt="ViarCanvas" style="max-width: 91%;display: block;margin: 0 auto;" src="https://viarcanvas.com/img/banner-img.png">
                <br>
                <h1 class="page-title">404 - @lang('gl.err_title')</h1>
                {{-- <p class="not-found__desc">
                    @lang('gl.err_link_text')
                </p> --}}
                <br>
                <div class="not-found__actions">
                    <a class="examples-slide__btn" href="{{ route('home') }}">
                        <span>@lang('gl.err_link_text')</span>
                        <i class="fa-arrow-next"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <style>
        .not-found {
            padding: 160px 0 160px;
            text-align: center;
        }

        .not-found__inner {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
            align-items: center;
        }

        .not-found__visual {
            position: relative;
            border-radius: 28px;
            overflow: hidden;
            background: linear-gradient(135deg, #fdf7f5 0%, #f0e5e6 100%);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.08);
            min-height: 260px;
        }

        .not-found__visual picture,
        .not-found__visual img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .not-found__code {
            position: absolute;
            top: 16px;
            left: 16px;
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 999px;
            font-weight: 700;
            font-size: 18px;
            color: #9c515b;
            letter-spacing: 1px;
        }

        .not-found__text {
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .not-found__desc {
            margin: 16px 0 32px;
            font-size: 18px;
            line-height: 1.6;
            color: #4a3b41;
        }

        .not-found__actions {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        @media (max-width: 900px) {
            .not-found {
                padding: 100px 0 120px;
            }

            .not-found__text {
                text-align: center;
            }

            .not-found__actions {
                justify-content: center;
            }
        }
    </style>
@endsection
