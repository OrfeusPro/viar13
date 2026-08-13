@if(Auth::user()->role->name == 'painter')
	<input type="hidden" name="order_id" value="{{ $order->id }}">

	<div class="cabinet-elements">
		<form action="#" method="post" class="painter_form js_painter_admin_chat" data-id={{ $order->id }}>
			<div class="/*cabinet-elements__item*/">
				<div class="cabinet-element">
					<div class="ct-row">
						<p>@lang("account_new.orders.chat_with_admin") <span class="activeSpan"></span>:</p>
						<p class="small-notif">@lang("account_new.orders.chat_with_admin_small_notif")</p>
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


								<div class="chat-inner scroll-block">
									<div class="chat-inner__block js_order_user_comments">
										@if($order->orders_chats)
											@foreach($order->orders_chats as $painter_chat)
												@if(!$painter_chat->is_img_sketch && !$painter_chat->is_img_painter)
												<div class="chat-group">
													<div class="chat-item @if($painter_chat->is_read == 0) active @endif" data-chat-id="{{ $painter_chat->id }}" onclick="messageRead(this)">
														<div class="chat-item__row">
															<div class="chat-name">
																@if(!$painter_chat->is_admin)
																	@lang("account_new.orders.chat.you")
																@else
																	@lang("account_new.orders.chat.admin")
																@endif
															</div>
															<div class="chat-date">
																{{ $painter_chat->created_at }}
															</div>
														</div>
														<div class="chat-item__body">
															<p>{{ $painter_chat->comment }}</p>
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
							<textarea name="user_comment" placeholder="@lang("account_new.orders.chat.placeholder")"></textarea>
							<button class="chatSend painter_btn">@lang("account_new.orders.chat.add_comment")</button>
						</div>
					</div>
				</div>
			</div>
		</form>

		<div class="cabinet-elements__item dropzone-container pt-5">

			@php
			$client_imgs = $order->client_images;
			$client_imgs = rtrim($client_imgs, ',');
			$client_imgs_items = explode (",", $client_imgs);
			@endphp

			<div class="loadedInner images-preview-on" @if(is_array($client_imgs_items) && !empty($client_imgs_items) && $client_imgs_items[0] != "") class="display: block;" @else style="display: none;" @endif>
				<div class="ct-row">
					<p>@lang("account_new.orders.add_client_files")</p>
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
								{{-- <span></span> --}}
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

{{-- ===== ЧАТ ХУДОЖНИКА С КЛИЕНТОМ (общие комментарии) ===== --}}
@php
    $client = \App\Models\User::find($order->user_id);
    $clientSettings = [];
    $clientLocale = null;

    if ($client && $client->settings) {
        $clientSettings = is_array($client->settings) ? $client->settings : json_decode($client->settings, true);
        $clientLocale = $clientSettings['locale'] ?? null;
    }
    if (!$clientLocale && !empty($client->locale)) {
        $clientLocale = $client->locale;
    }

    $langMap = [
        'ru' => 'Русский',
        'uk' => 'Українська',
        'ua' => 'Українська',
        'en' => 'English',
        'pl' => 'Polski',
        'lv' => 'Latviešu',
        'lt' => 'Lietuvių',
        'de' => 'Deutsch',
        'et' => 'Eesti',
        'ee' => 'Eesti',
    ];
    $clientLangTitle = $langMap[$clientLocale ?? app()->getLocale()] ?? strtoupper($clientLocale ?? app()->getLocale());

    // Комментарии без привязки к изображениям
    $painterClientComments = \App\Models\User::get_user_acc_comments($order->id);
@endphp

