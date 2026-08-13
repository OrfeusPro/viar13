@if(Auth::user()->role->name == 'painter')
<div class="commentary commentary_painter">
    <p class="painter_title">Чат</p>
    <ul class="comments__main__list">
        @if($order->orders_chats)
        @foreach($order->orders_chats as $chat_item)
        <li>
            <div class="comment__user">
                @if($chat_item->is_admin == 0) Вы
                @else Администратор @endif
            </div>
            <div class="comment__date">
                @php
                $date = Carbon::parse($chat_item->created_at);
                @endphp
                {{ $date->format("Y/m/d") }}
            </div>
            <div class="comment">
                {{ $chat_item->comment }}
            </div>
        </li>
        @endforeach
        @endif
    </ul>

    <form action="#" method="post" class="painter_form js_painter_admin_chat" data-id={{ $order->id }}>
        @csrf
        <div class="form_group">
            <textarea required class="painter_msg" name="painter_msg" cols="30" rows="10"
                placeholder="Комментарий"></textarea>
        </div>
        <div class="form_group">
            <button class="painter_btn" type="submit">Отправить</button>
            <div class="js_spinner"></div>
        </div>
    </form>
</div>
@endif