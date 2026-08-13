<div class="js_chat__cover mfp-hide white-popup-block" id="client_chat_{{ $order['id'] }}" style="max-width: 1100px!important; max-height: 90%; overflow: scroll;">

    <!-----------------------client ------------------->
    <!----------------------------- ------------------->
    <form action="{{ route('new_send_admin_to_client_painter_comments') }}" method="POST" class="f__user_order js_send_user_add_images">
        <input type="hidden" name="order_id" value="{{ $order['id'] }}">
    
        <div class="cabinet-elements">
            <div class="/*cabinet-elements__item*/">
                <div class="c-elementTitle">
                    @lang("account_new.comments.title")
                </div>
                <div class="cabinet-element">

                    <div class="chat-container scroll-box">
                        <div class="chat-scroll__container">
                            @php
                                $user_comments = \App\Models\User::get_user_acc_comments($order['id']);

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
                                                    <div class="chat-item @if($u_comment->admin_is_read == 0) active @endif" data-chat-id="{{ $u_comment->id }}" onclick="admin_messageRead(this)">
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
                            <textarea name="user_comment" style="width:100%;" placeholder="@lang("account_new.orders.chat.add_comment_placeholder")"></textarea>
                            <button class="chatSend painter_btn" style="float: right;">@lang("account_new.orders.chat.add_comment")</button>
                        </div>
                    </div>
                </div>
            </div>
    
        </div>
        {{-- <button class="cabinet-btn painter_btn">@lang("account_new.btn.send")</button> --}}
    </form>



    <!-----------------------client images------------------->
    <!----------------------------- ------------------->

					{{-- чат картины --}}
          {{-- @if($order && method_exists($order, 'order_painter_images')) --}}
          {{-- {{ dd($order) }} --}}
                @php
                    $painter_picture_images = $order['order_painter_images']->where('is_img_painter', 1);
                    $chat = \App\Models\User::get_user_acc_comments($order['id'],"is_img_painter");
                @endphp
          {{-- {{ dd($painter_picture_images) }} --}}
                @include('admin.o_chat_img', [
                    "imgs"=> $painter_picture_images, 
                    "css_js" => "js_hb_painter_".$order['id'],
                    "css_js_send_image_form" => "js_painter_form_images_upd",
                    "css_js_send_image_id" => "painter_imgs_order_".$order['id'],
                    "css_js_send_image_name" => "painter_images",
                    "painter_load_image_title" => __("account_new.orders.add_your_picture"),
                    "chat_img_type" => "is_img_painter",
                    "chat_route_admin_painter" => route('new_update_order_chat'),
                    "chat_route_admin_user" => route('new_send_admin_to_client_painter_comments'),
                    "route_change_images_status" => route('changeOrderPainterImageStatus'),
                    "block_title" => __("account_new.orders.picture_title"),
                    "block_sub_title" => __("account_new.picture_sub_title"),
                    "chat" => $chat,
                    ])

                
                {{-- чат наброски --}}
                @php
                    $painter_sketch_images = $order['order_painter_images']->where('is_img_sketch', 1);
                    $chat = \App\Models\User::get_user_acc_comments($order['id'],"is_img_sketch");
                @endphp

                @include('admin.o_chat_img', [
                    "imgs"=> $painter_sketch_images, 
                    "css_js" => "js_hb_sketch_painter_".$order['id'],
                    "css_js_send_image_form" => "js_painter_form_images_upd_sketch",
                    "css_js_send_image_id" => "painter_sketch_imgs_order_".$order['id'],
                    "css_js_send_image_name" => "painter_sketch_images",
                    "painter_load_image_title" => __("account_new.orders.add_your_sketch"),
                    "chat_img_type" => "is_img_sketch",
                    "chat_route_admin_painter" => route('new_update_order_chat'),
                    "chat_route_admin_user" => route('new_send_admin_to_client_painter_comments'),
                    "route_change_images_status" => route('changeOrderPainterImageStatus'),
                    "block_title" => __("account_new.orders.sketch_title"),
                    "block_sub_title" => __("account_new.sketch_sub_title"),
                    "chat" => $chat,
                    ])
              {{-- @endif --}}
</div>
