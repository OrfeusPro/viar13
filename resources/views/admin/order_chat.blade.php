<div class="js_chat__cover mfp-hide white-popup-block" id="chat_{{ $order['id'] }}">
    @if ($order['orders_chats'] != null && $order['orders_chats'] != '[]')
        @php
            $chat_items = $order['orders_chats'];
        @endphp
        <ul class="comments__main__list" id="chat_box_{{ $order['id'] }}">
            @foreach ($chat_items as $chat_item)
                <li>
                    <div class="comment__user">
                        @if($chat_item['is_admin'] == 1) 
                            Администратор
                            @if($chat_item['is_img_sketch'])
                                (набросок) 
                            @elseif($chat_item['is_img_painter'])
                                (картина) 
                            @else
                                {{-- (общий)  --}}
                            @endif
                        @else 
                            Художник 
                            @if($chat_item['is_img_sketch'])
                                (набросок) 
                            @elseif($chat_item['is_img_painter'])
                                (картина) 
                            @else
                                {{-- (общий)  --}}
                            @endif
                        @endif

                        <div class="comment__date">
                            @php
                                $date = Carbon::parse($chat_item['created_at']);
                            @endphp
                            {{ $date->format("Y-m-d H:i:s") }}
                        </div>
                    </div>
                    <div class="comment @if($chat_item->admin_is_read == 0 && $chat_item->is_admin == 0) active @endif" data-chat-id="{{ $chat_item->id }}" @if($chat_item->admin_is_read == 0 && $chat_item->is_admin == 0) onclick="messageAdminRead(this)" @endif>
                        {{ $chat_item['comment'] }}
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <p class="js_no_msgs">Сообщений нет</p>
        <ul class="comments__main__list" id="chat_box_{{ $order['id'] }}">
        </ul>
    @endif
    <br><br>
    <form action="{{ route('update_order_chat', ['order_id' => $order['id'], 'is_admin' => 1]) }}" method="post"
        class="painter_form js_painter_form">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order['id'] }}">
        <input type="hidden" name="is_admin" value="1">
        <div class="form_group">
            <textarea required class="painter_msg" name="painter_msg" cols="30" rows="10"
                placeholder="Комментарий"></textarea>
        </div>
        <select name="image_type">
            <option value="" selected>Общий</option>
            {{-- <option value="is_img_sketch">Набросок</option> --}}
            {{-- <option value="is_img_painter">Картина</option> --}}
        </select>
        <div class="form_group">
            <button class="painter_btn btn btn-sm btn-primary" type="submit">Отправить</button>
        </div>
    </form>
</div>
