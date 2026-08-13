<!DOCTYPE html>

@if (Config::get('app.locale') == 'ee')
	<html lang="et">
@else
	<html lang="{{ Config::get('app.locale') }}">
@endif

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{{ $data['meta_title'] }}</title>
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<meta name="description" content="{{ $data['meta_desc'] }}">
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon.png') }}">
	<meta name="cmsmagazine" content="5a7189a28fb3dc68238dbdb44c9ea99c" />
	<meta name="verify-paysera" content="a25580ff84c541195306e67d151dbb58">
	<link rel="canonical" href="{{ url(Request::url()) }}" />

	<link rel="stylesheet" href="{{ ver_asset('css/style.min.css') }}" media="all">
	<link rel="stylesheet" href="{{ ver_asset('style/main.min.css') }}" media="all" onload="this.media='all'">
	<link rel="stylesheet" href="{{ ver_asset(env('THEME') . 'style/custom.css') }}">
	<link rel="stylesheet" href="{{ ver_asset(env('THEME') . 'style/add.css') }}">
	<link rel="stylesheet" href="{{ ver_asset(env('THEME') . 'style/cart.min.css') }}" /> <!-- main menu 1 -->
	<link rel="stylesheet" href="{{ ver_asset(env('THEME') . 'style/home-media.css') }}">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"
		integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

	<script src="{{ ver_asset(env('THEME') . 'js/script.js') }}" defer=""></script>
	<script src="{{ ver_asset(env('THEME') . 'js/custom.js') }}" defer=""></script>
	<script src="{{ ver_asset(env('THEME') . 'js/add.js') }}" defer=""></script>

	<script defer src="{{ ver_asset(env('THEME') . 'js/cart.min.js') }}"></script> <!-- main menu 1 -->

	{!! $trackers !!}
	<link rel="stylesheet" href="{{ ver_asset(env('THEME') . 'style/overall/overall.css') }}">
	<style>
		select.vz-art.lang__select.js__lang {
			width: 100%;
		}

		.popup {
			opacity: 0;
			pointer-events: none;
		}

		.language {
			margin-left: 16px;
			position: relative
		}

		.f__errs {
			width: 100%;
			display: flex;
			flex-wrap: wrap;
			justify-content: end;
			color: #111;
			margin: 20px 0;
			text-align: right;
		}

		.f__errs ul {
			list-style: none;
		}

		.f__errs li {
			display: inline-block;
		}

		.language p {
			border-radius: 8px;
			background: #fff;
			display: flex;
			align-items: center;
			font-size: 15px;
			opacity: .8;
			padding: 10px;
			min-width: 50px
		}

		.language i {
			margin-left: 5px;
			font-size: 6px
		}

		.language ul {
			width: 100%;
			padding: 10px;
			display: none;
			box-sizing: border-box;
			border-radius: 0 0 8px 8px;
			background-color: #fff;
			position: absolute;
			left: 0;
			top: 90%;
			font-size: 15px;
			line-height: 18px
		}

		.language li {
			margin-bottom: 10px
		}

		.language li:last-child {
			margin-bottom: 0
		}

		.language a {
			color: rgba(35, 41, 55, .75);
			display: flex
		}

		.language img {
			width: 16px;
			height: 14px;
			object-fit: contain;
			margin-right: 4px
		}
/* 
        .br-object {
            position: absolute;
            top: 181px;
            left: 0;
            width: 100%;
            z-index: 8;
        } */

        .first {
            padding-top: 95px;
            padding-bottom: 35px;
            margin-bottom: 69px;
            background: url(../img/head-bg.svg) center center / cover no-repeat;
        }

        .breadcrumbs {
            margin-bottom: 25px;
        }

	</style>
</head>

