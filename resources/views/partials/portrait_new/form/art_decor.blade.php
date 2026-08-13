<div class="formalization-item">
	<div class="formalization-box">
		<div class="formalization-tab">
			<p>{!! trans('portrait_buy_form.step6_title') !!}</p>
			<span class="tab-icon"></span>
		</div>
		<div class="formalization-content">
			<div class="formalization-content--inner">
				<div class="kviz-row-single kviz-c-group">
					<div class="kviz-group__item" style="width: fit-content;">
						@if (isset($art_items))
							@foreach ($art_items as $item)
								<div
									class="js_decor__item kv__size__item kviz-radio js-checkbox @if ($loop->last) kviz-radio_active @endif"
									data-stock="1">
									<div class="check"></div>
									<label>
										<span>{{ $item->getTranslatedAttribute('name', app()->getLocale()) }}
											<img src="{{ asset('images/icon/info.svg') }}" alt="">
										</span>

										<input style="height: 0px;" @if ($loop->last) checked @endif class="js_art_decor"
											type="radio" name="tools" data-name="{{ $item->getTranslatedAttribute('name', app()->getLocale()) }}"
											data-id="{{ $item->id }}" data-coef_sm="{{ $item->coef_sm }}" data-coef_md="{{ $item->coef_md }}"
											data-coef_lg="{{ $item->coef_lg }}" data-id="{{ $item->id }}" value="{{ $item->price }}" />

										@if ($item->image)
											<div class="pic-pop">
												<picture>
													@php
														$portraitNewArtDecorImageSources = image_picture_sources(data_get($item, 'image'), true);
													@endphp
													@if(!empty($portraitNewArtDecorImageSources['src_webp']))
														<source srcset="{{ $portraitNewArtDecorImageSources['src_webp'] }}" type="image/webp">
													@endif
													@if(!empty($portraitNewArtDecorImageSources['src']) && !empty($portraitNewArtDecorImageSources['type']))
														<source srcset="{{ $portraitNewArtDecorImageSources['src'] }}" type="{{ $portraitNewArtDecorImageSources['type'] }}">
													@endif
													<img loading="lazy" src="{{ $portraitNewArtDecorImageSources['src'] }}" @altAttrs($item, 'image', data_get($item, 'image'))>
												</picture>
												<div class="hint">{!! $item->getTranslatedAttribute('hint', app()->getLocale()) !!}</div>
											</div>
										@endif
									</label>
									{!! sale_icon($size_item) !!}
								</div>
							@endforeach
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="formalization-prompt">
		<div class="formalization-prompt--wrapper">
			<div class="formalization-prompt--inner">
				<img src="{{ asset('images/prompt6.png') }}" alt="" />
				<p>
					{!! trans('portrait_buy_form.step6_bot_desc') !!}
				</p>
			</div>
		</div>
	</div>
</div>
