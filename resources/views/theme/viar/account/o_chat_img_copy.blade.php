{{-- {{ dd(Auth::user()->role->name) }} --}}
<div class="cabinet-sketch {{ $css_js }} orange_border_t pt-5 @if(!(isset($imgs[0]) && $imgs[0]) && !(isset($imgs[1]) && $imgs[1]) && Auth::user()->role->name == 'user') hidden @endif">
	<div class="cabinet-sketch__item">
		<div class="ct-row">
			<p>{!! $block_title !!}</p>
		</div>
		<div class="sketch-grid">
			<div>
				@if((isset($imgs[0]) && $imgs[0]) || (isset($imgs[1]) && $imgs[1]))
					@foreach($imgs as $painter_img)
						@php
							$ext = pathinfo($painter_img, PATHINFO_EXTENSION);
							$imgExists = order_image_exists($painter_img);
							$imgSrc = order_image_url($painter_img);
						@endphp

						<div class="sketch-image sketch-initial @if($loop->iteration != 1) mt3 @endif sketch-{{ $img_status->status }}" >
							<span>{{ $loop->iteration }}</span>
							<div class="sketchImg">
								<a target="_blank" href="{{ $imgSrc }}">
								<picture>
									@if(!$imgExists)
										<img width="180" height="200" style="max-width:100%;max-height:100%;" src="{{ order_image_placeholder() }}" alt="">
									@elseif($ext == 'psd')
										<img width="180" height="200" style="max-width:100%;max-height:100%;" src="/img/psd.svg" alt="">
									@elseif ($ext == 'pdf')
										<img width="180" height="200" style="max-width:100%;max-height:100%;" src="/img/pdf.svg" alt="">
									@else
										<img width="180" height="200" style="max-width:100%;max-height:100%;" src="{{ $imgSrc }}" alt="">
									@endif
								</picture>
								</a>
							</div>


							@if(!($order->status == 'completed' || $order->status=="sended" || $order->status=="send_lubanas"))
								<div class="overlay {{ $img_status->status }}">
									<a target="_blank" href="{{ $imgSrc }}" style="color: {{ $img_status["color"] }};"><p>{{ $img_status->getTranslatedAttribute('title') }}</p></a>
								</div>
							@endif

						</div>

					@endforeach
				@endif
			</div>

			<div class="sketch-content">
				<div class="cabinet-elements__item  @if((isset($imgs[0]) && $imgs[0]) || (isset($imgs[1]) && $imgs[1]) && Auth::user()->role->name == 'painter') @else hidden @endif">
					<div class="cabinet-element">
						<div class="ct-row">
							<p>
								{!! $block_sub_title !!}
							</p>
						</div>

						<form action="" method="POST" class="send_message" data-id="{{ $order->id }}" @if(Auth::user()->role->name == 'painter') data-route="{{ $chat_route_admin_painter }}" @endif @if(Auth::user()->role->name == 'user') data-route="{{ $chat_route_admin_user }}" @endif>

							<input type="hidden" name="chat_img_type" value="{{ $chat_img_type }}">

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
										<div class="chat-inner__block js_order_user_comments">


											@php
												if(Auth::user()->role->name == 'painter')
												{
													$chats = $chat_painter;
												} else {
													$chats = $chat_client;
												}
											@endphp

											@if(isset($chats) && $chats)
												@foreach($chats as $chat_item)
													@if($chat_item->$chat_img_type)
														<div class="chat-group">
															<div class="chat-item @if($chat_item->is_read == 0 && Auth::user()->role->name == 'user') active @endif" data-chat-id="{{ $chat_item->id }}" @if(Auth::user()->role->name == 'user') onclick="messageRead(this)" @endif>
																<div class="chat-item__row">
																	<div class="chat-name">
																		@if($chat_item->is_admin == 0)
																			@if(Auth::user()->role->name == 'user')
																				@lang("account_new.orders.chat.you")
																			@else
																				@lang("account_new.orders.chat.client")
																			@endif
																		@else
																			@lang("account_new.orders.chat.admin")
																		@endif
																	</div>
																	<div class="chat-date">
																		@php
																		$date = Carbon::parse($chat_item->created_at);
																		@endphp
																		{{ $date->format("Y-m-d H:i:s") }}
																	</div>
																</div>
																<div class="chat-item__body">
																	<p>{{ $chat_item->comment }}</p>
																</div>
															</div>
														</div>
													@endif
												@endforeach
											@endif
											
										</div>
									</div>
								</div>

								@if(!($order->status == 'completed' || $order->status=="sended" || $order->status=="send_lubanas"))
									@if(Auth::user()->role->name == 'user')
										<div class="chat-input">
											<p class="chat-name">@lang("account_new.orders.chat.you")</p>
											<textarea name="user_comment" placeholder="@lang("account_new.orders.chat.placeholder")"></textarea>
											<button class="chatSend">@lang("account_new.orders.chat.add_comment")</button>
										</div>
									@endif
								@endif
							</div>
						</form>
					</div>
				</div>

				@if(Auth::user()->role->name == 'painter')
					@if(!($order->status == 'completed' || $order->status=="sended" || $order->status=="send_lubanas"))
						<form action="" method="post" class="painter_form {{ $css_js_send_image_form }}" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="order_id" value="{{ $order->id }}">

							<div class="cabinet-elements__item dropzone-container">
								<div class="cabinet-element">
									<div class="ct-row">
										<p>@lang("account_new.orders.add_your_files")</p>
									</div>
									<div class="drop-image">
										<div class="img__place dropzone">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path
													d="M9.46144 11.3556L12.0261 8.9852C12.1287 8.88991 12.2157 8.86621 12.3395 8.86668C12.4642 8.86905 12.5861 8.9231 12.6528 8.9852L15.2175 11.3556M12.3395 3.1778C13.9494 3.1778 15.5635 3.79623 16.7912 5.04446C17.7439 6.013 18.0785 7.462 18.292 8.71854C20.7299 9.12102 22.8313 11.0195 22.8313 13.6074C22.8313 16.4817 20.5294 18.8222 17.7019 18.8222H6.27753C3.83154 18.8222 1.84766 16.8053 1.84766 14.3185C1.84766 11.8974 3.96467 10.177 6.32136 10.0741C6.25328 8.34923 6.60254 6.35883 7.89513 5.04446C9.12197 3.79717 10.7295 3.1778 12.3395 3.1778ZM12.3255 15.5037V8.86668V15.5037Z"
													stroke="#FC8C5F" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
													stroke-linejoin="round" />
											</svg>
											<div class="drop-image-title">@lang("account_new.orders.upload_file")</div>
											<p>@lang("account_new.orders.click_to_add_file")</p>
										</div>
										<input id="{{ $css_js_send_image_id }}" multiple type="file" accept=".png,.bmp,.jpg,.jpeg,.psd,.fig,.pdf,.heic,.heif" class="fileInput js_painter_images" name="{{ $css_js_send_image_name }}[]">
									</div>
								</div>
							</div>
							<button class="cabinet-btn painter_btn" style="margin-left: inherit">@lang("account_new.btn.send")</button>
						</form>
					@endif
				@endif

 				@if(Auth::user()->role->name == 'user')
					@if(!($order->status == 'completed' || $order->status=="sended" || $order->status=="send_lubanas"))
					<div class="btn-group">
						<button class="btn cabinet-btn" onclick="changeStatus(5, {{ $order->id }}, '{{ $route_change_images_status }}')">
							@lang("account_new.btn.send_for_development")						
						</button>
						<button class="btn cabinet-btn" onclick="changeStatus(4, {{ $order->id }}, '{{ $route_change_images_status }}')">
							@lang("account_new.btn.accept")
						</button>
					</div>
					@endif
				@endif

			</div>
		</div>
	</div>

</div>