<body>
	@include('partials.socials.fb')

	@widget('Header')
	{{--
		@include('partials.index_new.header0', ['is_all_styles' => 1])
        @include('partials.index_new.header1', ['is_all_styles' => 1])
		--}}

	@if(filter_var(setting('site.top_sale', false), FILTER_VALIDATE_BOOLEAN))
	<div class="top-sale">
		<img width="77" height="66" src="{{ asset(env('THEME') . 'images/sale.webp') }}" alt="">
		<p>@lang('header_footer_new.header.top_sale')</p>
	</div>
	@endif
	<div class="wrapper">
		<main class="main">

			<div class="first">
				<div class="section-frame">
					<div class="breadcrumbs">
						<div class="breadcrumbs__block">
							<a href="{{ route('home') }}" class="breadcrumbs__link breadcrumbs__link_main">
								@lang('account.index1')
							</a>
							<img src="{{ asset('img/icons/angle-arrow.svg') }}" alt="" class="img-svg breadcrumbs__arrow">
							<a href="#" class="breadcrumbs__link">
								{!! $data['title'] !!}
							</a>
						</div>
					</div>
					<h1 class="first__title">
						{!! $data['title'] !!}
					</h1>
					<h2 class="first__subtitle">
						{{ $data['desc'] }}
					</h2>
				</div>
			</div>


			<div class="arts">
				<div class="section-frame">
					<div class="arts__block">
						@include('partials.all_styles.top_items')
					</div>
				</div>
			</div>
			<div class="banner">
				<div class="section-frame">


					@include('partials.index_new.why7_form')
					{{-- @include('partials.all_styles.center_form') --}}



				</div>
			</div>
			<div class="arts">
				<div class="section-frame">
					<div class="arts__block">
						@include('partials.all_styles.center_items')
					</div>
				</div>
			</div>
			@include('partials.all_styles.bot_form')
			<div class="arts arts_more">
				<div class="section-frame">
					<div class="arts__block">
						@include('partials.all_styles.bot_items')
					</div>
					<button class="arts__more">
						{{ $data['show_more_text'] }}
					</button>
				</div>
			</div>
			<div class="quiz-modal">
				<div class="quiz-modal__block">
					<div class="quiz-modal__error">
						<img src="{{ asset('img/icons/error.svg') }}" alt="" class="img-svg">
					</div>
					<div class="quiz-modal__title">
						{!! $bot_form['no_select'] !!}
					</div>
					<div class="quiz-modal__btn">
						ОК
					</div>
				</div>
			</div>
		</main>
		@include('partials.all_styles.footer')
	</div>
    
	{{-- Успешно отправдено. --}}

	{{--  @include('partials.all_styles.modals') --}}
	<script>
		setTimeout(function() {
			const elem = document.createElement('link');
			elem.type = 'text/css';
			elem.rel = 'stylesheet';
			elem.href = '/theme/viar/style/intlTelInput.min.css';
			document.body.appendChild(elem);

		}, 2000);
	</script>
	<script src="{{ ver_asset(env('THEME') . 'js/intlTelInput.min.js') }}"></script>
	<script src="{{ ver_asset(env('THEME') . 'js/main.js') }}"></script>

	<link rel="stylesheet" href="{{ ver_asset('css/forms.css') }}">
	<link rel="stylesheet" href="{{ ver_asset('css/forms2.css') }}">
	{{-- <link rel="stylesheet" href="{{ ver_asset('css/media_new.css') }}"> --}}
	@if (Session::has('success_photo'))
		<script>
			document.querySelector(".js_pop_photo_form").classList.add("active");
			let close_mod_btn = document.querySelector(".close-content");
			let modal = document.querySelector(".popup");

			close_mod_btn.addEventListener("click", function(e) {
				for (var i = 0; i < modal.length; i++) {
					elems[i].classList.remove("active");
				}
			}, false);
		</script>
	@endif


	@if (Session::has('success_bot_form'))
		<script>
			document.querySelector(".quiz-thanks").classList.add("quiz-thanks__show");
			document.getElementById('q__form').scrollIntoView();
		</script>
	@endif
	<script>
		function footerAccordion() {
			if (document.documentElement.clientWidth < 768) {
				$(document).ready(function() {
					$('.footer__title').click(function() {
						$(this).toggleClass('ins').next().slideToggle(500);
					});
				});
			}
		}
		footerAccordion();
	</script>

	@if (Session::has('success_photo'))
		<script>
			$('.js_pop_photo_form').addClass('active');

			$(".close-content, .n-p-btn-back").on("click", function() {
				$(".popup").removeClass("active");
			});
		</script>
	@endif
</body>

</html>
