@include(config('theme.resource') . 'pages.review.breads')

<style>
	.stock-activation {
		min-height: 450px;
		background-image: url(../../images/stock/pngwing23_1.webp);
		background-color: #FBF2EA;
		background-repeat: no-repeat;
		background-size: contain;
	}

	.popup-stock {
		max-width: 681px;
		width: 100%;
		/* min-height: 638px; */
		padding: 47px 25px;
		border-radius: 8px;
		margin: auto;
		background: #FBF2EA;
		position: relative;
		background-image: url(../../images/stock/bg.webp);
		background-repeat: no-repeat;
		background-size: cover;
	}

	.popup-frame {
		z-index: 100;
	}

	.stock-activation .popup-stock__inner {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		height: 100%;
		text-align: center;
	}
</style>
<div class="main__review--screen">
	<div class="section-frame">
		<div class="main__review--inner">
			<div class="r-title-row">
				<div class="page-title r-title">
					@lang('pages.review_text1')
				</div>
			</div>
			<div class="r-block-row">
				<div class="r-images">
					<div class="r-images_row">
						<div class="r-images-block">
							<div class="r-images_title">
								<strong>@lang('pages.review_text14')</strong>
							</div>
							<div class="r-images_inner">
								<div class="r-img-wrap">
									<div class="r-img-t-row">
										<picture>
											<img width="50" height="50" src="{{ asset(config('theme.current') . '/images/review/client.svg') }}"
												alt="">
										</picture>
										<div class="r-img-info">
											<div class="r-img-name">
												@lang('pages.review_text25')
											</div>
											<div class="r-img-city">
												@lang('pages.review_text26')
											</div>
										</div>
									</div>
									<span>“</span>
									<div class="r-img-text">
										@lang('pages.review_text27')
									</div>
									<picture>
										<img width="256" height="201" src="{{ asset(config('theme.current') . '/images/review/1.svg') }}"
											alt="">
									</picture>
								</div>
							</div>
						</div>
						<div class="r-images-block">
							<div class="r-images_title">
								<strong> @lang('pages.review_text15')</strong>
								<span>
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd"
											d="M5.46667 5H5V5.46667V10.1333H5.93333V6.5933L9.80335 10.4633L10.4633 9.80335L6.5933 5.93333H10.1333V5H5.46667ZM18.5333 5H19V5.46667V10.1333H18.0667V6.5933L14.1966 10.4633L13.5367 9.80335L17.4067 5.93333H13.8667V5H18.5333ZM19 19H18.5333H13.8667V18.0667H17.4067L13.5367 14.1966L14.1966 13.5367L18.0667 17.4067V13.8667H19V18.5333V19ZM5.46667 19H5V18.5333V13.8667H5.93333V17.4067L9.80335 13.5367L10.4633 14.1966L6.5933 18.0667H10.1333V19H5.46667Z"
											fill="#FA7846" />
									</svg>
									30х40см
								</span>
							</div>
							<div class="r-images_inner r-image_right">
								<div class="r-img-wrap">
									<picture>
										<img width="256" height="201" src="{{ asset(config('theme.current') . '/images/review/2.svg') }}"
											alt="">
									</picture>
								</div>
							</div>
						</div>
					</div>
				</div>


				<form method="post" id="review" class="r-form" action="{{ route('review.store') }}"
					enctype="multipart/form-data">
					@csrf
					<input type="hidden" id="audioData" name="audioData">

					<div class="input-item">
						<label for="review">@lang('pages.review_text0')</label>
						<div class="textarea-wrapper">
							<textarea required name="text"></textarea>
						</div>
					</div>
					<div class="input-item">
						<label for="name">@lang('pages.review_text3')</label>
						<div class="input-wrapper">
							<input name="name" type="text" value="{{ old('name', auth()->check() ? auth()->user()->first_name : '') }}" required>
						</div>
					</div>
					<div class="input-item">
						<label for="email">@lang('pages.review_text4')</label>
						<div class="input-wrapper">
							<input name="email" type="email" value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}" required>
						</div>
					</div>
					<div class="input-item">
						<label for="photo-order">@lang('pages.review_text5')</label>
						<div class="file-save file-save__popup">
							<div class="file-save__item js-file-preview">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . '/sprite.svg') }}#save"></use>
								</svg>
								<div class="file-save__title">
									@lang('pages.review_text6')
								</div>
							</div>
							<div class="file-save__item js-file-upload js-file-multiple">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . '/sprite.svg') }}#check"></use>
								</svg>
								<div class="file-save__title">
									<p class="file-title_green">@lang('pages.review_text7')</p>
								</div>
							</div>
							<input type="file" class="file-input custom-in" name="file[1]" accept="image/*,image/heif,image/heic"
								aria-label="file input">
						</div>
					</div>
					<div class="input-item">
						<label for="ava">@lang('pages.review_text8')</label>
						<span>@lang('pages.review_text9')</span>
						<div class="file-save file-save__popup">
							<div class="file-save__item js-file-preview">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . '/sprite.svg') }}#save"></use>
								</svg>
								<div class="file-save__title">
									@lang('pages.review_text6')
								</div>
							</div>
							<div class="file-save__item js-file-upload js-file-multiple">
								<svg>
									<use xlink:href="{{ asset(env('THEME') . '/sprite.svg') }}#check"></use>
								</svg>
								<div class="file-save__title">
									<p class="file-title_green">@lang('pages.review_text7')</p>
								</div>
							</div>
							<input type="file" class="file-input custom-in" name="file[2]" accept="image/*,image/heif,image/heic"
								aria-label="file input">
						</div>
					</div>
					<div class="input-item">
						<label for="audio">@lang('pages.review_text10') </label>
						<div class="r-file-container r-file-container_audio">
							<div class="input-file_wrapper input-audio_wrapper">
								<div class="input-audio-w w-on">
									@lang('pages.review_text11')
									<svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
										<g clip-path="url(#clip0_110_380)">
											<path
												d="M14.0039 0.0879211C12.1113 0.433624 10.5352 1.62894 9.69727 3.36331C9.36914 4.03714 9.2168 4.6055 9.14063 5.41995C9.06445 6.26956 9.06445 15.1172 9.14063 15.9492C9.28125 17.4258 9.84961 18.6387 10.8867 19.67C13.752 22.5176 18.5332 21.6797 20.3027 18.0235C20.6309 17.3496 20.7832 16.7813 20.8594 15.9668C20.9356 15.1114 20.9356 6.27542 20.8594 5.41995C20.7832 4.6055 20.6309 4.03714 20.3027 3.36331C19.1484 0.978546 16.5527 -0.36911 14.0039 0.0879211Z"
												fill="white" />
											<path
												d="M6.22844 14.4727C5.83586 14.5664 5.54875 14.8359 5.44328 15.2051C5.36125 15.5039 5.46086 16.7812 5.63078 17.5488C6.05266 19.4824 7.10735 21.3223 8.52532 22.5938C9.94914 23.877 11.6659 24.7031 13.4765 24.9844L13.9159 25.0547L13.9335 26.4727L13.9452 27.8906H11.912C10.0546 27.8906 9.84953 27.9023 9.63274 28.002C8.85344 28.3594 8.84758 29.5312 9.62688 29.8887C9.86125 29.9941 10.0898 30 14.9999 30C19.9101 30 20.1386 29.9941 20.373 29.8887C21.1523 29.5312 21.1464 28.3594 20.3671 28.002C20.1503 27.9023 19.9452 27.8906 18.0878 27.8906H16.0546L16.0663 26.4727L16.0839 25.0547L16.5234 24.9844C20.3906 24.3867 23.5195 21.416 24.3691 17.5488C24.539 16.793 24.6386 15.5039 24.5566 15.2051C24.4863 14.9355 24.2812 14.6953 24.0058 14.5547C23.7714 14.4316 23.2851 14.4258 23.0566 14.5488C22.6464 14.7598 22.5292 15.0352 22.4472 15.9668C22.2773 17.9766 21.5683 19.5059 20.1738 20.8711C17.3261 23.6543 12.6738 23.6543 9.8261 20.8711C8.43157 19.5059 7.72258 17.9766 7.55266 15.9668C7.48821 15.2285 7.41789 14.9766 7.21282 14.7422C7.00774 14.5137 6.56828 14.3906 6.22844 14.4727Z"
												fill="white" />
										</g>
										<defs>
											<clipPath id="clip0_110_380">
												<rect width="30" height="30" fill="white" />
											</clipPath>
										</defs>
									</svg>
								</div>
								<div class="input-audio-w w-off">
									@lang('pages.review_text12')
									<svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path
											d="M13.916 1.916C8.51951 2.38475 3.8906 6.15819 2.41404 11.3086C2.0156 12.6855 1.90427 13.4941 1.91013 15.0293C1.91013 16.4883 2.00388 17.209 2.36716 18.5273C3.33396 22.043 5.83005 25.0722 9.12888 26.7422C10.1718 27.2695 11.6308 27.7324 12.9492 27.9609C13.9453 28.1308 15.9433 28.1484 16.9043 27.9902C19.8984 27.498 22.4062 26.2031 24.4394 24.0996C26.0215 22.459 27.1289 20.4844 27.6855 18.3164C28.0195 16.9922 28.0957 16.4121 28.0898 14.9707C28.0898 13.5762 27.9961 12.832 27.6855 11.6367C26.5605 7.30663 23.1328 3.75584 18.7969 2.43748C17.3496 1.99217 15.3984 1.78709 13.916 1.916ZM20.625 15V20.625H15H9.37497V15V9.37498H15H20.625V15Z"
											fill="white" />
									</svg>
								</div>
							</div>
							<div class="r-audio_box">
								<div class="r-audio_inn"></div>
								<div class="r-audio_remove">
									<svg fill="red" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24px" height="24px">
										<path
											d="M 10 2 L 9 3 L 3 3 L 3 5 L 21 5 L 21 3 L 15 3 L 14 2 L 10 2 z M 4.3652344 7 L 5.8925781 20.263672 C 6.0245781 21.253672 6.877 22 7.875 22 L 16.123047 22 C 17.121047 22 17.974422 21.254859 18.107422 20.255859 L 19.634766 7 L 4.3652344 7 z" />
									</svg>
								</div>
							</div>
						</div>
						<div class="recording-animation">
							<span class="time">
								0:00
							</span>
							<div class="rec-animation--inner">
								<div>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
									<span></span>
								</div>
							</div>
						</div>
					</div>
					<div class="input-item">
						<button class="r-btn r-submit {{ auth()->guest() ? 'js-popup-login' : '' }}" type="submit">@lang('pages.review_text13')</button>
					</div>
				</form>

			</div>
		</div>
	</div>
