@if(Auth::user()->role->name == 'user')
<form action="{{ route('new_send_client_painter_comments') }}" method="POST" class="f__user_order js_send_user_add_images">
	<input type="hidden" name="order_id" value="{{ $order->id }}">

	<div class="cabinet-elements">
		<div class="/*cabinet-elements__item*/">
			<div class="c-elementTitle">
				@lang("account_new.comments.title")
			</div>
			<div class="cabinet-element">
				<div class="ct-row">
					<p>
						@lang("account_new.orders.chat_with_viarcanvas")
					</p>
				</div>
				<div class="chat-container scroll-box">
					<div class="chat-scroll__container">
						<div class="cabinet-scroll__nav">
							<div class="scroll-up">
								<svg width="19" height="13" viewBox="0 0 19 13" fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M17.8577 12.7766C17.7308 12.9255 17.5848 13 17.4198 13C17.2548 13 17.1089 12.9255 16.982 12.7766L9.5 3.99828L2.01804 12.7766C1.89112 12.9255 1.74516 13 1.58016 13C1.41516 13 1.2692 12.9255 1.14228 12.7766L0.190381 11.6598C0.0634605 11.5109 -7.7486e-07 11.3396 -7.7486e-07 11.146C-7.7486e-07 10.9525 0.0634605 10.7812 0.190381 10.6323L9.06212 0.223368C9.18904 0.0744559 9.335 0 9.5 0C9.665 0 9.81096 0.0744559 9.93788 0.223368L18.8096 10.6323C18.9365 10.7812 19 10.9525 19 11.146C19 11.3396 18.9365 11.5109 18.8096 11.6598L17.8577 12.7766Z"
										fill="#FA7846" />
								</svg>
							</div>
							<div class="scroll-down">
								<svg width="19" height="13" viewBox="0 0 19 13" fill="none"
									xmlns="http://www.w3.org/2000/svg">
									<path
										d="M1.14229 0.223367C1.26921 0.0744551 1.41516 -1.13249e-06 1.58016 -1.13249e-06C1.74516 -1.13249e-06 1.89112 0.0744551 2.01804 0.223367L9.5 9.00172L16.982 0.223367C17.1089 0.0744551 17.2548 -1.13249e-06 17.4198 -1.13249e-06C17.5848 -1.13249e-06 17.7308 0.0744551 17.8577 0.223367L18.8096 1.34021C18.9365 1.48912 19 1.66037 19 1.85395C19 2.04754 18.9365 2.21879 18.8096 2.3677L9.93788 12.7766C9.81096 12.9255 9.665 13 9.5 13C9.335 13 9.18904 12.9255 9.06212 12.7766L0.19038 2.3677C0.0634597 2.21879 0 2.04754 0 1.85395C0 1.66037 0.0634597 1.48912 0.19038 1.34021L1.14229 0.223367Z"
										fill="#FA7846" />
								</svg>
							</div>
						</div>

						@php
							$user_comments = \App\Models\User::get_user_acc_comments($order->id);

							// подготовим роли авторов сообщений одним запросом
							$authorRoles = collect();
							if ($user_comments && count($user_comments)) {
								$authorIds = collect($user_comments)->pluck('user_id')->filter()->unique()->values();
								if ($authorIds->isNotEmpty()) {
									$authorRoles = \App\Models\User::whereIn('id', $authorIds)->pluck('role_id', 'id');
								}
							}
						@endphp



							<div class="chat-inner scroll-block">
								<div class="chat-inner__block js_order_user_comments">
									@if($user_comments)
										@foreach($user_comments as $u_comment)
											@if(!$u_comment->is_img_sketch && !$u_comment->is_img_painter)
											<div class="chat-group">
												<div class="chat-item @if($u_comment->is_read == 0) active @endif" data-chat-id="{{ $u_comment->id }}" onclick="messageRead(this)">
													<div class="chat-item__row">
														@php
															$authorRoleId = $authorRoles[$u_comment->user_id] ?? null;
														@endphp
														<div class="chat-name">
															@if(Auth::user()->id == $u_comment->user_id)
																@lang('account_new.orders.chat.you')
															@elseif($authorRoleId == 3)
																@lang('account_new.orders.chat.painter')
															@elseif(!empty($u_comment->is_admin))
																@lang('account_new.orders.chat.admin')
															@else
																@lang('account_new.orders.chat.client')
															@endif
														</div>
														<div class="chat-date">
															{{ $u_comment->created_at }}
														</div>
													</div>
													<div class="chat-item__body">
														@php
															$escapedComment = e($u_comment->comment);
															$linkedComment = preg_replace(
																'~(https?://[^\s<]+)~i',
																'<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
																$escapedComment
															);
														@endphp
														<p>{!! nl2br($linkedComment) !!}</p>
													</div>
												</div>
											</div>
											@endif
										@endforeach
									@endif

								</div>
							</div>

					</div>
					<div class="chat-input">
						<p class="chat-name">@lang("account_new.orders.chat.you")</p>
						<textarea name="user_comment" placeholder="@lang("account_new.orders.chat.add_comment_placeholder")"></textarea>
						<button class="chatSend painter_btn">@lang("account_new.orders.chat.add_comment")</button>
					</div>
				</div>
			</div>
		</div>
		<div class="cabinet-elements__item dropzone-container pt-5">
			<div class="c-elementTitle">
				<span class="activeSpan">@lang("account_new.orders.photocomments")</span>
			</div>
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
					<input multiple type="file" accept=".png,.bmp,.jpg,.jpeg,.psd,.fig,.pdf,.heic,.heif" class="fileInput js_painter_images" name="user_images[]" id="js_painter_images_{{ $order->id }}">
				</div>
			</div>


			@php
			$client_imgs = $order->client_images;
			$client_imgs = rtrim($client_imgs, ',');
			$client_imgs_items = explode (",", $client_imgs);
			@endphp

			<div class="loadedInner">
				<div class="ct-row">
					<p>
						@lang("account_new.orders.you_images")
					</p>
				</div>
				<div class="images-preview-element">
					<div class="loaded-image--scroll">
						<div class="loaded-images__container image-absoluteContainer">

							@if(is_array($client_imgs_items) && !empty($client_imgs_items) && $client_imgs_items[0] != "")
							@foreach($client_imgs_items as $img_item)
							@php
								$imgSrc = order_image_url($img_item);
							@endphp
							<div class="uploaded-image-styles">
								<span></span>
								<a href="{{ $imgSrc }}" target="_blank">
									<img src="{{ $imgSrc }}" width="114" height="140" alt="">
								</a>
							</div>
							@endforeach
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
	<button class="cabinet-btn painter_btn">@lang("account_new.btn.send")</button>
</form>
@endif
