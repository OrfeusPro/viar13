@if ($order['comment'] != '' && $order['comment'] != null)
    Комментарий заказа:<br>
    <p class="order_comment">{{ $order['comment'] }}</p>
@endif
@if ($order['admin_comment'] != '' && $order['admin_comment'] != null)
    <span style="color: #d33;">Комментарий администратора:</span><br>
    <p class="order_comment">{{ $order['admin_comment'] }}</p>
@endif
@if ($order['painter_comment'] != '' && $order['painter_comment'] != null)
    Комментарий художника:<br>
    <p class="order_comment">{{ $order['painter_comment'] }}</p>
@endif
@php
    $user_comments = \App\Models\User::get_user_acc_comments($order['id']);
@endphp
<ul class="commentary commentary__bot commentary__bot__inline">
    <li>Комментарии<br> клиента к
        картинам:<br>{{ $order['client_comment'] }}
    </li>

    @if ($user_comments)
        <ul class="user__comments__list">
            @foreach ($user_comments as $u_comment)
                <li class="order_comment">
                    <span>{{ date('d.m.Y', strtotime($u_comment->created_at)) }} - </span>{{ $u_comment->comment }}
                </li><br>
            @endforeach
        </ul>
    @endif
</ul>@php
    $user_comments = \App\Models\User::get_painter_all_comments($order['id']);
@endphp
<ul class="commentary commentary__bot commentary__bot__inline">
    <li>Комментарии<br>
        художника:<br>{{ $order['client_comment'] }}
    </li>
    @if ($user_comments)
        <ul class="user__comments__list">
            @foreach ($user_comments as $u_comment)
                <li class="order_comment">
                    <span>{{ date('d.m.Y', strtotime($u_comment->created_at)) }} - </span>{{ $u_comment->comment }}
                </li><br>
            @endforeach
        </ul>
    @endif
</ul>

<br>

@php
    $clientChatsSource = \App\Models\User::get_user_acc_comments($order['id']);
    $adminChatsSource = $order['admin_chats'] ?? [];
    $orderChatsSource = $order['orders_chats'] ?? [];

    if ($clientChatsSource instanceof \Illuminate\Support\Collection) {
        $clientChats = $clientChatsSource;
    } elseif (is_array($clientChatsSource)) {
        $clientChats = collect($clientChatsSource);
    } elseif (is_string($clientChatsSource)) {
        $decodedClientChats = json_decode($clientChatsSource, true);
        $clientChats = is_array($decodedClientChats) ? collect($decodedClientChats) : collect();
    } else {
        $clientChats = collect();
    }

    if ($adminChatsSource instanceof \Illuminate\Support\Collection) {
        $adminChats = $adminChatsSource;
    } elseif (is_array($adminChatsSource)) {
        $adminChats = collect($adminChatsSource);
    } elseif (is_string($adminChatsSource)) {
        $decodedAdminChats = json_decode($adminChatsSource, true);
        $adminChats = is_array($decodedAdminChats) ? collect($decodedAdminChats) : collect();
    } else {
        $adminChats = collect();
    }

    if ($orderChatsSource instanceof \Illuminate\Support\Collection) {
        $orderChats = $orderChatsSource;
    } elseif (is_array($orderChatsSource)) {
        $orderChats = collect($orderChatsSource);
    } elseif (is_string($orderChatsSource)) {
        $decodedOrderChats = json_decode($orderChatsSource, true);
        $orderChats = is_array($decodedOrderChats) ? collect($decodedOrderChats) : collect();
    } else {
        $orderChats = collect();
    }
@endphp

<ul class="commentary commentary__bot commentary__bot__inline">
    <li>
        <div class="admin__chat__cover">
                <a class="js_show_chat btn btn-sm btn-primary" href="#client_chat_{{ $order['id'] }}">Показать чат<br> с клиентом
                    @if($clientChats->isNotEmpty())
                        <b style="color: black;">({{ $clientChats->count() }})</b>
                    @endif
                </a>
            @include('admin.client_chat')
        </div>
    </li>
</ul>


<br>
<ul class="commentary commentary__bot commentary__bot__inline">
<li>
    <div class="admin__chat__cover">
            <a class="js_show_chat btn btn-sm btn-primary" href="#admin_chat_{{ $order['id'] }}">Показать чат<br> для админов
                @if($adminChats->isNotEmpty())
                    <b style="color: black;">({{ $adminChats->count() }})</b>
                @endif
            </a>
        @include('admin.admin_chat')
    </div>
</li>
</ul>

<br>
<ul class="commentary commentary__bot commentary__bot__inline">
<li>
    <div class="admin__chat__cover">
        <a class="js_show_chat btn btn-sm" style="background-color: #25D366; border-color: #25D366; color: white;" href="#sa_chat_{{ $order['id'] }}">
            <i class="voyager-paper-plane"></i> WhatsApp Чат (SA)
            @if(isset($order['sa_messages']) && count($order['sa_messages']) > 0)
                @php
                    $unreadClientMsgs = $order['sa_messages']->filter(function($msg) {
                        $from = $msg->from_json ? (json_decode($msg->from_json, true) ?: []) : [];
                        $fromType = strtolower((string) data_get($from, 'type', ''));

                        return ($msg->direction === 'inbound' || ($msg->direction === 'outbound' && $fromType === 'bot'))
                            && $msg->status !== 'read';
                    })->count();
                @endphp
                @if($unreadClientMsgs > 0)
                    <b style="color: black; background: white; padding: 2px 5px; border-radius: 10px; font-size: 11px; margin-left: 5px;">{{ $unreadClientMsgs }}</b>
                @endif
            @endif
        </a>
        @include('admin.sa_chat')
    </div>
</li>
</ul>

<br>
<div class="admin__chat__cover">
    <a class="js_show_chat btn btn-sm btn-primary" href="#chat_{{ $order['id'] }}">Показать чат<br> c художником
        @if($orderChats->isNotEmpty())
            <b style="color: black;">({{ $orderChats->count() }})</b>
        @endif
    </a>

    @if($adminChats->isNotEmpty())
        <div class="order-chat-preview">
            @foreach($adminChats as $chatItem)
                @php
                    $chatItemComment = data_get($chatItem, 'comment', '');
                    $chatItemDate = data_get($chatItem, 'created_at');
                    $chatItemAuthorName = trim((string) data_get($chatItem, 'first_name', ''));
                    $chatItemAuthor = $chatItemAuthorName !== '' ? $chatItemAuthorName : 'Администратор';
                    $chatItemDateLabel = $chatItemDate ? \Carbon\Carbon::parse($chatItemDate)->format('d.m.Y H:i') : '';
                @endphp
                <div class="order-chat-preview__item">
                    <div class="order-chat-preview__meta">
                        <span class="order-chat-preview__author" @if($chatItemDateLabel !== '') title="{{ $chatItemDateLabel }}" @endif>{{ $chatItemAuthor }}</span>
                    </div>
                    <div class="order-chat-preview__text">{{ $chatItemComment }}</div>
                </div>
            @endforeach
        </div>
    @endif

    @include('admin.order_chat')
</div>
<br><br>
