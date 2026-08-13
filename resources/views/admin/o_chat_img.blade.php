@if ((isset($imgs[0]) && $imgs[0]) || (isset($imgs[1]) && $imgs[1]))
	<div class="cabinet-sketch {{ $css_js }} orange_border_t pt-5">
		<div class="cabinet-sketch__item">
			<div class="ct-row mb-3">
				<p>{!! $block_title !!}</p>
			</div>
			@foreach ($imgs as $painter_img)
				@php
					$chats = $painter_img
					    ->order_user_comments()
					    ->where($chat_img_type, 1)
					    ->get();

					$authorRoles = collect();
					if ($chats && $chats->count()) {
						$ids = $chats->pluck('user_id')->filter()->unique()->values();
						if ($ids->isNotEmpty()) {
							// вернёт map: [user_id => role_id]
							$authorRoles = \App\Models\User::whereIn('id', $ids)->pluck('role_id', 'id');
						}
					}
				@endphp

				<div class="sketch-grid pt-5">
					<div>
						@php
							$ext = pathinfo($painter_img, PATHINFO_EXTENSION);
							$img_status = \App\Models\APainterImagesStatus::getPainterImagesStatus($painter_img->status);

						@endphp

						<div
							class="sketch-image sketch-initial @if ($loop->iteration != 1)  @endif sketch-{{ $img_status['status'] }}">
							<span>{{ $loop->iteration }}</span>
							<div class="sketchImg">
								<a target="_blank" href="https://viarcanvas.com/{{ $painter_img['image'] }}">
									<picture>
										@if ($ext == 'psd')
											<img width="180" height="200" style="max-width:100%;max-height:100%;" src="/img/psd.svg" alt="">
										@elseif ($ext == 'pdf')
											<img width="180" height="200" style="max-width:100%;max-height:100%;" src="/img/pdf.svg" alt="">
										@else
											<img width="180" height="200" style="max-width:100%;max-height:100%;"
												src="https://viarcanvas.com/{{ $painter_img['small_image'] ? $painter_img['small_image'] : $painter_img['image'] }}"
												alt="">
										@endif
									</picture>
								</a>
							</div>

							@if (!($order['status'] == 'completed' || $order['status'] == 'sended' || $order['status'] == 'send_lubanas'))
								<div class="overlay {{ $img_status['status'] }}">
									<a target="_blank" href="https://viarcanvas.com/{{ $painter_img['image'] }}"
										style="color: {{ $img_status['color'] }};">
										<p>
											{{ $img_status->getTranslatedAttribute('title') }}
										</p>
									</a>
								</div>
							@endif

						</div>

					</div>


					<div
						class="sketch-content {{ Auth::user()->role->name }} @if (!isset($chats)) true @endif @if (Auth::user()->role->name != 'painter' && !isset($chats)) hidden @endif">
						<div class="cabinet-elements__item  @if (!isset($chats[0]) && Auth::user()->role->name == 'painter') hidden @else @endif">
							<div class="cabinet-element">
								<div class="ct-row">
									<p>
										{!! $block_sub_title !!}
									</p>
								</div>
								<form action="" method="POST" class="send_message" data-id="{{ $order['id'] }}"
									@if (Auth::user()->role->name == 'painter') data-route="{{ $chat_route_admin_painter }}" @endif
									@if (Auth::user()->role->name == 'user') data-route="{{ $chat_route_admin_user }}" @endif>

									<input type="hidden" name="chat_img_type" value="{{ $chat_img_type }}">
									<input type="hidden" name="order_painter_image_id" value="{{ $painter_img->id }}">

									<div
										class="chat-container scroll-box @if (Auth::user()->role->name == 'painter') chat_painter @else chat_client @endif">
										<div class="chat-scroll__container">
											{{-- <div class="cabinet-scroll__nav">
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
											</div> --}}
											<div class="chat-inner scroll-block" style="opacity: 1;">
												<div class="chat-inner__block js_order_user_comments">

													@if (isset($chats) && $chats)
														@foreach ($chats as $chat_item)
															@if ($chat_item->$chat_img_type)
																<div class="chat-group">
																	<div class="chat-item @if ($chat_item->admin_is_read == 0 && Auth::user()->role->name != 'user') active @endif"
																		data-chat-id="{{ $chat_item->id }}" onclick="admin_messageRead(this)">
																		<div class="chat-item__row">
																			<div class="chat-name">
																				@php
																					$authorRoleId = $authorRoles[$chat_item->user_id] ?? null;
																				@endphp

																				@if ($chat_item->user_id == Auth::id())
																					@lang('account_new.orders.chat.you')
																				@elseif ($authorRoleId == 3)
																					@lang('account_new.orders.chat.painter')
																				@elseif (!empty($chat_item->is_admin))
																					@lang('account_new.orders.chat.admin')
																				@elseif ($chat_item->user_id == ($order['user_id'] ?? null))
																					@lang('account_new.orders.chat.client')
																				@else
																					@lang('account_new.orders.chat.client')
																				@endif
																			</div>
																			<div class="chat-date">
																				@php
																					$date = Carbon::parse($chat_item->created_at);
																				@endphp
																				{{ $date->format('Y-m-d H:i:s') }}
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

										@if (!($order['status'] == 'completed' || $order['status'] == 'sended' || $order['status'] == 'send_lubanas'))
											@if (Auth::user()->role->name != 'user')
												<div class="chat-input">
													<p class="chat-name">@lang('account_new.orders.chat.you')</p>
													<textarea name="user_comment" placeholder="@lang('account_new.orders.chat.placeholder')"></textarea>
													<button class="chatSend">@lang('account_new.orders.chat.add_comment')</button>
												</div>
											@endif
										@endif
									</div>
								</form>
							</div>
						</div>


					</div>
				</div>
			@endforeach
		</div>

	</div>
@endif
