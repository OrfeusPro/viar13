{{-- @if(Auth::user()->role->name == 'painter') --}}

@php
	$pimg = preg_replace("/\s+/", "", $order->painter_sketch_images);
	$painter_sketch_images = trim($pimg, ',');
	$painter_sketch_images = explode(',',$painter_sketch_images);
@endphp

<div class="cabinet-sketch js_hb_sketch_painter_{{$order->id}}">
	<div class="cabinet-sketch__item">
		<div class="ct-row">
			<p><span class="activeSpan">Наброски </span>художника:</p>
		</div>
		<div class="sketch-grid">
			<div>

			@if((isset($painter_sketch_images[0]) && $painter_sketch_images[0]) || (isset($painter_sketch_images[1]) && $painter_sketch_images[1]))

				@foreach($painter_sketch_images as $painter_img)
					@php
						$ext = pathinfo($painter_img, PATHINFO_EXTENSION);
					@endphp

					<div class="sketch-image sketch-initial @if($loop->iteration != 1) mt3 @endif  sketch-success" >
						<span>{{ $loop->iteration }}</span>
						<div class="sketchImg">
							<a target="_blank" href="{{ $painter_img }}">
							<picture>
								@if($ext == 'psd')
									<img width="180" height="200" style="max-width:100%;max-height:100%;" src="/img/psd.svg" alt="">
								@elseif ($ext == 'pdf')
									<img width="180" height="200" style="max-width:100%;max-height:100%;" src="/img/pdf.svg" alt="">
								@else
									<img width="180" height="200" style="max-width:100%;max-height:100%;" src="{{ $painter_img }}" alt="">
								@endif
							</picture>
							</a>
						</div>

						@if ($APainterImagesStatus)
							@foreach ($APainterImagesStatus as $pis)
								<div class="overlay pending">
									<a target="_blank" href="{{ $painter_img }}" style="color:;"><p>{{ $order->painter_sketch_images_status->getTranslatedAttribute('title') }}</p></a>
								</div>

								{{-- <option value="{{ $pis->id }}" @if (isset($order['painter_sketch_images_status']) and $order['painter_sketch_images_status'] == $pis->id) selected @endif>
									{{ $pis->title }}</option> --}}
							@endforeach
						@endif

						{{-- {{ dd($order->painter_sketch_images_status) }} --}}


						{{-- <div class="overlay pending">
							<a target="_blank" href="{{ $painter_img }}"><p>Принят на доработку</p></a>
						</div>
						<div class="overlay success">
							<a target="_blank" href="{{ $painter_img }}"><p>Принят на доработку</p></a>
						</div> --}}
					</div>

				@endforeach
			@endif
			</div>

			<div class="sketch-content">
				<div class="cabinet-elements__item">
					<div class="cabinet-element">
						<div class="ct-row">
							<p>@lang("account_new.orders.chat_with_viarcanvas")</p>
						</div>
						<div class="chat-container scroll-box">
							<div class="chat-scroll__container">
								<div class="cabinet-scroll__nav">
									<div class="scroll-up">
										<svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M17.8577 12.7766C17.7308 12.9255 17.5848 13 17.4198 13C17.2548 13 17.1089 12.9255 16.982 12.7766L9.5 3.99828L2.01804 12.7766C1.89112 12.9255 1.74516 13 1.58016 13C1.41516 13 1.2692 12.9255 1.14228 12.7766L0.190381 11.6598C0.0634605 11.5109 -7.7486e-07 11.3396 -7.7486e-07 11.146C-7.7486e-07 10.9525 0.0634605 10.7812 0.190381 10.6323L9.06212 0.223368C9.18904 0.0744559 9.335 0 9.5 0C9.665 0 9.81096 0.0744559 9.93788 0.223368L18.8096 10.6323C18.9365 10.7812 19 10.9525 19 11.146C19 11.3396 18.9365 11.5109 18.8096 11.6598L17.8577 12.7766Z" fill="#FA7846"></path>
										</svg>
									</div>
									<div class="scroll-down">
										<svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M1.14229 0.223367C1.26921 0.0744551 1.41516 -1.13249e-06 1.58016 -1.13249e-06C1.74516 -1.13249e-06 1.89112 0.0744551 2.01804 0.223367L9.5 9.00172L16.982 0.223367C17.1089 0.0744551 17.2548 -1.13249e-06 17.4198 -1.13249e-06C17.5848 -1.13249e-06 17.7308 0.0744551 17.8577 0.223367L18.8096 1.34021C18.9365 1.48912 19 1.66037 19 1.85395C19 2.04754 18.9365 2.21879 18.8096 2.3677L9.93788 12.7766C9.81096 12.9255 9.665 13 9.5 13C9.335 13 9.18904 12.9255 9.06212 12.7766L0.19038 2.3677C0.0634597 2.21879 0 2.04754 0 1.85395C0 1.66037 0.0634597 1.48912 0.19038 1.34021L1.14229 0.223367Z" fill="#FA7846"></path>
										</svg>
									</div>
								</div>
								<div class="chat-inner scroll-block" style="opacity: 1;">
									<div class="chat-inner__block">


										@if($order->orders_chats)
										@foreach($order->orders_chats as $chat_item)
											<div class="chat-group">
												<div class="chat-item">
													<div class="chat-item__row">
														<div class="chat-name">
															@if($chat_item->is_admin == 0)
																@lang("account_new.orders.chat.you")
															@else
																@lang("account_new.orders.chat.admin")
															@endif
														</div>
														<div class="chat-date">
															@php
															$date = Carbon::parse($chat_item->created_at);
															@endphp
															{{ $date->format("Y/m/d") }}
														</div>
													</div>
													<div class="chat-item__body">
														<p>{{ $chat_item->comment }}</p>
													</div>
												</div>
											</div>
											@endforeach
										@endif
										
									</div>
								</div>
							</div>
							<div class="chat-input">
								<p class="chat-name">@lang("account_new.orders.chat.you")</p>
								<textarea name="chatText" placeholder="@lang("account_new.orders.chat.add_comment_placeholder") "></textarea>
								<button class="chatSend">@lang("account_new.add_comment")</button>
							</div>
						</div>
					</div>
				</div>
				<div class="cabinet-elements__item">
					<div class="c-elementTitle">
						<span class="activeSpan">@lang("account_new.orders.photocomments")</span>
					</div>
					<div class="cabinet-element dropzone-container">
						<div class="my-loadedImages">
							<div class="loadTarget">
								<div class="ct-row">
									<p>Добавить Ваши файлы:</p>
								</div>
								<div class="drop-image">
									<div class="img__place dropzone">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M9.46144 11.3556L12.0261 8.9852C12.1287 8.88991 12.2157 8.86621 12.3395 8.86668C12.4642 8.86905 12.5861 8.9231 12.6528 8.9852L15.2175 11.3556M12.3395 3.1778C13.9494 3.1778 15.5635 3.79623 16.7912 5.04446C17.7439 6.013 18.0785 7.462 18.292 8.71854C20.7299 9.12102 22.8313 11.0195 22.8313 13.6074C22.8313 16.4817 20.5294 18.8222 17.7019 18.8222H6.27753C3.83154 18.8222 1.84766 16.8053 1.84766 14.3185C1.84766 11.8974 3.96467 10.177 6.32136 10.0741C6.25328 8.34923 6.60254 6.35883 7.89513 5.04446C9.12197 3.79717 10.7295 3.1778 12.3395 3.1778ZM12.3255 15.5037V8.86668V15.5037Z" stroke="#FC8C5F" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
										</svg>
										<div class="drop-image-title">Загрузить файл</div>
										<p>Нажмите, чтобы добавить файл</p>
									</div>
									<input multiple="" type="file" class="fileInput">
								</div>
							</div>
							<div class="loadedInner images-preview-on">
								<div class="ct-row">
									<p><span class="activeSpan">Ваши </span>загруженые файлы : </p>
								</div>
								<div class="images-preview-element">
									<div class="loaded-image--scroll">
										<div class="loaded-images__container image-absoluteContainer">
											<div class="uploaded-image-styles">
												<span></span>
												<img src="{{ asset(config('theme.current') . '/images') }}/cabinet/image-preview.png" width="114" height="140" alt="">
											</div>
											<div class="uploaded-image-styles">
												<span></span>
												<img src="{{ asset(config('theme.current') . '/images') }}/cabinet/image-preview.png" width="114" height="140" alt="">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="btn-group">
					<button class="btn cabinet-btn">
						ОТПРАВИТЬ НА ДОРАБОТКУ
					</button>
					<button class="btn cabinet-btn">
						ПРИНЯТЬ КАРТИНУ
					</button>
				</div>
			</div>
		</div>
	</div>

</div>
{{-- @endif --}}