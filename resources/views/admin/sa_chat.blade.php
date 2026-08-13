<div class="js_chat__cover mfp-hide white-popup-block" id="sa_chat_{{ $order['id'] }}" style="max-width: 1100px!important; max-height: 90%; overflow: scroll;">
    
    <div style="background: #f1f1f1; padding: 10px; border-radius: 5px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <b>Режим бота SA:</b>
            @php
                $botMode = 'n/a';
                if (isset($order['sa_bot_mode'])) {
                    $botMode = $order['sa_bot_mode'];
                }
                $btnColor = $botMode == 'active' ? 'green' : ($botMode == 'paused' ? 'orange' : 'red');
            @endphp
            <span id="sa_bot_mode_label_{{ $order['id'] }}" style="color: {{ $btnColor }}; font-weight: bold; text-transform: uppercase;">{{ $botMode }}</span>
        </div>
        <div>
            <button class="btn btn-sm" style="background: green; color: white;" onclick="setSaBotMode('resume_bot', {{ $order['id'] }})">Resume (Включить)</button>
            <button class="btn btn-sm" style="background: orange; color: white;" onclick="setSaBotMode('pause_bot', {{ $order['id'] }})">Pause (Пауза)</button>
            <button class="btn btn-sm" style="background: red; color: white;" onclick="setSaBotMode('handoff_to_manager', {{ $order['id'] }})">Handoff (Человек)</button>
        </div>
    </div>

    <!----------------------- SA WhatsApp Feed ------------------->
    <div class="cabinet-elements">
        <div class="c-elementTitle">
            Чат WhatsApp (SA Интеграция)
        </div>
        <div class="cabinet-element">

            <div class="chat-container scroll-box">
                <div class="chat-scroll__container">
                    <div class="chat-inner scroll-block">
                        <div class="chat-inner__block" id="sa_chat_messages_{{ $order['id'] }}">
                            @if(isset($order['sa_messages']) && $order['sa_messages'] && count($order['sa_messages']) > 0)
                                @foreach($order['sa_messages'] as $msg)
                                    @php
                                        $from = $msg->from_json ? (json_decode($msg->from_json, true) ?: []) : [];
                                        $fromType = strtolower((string) data_get($from, 'type', ''));
                                        if ($msg->direction === 'inbound') {
                                            $senderLabel = 'Клиент';
                                        } elseif ($fromType === 'bot') {
                                            $senderLabel = 'Бот';
                                        } elseif ($fromType === 'system') {
                                            $senderLabel = 'Система';
                                        } else {
                                            $senderLabel = 'Менеджер';
                                        }
                                    @endphp
                                    <div class="chat-group" data-msg-id="{{ $msg->id }}">
                                        <div class="chat-item">
                                            <div class="chat-item__row">
                                                <div class="chat-name">
                                                    {{ $senderLabel }}
                                                </div>
                                                <div class="chat-date">
                                                    {{ $msg->sent_at }}
                                                    @if($msg->direction === 'outbound')
                                                        <span style="margin-left: 10px; font-weight: bold; color: #555;">
                                                            @if($msg->status === 'sent')
                                                                <i class="voyager-paper-plane"></i> Отправлено
                                                            @elseif($msg->status === 'delivered')
                                                                <i class="voyager-double-check"></i> Доставлено
                                                            @elseif($msg->status === 'read')
                                                                <i class="voyager-double-check" style="color: #34b7f1;"></i> Прочитано
                                                            @elseif($msg->status === 'failed')
                                                                <i class="voyager-x" style="color: red;"></i> Ошибка
                                                            @else
                                                                ({{ $msg->status }})
                                                            @endif
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="chat-item__body">
                                                <p>{!! nl2br(e($msg->text)) !!}</p>
                                                
                                                @if($msg->attachments_json)
                                                    @php
                                                        $attachments = json_decode($msg->attachments_json, true) ?: [];
                                                    @endphp
                                                    @foreach($attachments as $att)
                                                        @if(isset($att['path']))
                                                            <div style="margin-top: 10px;">
                                                                <a href="{{ Storage::url($att['path']) }}" target="_blank">
                                                                    <img src="{{ Storage::url($att['path']) }}" style="max-width: 200px; border-radius: 5px; border: 1px solid #ddd;" />
                                                                </a>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div style="padding: 20px; text-align: center; color: #777;">Нет истории сообщений WhatsApp</div>
                            @endif

                        </div>
                    </div>
                </div>

                <div class="chat-input" style="background:#f9f9f9; padding:15px; border-top:1px solid #ddd;" id="sa_chat_block_{{ $order['id'] }}">
                    <p class="chat-name">Ответить клиенту в WhatsApp</p>
                    <textarea id="sa_message_text_{{ $order['id'] }}" style="width:100%; height: 60px; padding: 10px;" placeholder="Введите сообщение..."></textarea>
                    
                    <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <label style="margin: 0;">
                            <input type="checkbox" id="sa_message_handoff_{{ $order['id'] }}" value="handoff_to_manager" style="margin-right: 5px;">
                            Отключить бота (Handoff) при отправке
                        </label>
                        <button class="chatSend painter_btn btn-success" style="background: #25D366; border-color: #25D366; color: white;" onclick="sendSaMessage({{ $order['id'] }})">
                            <i class="voyager-paper-plane"></i> Отправить в WA
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