<div class="cabinet-elements mt-5">
    <form action="#" method="post" class="painter_form js_painter_client_chat" data-id="{{ $order->id }}">
        <div class="/*cabinet-elements__item*/">
            <div class="cabinet-element">
                <div class="ct-row">
                    <p>
                        @lang("account_new.orders.chat_with_client")<br>
                        <span class="activeSpan">@lang("account_new.orders.client_lang") {{ $clientLocale }} - {{ $clientLangTitle }}</span>
                    </p>
                    <p class="small-notif" style="max-width: 780px;">
						@lang("account_new.orders.chat_with_client_small_notif")                        
                    </p>
                </div>

                <div class="chat-container scroll-box">
                    <div class="chat-scroll__container">
                        <div class="cabinet-scroll__nav">
                            <div class="scroll-up">
                                <svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.8577 12.7766C17.7308 12.9255 17.5848 13 17.4198 13C17.2548 13 17.1089 12.9255 16.982 12.7766L9.5 3.99828L2.01804 12.7766C1.89112 12.9255 1.74516 13 1.58016 13C1.41516 13 1.2692 12.9255 1.14228 12.7766L0.190381 11.6598C0.0634605 11.5109 -7.7486e-07 11.3396 -7.7486e-07 11.146C-7.7486e-07 10.9525 0.0634605 10.7812 0.190381 10.6323L9.06212 0.223368C9.18904 0.0744559 9.335 0 9.5 0C9.665 0 9.81096 0.0744559 9.93788 0.223368L18.8096 10.6323C18.9365 10.7812 19 10.9525 19 11.146C19 11.3396 18.9365 11.5109 18.8096 11.6598L17.8577 12.7766Z" fill="#FA7846"/>
                                </svg>
                            </div>
                            <div class="scroll-down">
                                <svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1.14229 0.223367C1.26921 0.0744551 1.41516 -1.13249e-06 1.58016 -1.13249e-06C1.74516 -1.13249e-06 1.89112 0.0744551 2.01804 0.223367L9.5 9.00172L16.982 0.223367C17.1089 0.0744551 17.2548 -1.13249e-06 17.4198 -1.13249e-06C17.5848 -1.13249e-06 17.7308 0.0744551 17.8577 0.223367L18.8096 1.34021C18.9365 1.48912 19 1.66037 19 1.85395C19 2.04754 18.9365 2.21879 18.8096 2.3677L9.93788 12.7766C9.81096 12.9255 9.665 13 9.5 13C9.335 13 9.18904 12.9255 9.06212 12.7766L0.19038 2.3677C0.0634597 2.21879 0 2.04754 0 1.85395C0 1.66037 0.0634597 1.48912 0.19038 1.34021L1.14229 0.223367Z" fill="#FA7846"/>
                                </svg>
                            </div>
                        </div>

                        <div class="chat-inner scroll-block">
                            <div class="chat-inner__block js_order_user_comments">
                                @if($painterClientComments)
                                    @foreach($painterClientComments as $u_comment)
                                        @if(empty($u_comment->is_img_sketch) && empty($u_comment->is_img_painter))
                                            <div class="chat-group">
                                                <div class="chat-item" @if(isset($u_comment->is_read) && $u_comment->is_read == 0) class="active" @endif>
                                                    <div class="chat-item__row">
                                                        <div class="chat-name">
                                                            @if(Auth::user()->id == $u_comment->user_id)
                                                                @lang("account_new.orders.chat.you")
                                                            @else
                                                                @lang("account_new.orders.chat.client")
                                                            @endif
                                                        </div>
                                                        <div class="chat-date">
                                                            {{ \Carbon\Carbon::parse($u_comment->created_at)->format('Y-m-d H:i:s') }}
                                                        </div>
                                                    </div>
                                                    <div class="chat-item__body">
                                                        <p>{{ $u_comment->comment }}</p>
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
                        <div class="chat-input">
                            <p class="chat-name">@lang("account_new.orders.chat.you")</p>
                            <textarea name="user_comment" class="js_u_comm_textarea" placeholder="@lang('account_new.orders.chat.placeholder')"></textarea>
                            <button class="chatSend painter_btn">@lang("account_new.orders.chat.add_comment")</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>
{{-- ===== /ЧАТ ХУДОЖНИКА С КЛИЕНТОМ ===== --}}
@endif
