<div class="formalization-item">
	<div class="formalization-box">
		<div class="formalization-tab">
            <p>{!! trans('portrait_buy_form.step5_title') !!}</p>
			<span class="tab-icon"></span>
		</div>
		<div class="formalization-content">
			<div class="formalization-content--inner">
				<div class="kviz-row-single kviz-c-group">
					<div class="kviz-group__item" style="width: fit-content;">
						@if (isset($canvas_items))
							@foreach ($canvas_items as $item)
                                <div class="kviz-radio js_canvas_type_item js-checkbox @if($item->default) kviz-radio_active @endif" data-stock="{{ $loop->iteration }}" data-price="{{ $item->price }}" data-id="{{ $item->id }}">
{{--								<div class="js_canvas_type_item kv_size_item kviz-radio js-checkbox @if ($item->default) kviz-radio_active @endif" data-stock="{{ $loop->iteration }}"  data-price="{{ $item->price }}">--}}
									<div class="check"></div>
									<label>
										<span>{{ $item->getTranslatedAttribute('name', app()->getLocale()) }} {{ $item->getTranslatedAttribute('density', app()->getLocale()) }}
											<img src="{{ asset('images/icon/info.svg') }}" alt="">
										</span>
										<input style="height: 0px;" @if ($item->default) checked @endif
                                        class="js_canvas_type"
											type="radio" name="canvas_type"
                                               data-name="{{ $item->getTranslatedAttribute('name', app()->getLocale()) }} "
                                               data-price="{{ $item->price }}"
											data-id="{{ $item->id }}"  value="{{ $item->price }}" />
										@if ($item->image)
											<div class="pic-pop">
												<picture>
													@php
														$portraitNewCanvasImageSources = image_picture_sources(data_get($item, 'image'), true);
													@endphp
													@if(!empty($portraitNewCanvasImageSources['src_webp']))
														<source srcset="{{ $portraitNewCanvasImageSources['src_webp'] }}" type="image/webp">
													@endif
													@if(!empty($portraitNewCanvasImageSources['src']) && !empty($portraitNewCanvasImageSources['type']))
														<source srcset="{{ $portraitNewCanvasImageSources['src'] }}" type="{{ $portraitNewCanvasImageSources['type'] }}">
													@endif
													<img loading="lazy" src="{{ $portraitNewCanvasImageSources['src'] }}" @altAttrs($item, 'image', data_get($item, 'image'))>
												</picture>

											</div>
										@endif
									</label>
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