</div>




<!--  -->

<section class="bonus__screen">
	<div class="section-frame">
		<div class="bonus__screen--inner">
			<div class="bonus-subtitle">
				@lang('pages.review_text17')
			</div>
			<div class="bonus__row">
				<div class="b-image">
					<img width="494" height="505" src="{{ asset(config('theme.current') . '/images/review/3.svg') }}"
						alt="">
				</div>
				<div class="b-soc">
					<a href="{{ setting('sots-seti.facebook') }}" class="b-soc-item" target="_blank">
						<img width="80" height="80" src="{{ asset(config('theme.current') . '/images/review/f.svg') }}"
							alt="">
						<p>@lang('pages.review_text18')</p>
					</a>
					<a href="{{ setting('sots-seti.google') }}" class="b-soc-item" target="_blank">
						<img width="80" height="80" src="{{ asset(config('theme.current') . '/images/review/g.svg') }}"
							alt="">
						<p>@lang('pages.review_text18')</p>
					</a>
				</div>
				<div class="b-content">
					<div class="b-content_inner hb_text_thanks">
						@lang('pages.review_text30')
					</div>
				</div>
			</div>
		</div>
	</div>
</section>


<!--  -->
<div class="ellipse ellipse_black ellipse_top">
	<img src="{{ asset(config('theme.current') . '/images/icon/ellipse-black.svg') }}" alt="img" loading="lazy">
