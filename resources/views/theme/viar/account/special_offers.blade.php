@if (Auth::user()->role->name != 'painter')
<section class="portraits-examples"  id="top_mail_section">
	<div class="p-examples-top">
		<div class="section-frame">
			<div class="top-title">
				<div class="page-title h2_old">@lang("account_new.special_offers.title")</h2>
			</div>
			<div class="p-example-slider--wrapper">
				<div class="examples-slider p-examples-slider">

					@foreach ($top_mail as $element)
						<div class="examples-slide">
							<a href="{{ asset(config('theme.current') . '/images')}}/portrait-1.jpg" class="examples-slide__photo" data-fancybox="examples"
								aria-label="examples slide link">
								<picture>
									<source srcset="{{ $images_folder . $element->image }}" type="image/jpeg">
									<img width="120px" height="86px" src="{{ $images_folder . $element->image }}" alt="Viar">
								</picture>
							</a>
							<a href="#" class="examples-slide__title">{{ $element->value }}</a>
							<div class="examples-size">
								<svg class="happy-item__icon">
									<use xlink:href="{{ asset(env('THEME').'sprite.svg#size') }}"></use>
								</svg>
								<p>{{ $element->size }}</p>
							</div>

							<div class="example-price">
								@if ($element->sale_price != 0)
									@php $total_price = $element->sale_price*$multiplyer @endphp

									<span class="newP">
										{{ $element->sale_price * $multiplyer }}€
									</span>
									<span class="oldP">
										{{ $element->price * $multiplyer }}€
									</span>
								@else
									@php $total_price = $element->price*$multiplyer @endphp
									<span class="newP">
										{{ $element->price * $multiplyer }}€
									</span>
								@endif
							</div>

							<a href="#" class="examples-slide__btn top-mail-button" data-price="<?= $total_price ?>"
								data-size="<?= $element->size ?>" data-name="<?= $element->value ?>" data-catid="<?= $element->cat_id ?>">
								@lang('stock.topmail_buy_button')
							</a>

						</div>
					@endforeach

				</div>
				<div class="examples-arrow">
					<a href="#" class="examples-prev c-ex-prev" aria-label="examples prev">
						<i class="fa-arrow-prev"></i>
					</a>
					<a href="#" class="examples-next c-ex-next" aria-label="examples next">
						<i class="fa-arrow-next"></i>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>
@endif
