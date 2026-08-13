<div class="js_chat__cover mfp-hide white-popup-block" id="admin_chat_{{ $order['id'] }}">
    @if ($order['admin_chats'] != null && $order['admin_chats'] != '[]')
        @php
            $chat_items = $order['admin_chats'];
        @endphp
        <ul class="comments__main__list" id="chat_box_{{ $order['id'] }}">
            @foreach ($chat_items as $chat_item)
                <li>
                    <div class="comment__user">
                        {{$chat_item['first_name'] }}  ( {{$chat_item['email'] }} )
                    </div>
                    <div class="comment__date">
                        @php
                            $date = Carbon::parse($chat_item['created_at']);
                        @endphp
                        {{ $date->format('Y/m/d') }}
                    </div>
                    <div class="comment">
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
    <form class="admin_chat_form" action="{{ route('update_order_chat', ['order_id' => $order['id'], 'is_admin' => 1]) }}" method="post"
        class="painter_form js_painter_form">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order['id'] }}">
        <input type="hidden" name="is_admin" value="1">
        <div class="form_group">
            <textarea required class="painter_msg" name="admin_msg" cols="30" rows="10"
                placeholder="Комментарий"></textarea>
        </div>
        <div class="form_group">
            <button class="admin_chat_btn btn btn-sm btn-primary" type="submit">Отправить</button>
        </div>
    </form>
</div>