</div>

<div class="only__screen">
	<div class="section-frame">
		<div class="only__screen--inner">
			<div class="page-title only-title">
				@lang('pages.review_text21') </span>
			</div>
			<div class="only-row">
				<div class="only-item">
					<div class="o-box">
						<img src="{{ asset(config('theme.current') . '/images/review/o1.svg') }}" alt="">
					</div>
					<p>
						@lang('pages.review_text22')
					</p>
				</div>
				<div class="only-item">
					<div class="o-box">
						<img src="{{ asset(config('theme.current') . '/images/review/o2.svg') }}" alt="">
					</div>
					<p>
						@lang('pages.review_text23')
					</p>
				</div>
				<div class="only-item">
					<div class="o-box">
						<img src="{{ asset(config('theme.current') . '/images/review/o3.svg') }}" alt="">
					</div>
					<p>
						@lang('pages.review_text24')
					</p>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="ellipse ellipse_black">
	<img src="{{ asset(config('theme.current') . '/images/icon/ellipse-black.svg') }}" alt="img" loading="lazy">
</div>

@include(config('theme.resource') . 'partials.google_reviews_section')

<div class="vz-art target-frame popup-frame success-frame" style="z-index: 100;">
	<div class="vz-art js-popup target-box popup-stock stock-a-js stock-activation"
		style="align-items: center; justify-content: center; ">
		<div class="popup-stock__inner">
			<i class="vz-art fa-close popup-close"></i>
			<div class="ps-title">
				@lang('pages.review_text28')</div>
		</div>
	</div>
</div>

@include(config('theme.resource') . 'pages.index.faq9')

<?php
// Check if jQuery is already included
if (!function_exists('jquery_is_loaded')) {
    function jquery_is_loaded()
    {
        return function_exists('wp_enqueue_script') && wp_script_is('jquery', 'done');
    }
}

// Include jQuery if it's not already loaded
if (!jquery_is_loaded()) {
    echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>';
}
?>

{{-- -------------------------- --}}
<script src="{{ ver_asset(config('theme.current') . 'js/record.js') }}"></script>

<script>
	$(document).ready(function() {
		$('#review').submit(function(event) {

			event.preventDefault(); // Prevent form submission

			// Serialize the form data
			// var formData = $(this).serialize();
			var formData = new FormData(this);
			console.log(formData);
			// Make an AJAX request
			$.ajax({
				url: $(this).attr('action'), // Form action URL
				type: $(this).attr('method'), // Form method (e.g., 'post')
				data: formData, // Form data with file inputs
				processData: false, // Prevent jQuery from processing the data
				contentType: false, // Prevent jQuery from setting the content t
				success: function(response) {
					if (response == 'success') {
						$(".stock-screen-bonus").fadeOut(0);
						$(".success-frame").css("display", "flex").hide().fadeIn();
						$(".stock-activation").css("display", "flex").hide().fadeIn();
					}

				},
				error: function(xhr, status, error) {
					// Handle the error response
					console.log(xhr.responseText);
					// Optionally, perform error handling or display an error message
				}
			});

		});
	});
</script>
