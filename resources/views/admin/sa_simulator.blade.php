@extends('voyager::master')

@section('page_title', 'Симулятор SA вебхуков')

@section('content')
<div class="page-content container-fluid" style="padding: 20px;">
    
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-title" style="margin-bottom: 20px;">
                <i class="voyager-paper-plane"></i> Симулятор SA вебхуков (Тестовая среда)
            </h1>
            <p>Используйте этот инструмент для проверки корректности приема вебхуков от SalesAI без необходимости подключения реального WhatsApp аккаунта.</p>
            <p style="margin-top:10px;">
                <a href="{{ route('admin.sa.conversations.index') }}" class="btn btn-warning">
                    <i class="voyager-chat"></i> Входящие SA-диалоги
                    @if(!empty($unreadConversationsCount))
                        <span class="badge" style="margin-left:8px; background:#d9534f;">{{ (int) $unreadConversationsCount }}</span>
                    @endif
                </a>
            </p>
        </div>
    </div>

    <div class="row">
        <!-- Боковая панель с пресетами -->
        <div class="col-md-4">
            <div class="panel panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title">Готовые сценарии</h3>
                </div>
                <div class="panel-body">
                    
                    <div class="list-group">
                        <div class="list-group-item" style="background:#f7f7f7; font-weight:600;">Создание лидов и заказов</div>
                        <a href="javascript:void(0);" onclick="loadPreset('new_lead')" class="list-group-item">
                            <h4 class="list-group-item-heading">1. Новый Лид</h4>
                            <p class="list-group-item-text">Эмуляция первого сообщения от нового клиента. Создаст заказ в CRM.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('new_lead_hm43_exact')" class="list-group-item">
                            <h4 class="list-group-item-heading">2. Новый лид HM-43 (Exact Layout)</h4>
                            <p class="list-group-item-text">Создать modular заказ с точной ценой по layout_svg.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('new_lead_coupon')" class="list-group-item">
                            <h4 class="list-group-item-heading">3. Новый лид с купоном</h4>
                            <p class="list-group-item-text">Create-lead сценарий с заполненным `lead.pricing.coupon_code`.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('new_lead_bonus')" class="list-group-item">
                            <h4 class="list-group-item-heading">4. Новый лид со списанием бонусов</h4>
                            <p class="list-group-item-text">Create-lead сценарий для реального пользователя с `use_bonus=true`.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('new_lead_gift_card')" class="list-group-item">
                            <h4 class="list-group-item-heading">4.1. Новый лид Gift Card</h4>
                            <p class="list-group-item-text">Create-lead сценарий покупки подарочной карты `GC-5` с online/email доставкой.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('new_lead_family_constructor')" class="list-group-item">
                            <h4 class="list-group-item-heading">4.2. Новый лид Family Constructor</h4>
                            <p class="list-group-item-text">Create-lead сценарий для `FC-1 /family-constructor` с реальным construct-flow.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('new_lead_hm44_exact')" class="list-group-item">
                            <h4 class="list-group-item-heading">4.3. Новый лид HM-44 (Exact Item)</h4>
                            <p class="list-group-item-text">Create-lead сценарий для `HM-44 /new/gallery` с выбором конкретной картины `gallery_item_id`.</p>
                        </a>
                        <div class="list-group-item" style="background:#f7f7f7; font-weight:600;">Сообщения, бот и стадии</div>
                        <a href="javascript:void(0);" onclick="loadPreset('inbound_msg')" class="list-group-item">
                            <h4 class="list-group-item-heading">5. Сообщение (Текст) в сущ. заказ</h4>
                            <p class="list-group-item-text">Входящее сообщение от клиента.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('inbound_msg_preorder')" class="list-group-item">
                            <h4 class="list-group-item-heading">5.1. Сообщение без заказа</h4>
                            <p class="list-group-item-text">Входящее сообщение в новый dialog только по `conversation_id`, без `lead_id`.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('inbound_msg_existing_conversation')" class="list-group-item">
                            <h4 class="list-group-item-heading">5.2. Сообщение в существующее обращение</h4>
                            <p class="list-group-item-text">Входящее сообщение в уже существующий SA-диалог по текущему `conversation_id`.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('bot_message')" class="list-group-item">
                            <h4 class="list-group-item-heading">5.3. Сообщение бота клиенту</h4>
                            <p class="list-group-item-text">Ответ бота в ту же цепочку: `direction=outbound`, `from.type=bot`.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('inbound_media')" class="list-group-item">
                            <h4 class="list-group-item-heading">6. Сообщение (Картинка)</h4>
                            <p class="list-group-item-text">Текст с прикрепленным файлом-картинкой.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('bot_handoff')" class="list-group-item">
                            <h4 class="list-group-item-heading">7. Эскалация от бота (Handoff)</h4>
                            <p class="list-group-item-text">Имитация команды от бота на досрочное подключение менеджера.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('update_stage')" class="list-group-item">
                            <h4 class="list-group-item-heading">8. Смена стадии Лида</h4>
                            <p class="list-group-item-text">SA передвигает лид в статус 'success' или 'lost'.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('message_status')" class="list-group-item">
                            <h4 class="list-group-item-heading">9. Статус сообщения</h4>
                            <p class="list-group-item-text">Обновление статуса существующего сообщения (`message.status`).</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('send_message')" class="list-group-item">
                            <h4 class="list-group-item-heading">10. CRM -> SA Отправить сообщение</h4>
                            <p class="list-group-item-text">Исходящая команда из CRM с управлением режимом бота.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('send_message_preorder')" class="list-group-item">
                            <h4 class="list-group-item-heading">10.1. CRM -> SA Ответ без заказа</h4>
                            <p class="list-group-item-text">Ответ менеджера в цепочку до создания заказа, только по `conversation_id`.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('pipeline_changed')" class="list-group-item">
                            <h4 class="list-group-item-heading">11. Изменение стадии (Pipeline)</h4>
                            <p class="list-group-item-text">Webhook CRM о смене стадии лида/заказа.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('escalation')" class="list-group-item">
                            <h4 class="list-group-item-heading">12. Эскалация</h4>
                            <p class="list-group-item-text">Запрос эскалации от SA в очередь менеджеров CRM.</p>
                        </a>
                        <div class="list-group-item" style="background:#f7f7f7; font-weight:600;">Каталог и прайсинг</div>
                        <a href="javascript:void(0);" onclick="loadPreset('services_catalog')" class="list-group-item">
                            <h4 class="list-group-item-heading">13. Каталог услуг (GET)</h4>
                            <p class="list-group-item-text">Получить каталог услуг с языком и фильтрами.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('catalog_full')" class="list-group-item">
                            <h4 class="list-group-item-heading">13.1. Полный каталог (GET)</h4>
                            <p class="list-group-item-text">Получить категории, сервисы, размеры, цены и фото товаров одним запросом.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('service_sizes')" class="list-group-item">
                            <h4 class="list-group-item-heading">14. Размеры услуги (GET)</h4>
                            <p class="list-group-item-text">Получить доступные размеры и цены для услуги `HM-*`.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('service_sizes_hm44_exact')" class="list-group-item">
                            <h4 class="list-group-item-heading">15. Размеры HM-44 (Exact Item)</h4>
                            <p class="list-group-item-text">Получить размеры конкретной картины `gallery_item_id`.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('service_price_by_size')" class="list-group-item">
                            <h4 class="list-group-item-heading">16. Цена по размеру (GET)</h4>
                            <p class="list-group-item-text">Получить точную цену для выбранного размера и страны.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('service_price_by_size_hm44_exact')" class="list-group-item">
                            <h4 class="list-group-item-heading">17. Цена HM-44 по размеру (Exact Item)</h4>
                            <p class="list-group-item-text">Получить exact price для конкретной картины `gallery_item_id`.</p>
                        </a>
                        <div class="list-group-item" style="background:#f7f7f7; font-weight:600;">Заказы и поиск</div>
                        <a href="javascript:void(0);" onclick="loadPreset('orders_lookup')" class="list-group-item">
                            <h4 class="list-group-item-heading">18. Поиск заказа по номеру заказа (GET)</h4>
                            <p class="list-group-item-text">Получить информацию по заказу через `order_id/lead_id` или телефон клиента.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('orders_lookup_phone')" class="list-group-item">
                            <h4 class="list-group-item-heading">18.1. Поиск заказов по телефону (GET)</h4>
                            <p class="list-group-item-text">Найти все заказы клиента по `phone/client_phone`, включая случаи с несколькими заказами.</p>
                        </a>
                        <div class="list-group-item" style="background:#f7f7f7; font-weight:600;">Blog API</div>
                        <a href="javascript:void(0);" onclick="loadPreset('blog_categories')" class="list-group-item">
                            <h4 class="list-group-item-heading">19. Категории блога (GET)</h4>
                            <p class="list-group-item-text">Получить категории блога со slug/title/meta по всем языкам.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('blog_authors')" class="list-group-item">
                            <h4 class="list-group-item-heading">19.1. Авторы блога (GET)</h4>
                            <p class="list-group-item-text">Получить авторов блога из blog_authors с переводами имени.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('blog_author_create')" class="list-group-item">
                            <h4 class="list-group-item-heading">19.2. Создать автора блога</h4>
                            <p class="list-group-item-text">Создать автора в blog_authors с переводами имени.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('blog_posts')" class="list-group-item">
                            <h4 class="list-group-item-heading">20. Статьи блога (GET)</h4>
                            <p class="list-group-item-text">Получить статьи с HTML-текстом и переводами по всем языкам.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('blog_post_create')" class="list-group-item">
                            <h4 class="list-group-item-heading">21. Создать статью блога</h4>
                            <p class="list-group-item-text">Создать draft/published статью с отдельными slug под языки.</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('blog_media_upload')" class="list-group-item">
                            <h4 class="list-group-item-heading">21.1. Загрузить медиа блога</h4>
                            <p class="list-group-item-text">Multipart upload картинки в storage/app/public/blog/...</p>
                        </a>
                        <a href="javascript:void(0);" onclick="loadPreset('blog_post_update')" class="list-group-item">
                            <h4 class="list-group-item-heading">22. Обновить статью блога</h4>
                            <p class="list-group-item-text">Частично обновить статус, категории или переводы существующей статьи.</p>
                        </a>
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- Main Payload Editor -->
        <div class="col-md-8">
            <div class="panel panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title">Настройки вебхука</h3>
                </div>
                <div class="panel-body">
                    
                    <form id="simulatorForm">
                        @csrf
                        <div class="form-group">
                            <label>API endpoint (URL)</label>
                            <input type="text" id="endpoint" class="form-control" name="endpoint" placeholder="/api/sa/webhooks/messages" value="/api/sa/leads">
                            <div style="margin-top: 8px;">
                                <span id="endpointMethodBadge" class="label label-primary">POST</span>
                            </div>
                            <small class="text-muted">Относительный URL нашего приложения, куда SA должен присылать данные</small>
                        </div>

                        <div class="form-group">
                            <label>JSON payload (тело запроса)</label>
                            <textarea id="payload" class="form-control" style="font-family: monospace; height: 350px;">
                            </textarea>
                            <small class="text-muted">Структура данных согласно документации SA</small>
                        </div>
                        
                        <button type="submit" class="btn btn-success" style="font-size: 16px; font-weight: bold; width: 100%; padding: 12px;">
                            <i class="voyager-paper-plane"></i> Отправить вебхук
                        </button>
                    </form>

                    <div id="responseBlock" style="margin-top: 20px; display: none;">
                        <h4>Ответ API:</h4>
                        <pre id="responseOutput" style="background: #222; color: #0f0; border-radius: 5px; padding: 15px;"></pre>
                        <div id="validationDetailsBlock" class="alert alert-warning" style="display:none; margin-top:10px;">
                            <strong>Детали VALIDATION_ERROR:</strong>
                            <ul id="validationDetailsList" style="margin-top:8px; margin-bottom:0; padding-left:20px;"></ul>
                        </div>
                    </div>

                    <div id="commandReferenceBlock" style="margin-top: 20px;">
                        <h4>Справка по команде</h4>
                        <div class="panel panel-default" style="margin-bottom: 0;">
                            <div class="panel-body" style="padding: 15px;">
                                <p style="margin-bottom: 8px;"><strong id="docTitle">1. Новый лид</strong></p>
                                <p style="margin-bottom: 8px;">
                                    <strong>Метод:</strong> <code id="docMethod">POST</code><br>
                                    <strong>Endpoint:</strong> <code id="docEndpoint">/api/sa/leads</code>
                                </p>
                                <p style="margin-bottom: 4px;"><strong>Обязательные поля</strong></p>
                                <ul id="docRequired" style="margin-bottom: 10px; padding-left: 20px;"></ul>
                                <p style="margin-bottom: 4px;"><strong>По умолчанию / опционально</strong></p>
                                <ul id="docDefaults" style="margin-bottom: 10px; padding-left: 20px;"></ul>
                                <p style="margin-bottom: 4px;"><strong>Примечания</strong></p>
                                <ul id="docNotes" style="margin-bottom: 0; padding-left: 20px;"></ul>
                                <hr style="margin: 12px 0;">
                                <p style="margin-bottom: 6px;"><strong>cURL для текущей команды</strong></p>
                                <button type="button" id="copyCurlBtn" class="btn btn-default btn-sm" style="margin-bottom: 8px;">Скопировать cURL</button>
                                <pre id="docCurl" style="background:#f8f8f8; border:1px solid #ddd; border-radius:4px; padding:10px; white-space:pre-wrap;"></pre>
                                <p style="margin-bottom: 4px;"><strong>Частые ошибки</strong></p>
                                <ul id="docErrors" style="margin-bottom: 10px; padding-left: 20px;"></ul>
                                <p style="margin-bottom: 4px;"><strong>Коды ответа</strong></p>
                                <ul id="docResponses" style="margin-bottom: 0; padding-left: 20px;"></ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.saSimulatorDefaults = @json($simulatorDefaults ?? []);
</script>

<script>
    var currentPresetKey = 'new_lead';

    function genUuidV4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0;
            var v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    function resolveMethodByEndpoint(endpoint) {
        if (endpoint.indexOf('/api/sa/services-catalog') !== -1 || endpoint.indexOf('/api/sa/catalog-full') !== -1 || endpoint.indexOf('/api/sa/orders/lookup') !== -1 || endpoint.indexOf('/api/blog/categories') !== -1 || endpoint.indexOf('/api/blog/authors?') === 0 || endpoint.indexOf('/api/blog/posts?') === 0) {
            return 'GET';
        }
        if (/^\/api\/sa\/services\/(HM-\d+|GC-5|FC-1)\/(sizes|price-by-size)/.test(endpoint)) {
            return 'GET';
        }
        if (endpoint.indexOf('/api/sa/leads/') === 0 && endpoint !== '/api/sa/leads') {
            return 'PATCH';
        }
        if (/^\/api\/blog\/posts\/\d+/.test(endpoint)) {
            return 'PATCH';
        }
        return 'POST';
    }

    function updateMethodBadge(endpoint) {
        var method = resolveMethodByEndpoint(endpoint || '');
        var badge = document.getElementById('endpointMethodBadge');
        if (!badge) {
            return;
        }
        badge.innerText = method;
        badge.className = 'label ' + (method === 'GET' ? 'label-info' : (method === 'PATCH' ? 'label-warning' : 'label-primary'));
    }

    function buildPresetDocs() {
        return {
            'new_lead': {
                title: '1. Новый лид',
                required: ['idempotency_key', 'source=SA', 'lead.client.phone', 'lead.channel'],
                defaults: [
                    'pipeline.id по умолчанию PIPE-1',
                    'stage.id по умолчанию watching',
                    'service_request опционален',
                    'lead.recipient опционален: name, last_name, phone, address, postal_index',
                    'lead.billing.company опционален: name, name_l, registration_number, legal_address, pnr_nr, bank_name, bank_code, bank_account_code',
                    'lead.pricing опционален: coupon_code или use_bonus=true',
                    'lead.delivery.method: to_the_door|pickup_at_viar_workshop|city_delivery|venipak|pickup_Riga|pickup_Daugavplis|pickup_Daugavpils|email',
                    'lead.delivery.payment: on_delivery|online_paysera|creditcart|google_pay|apple_pay|paypalOnetimePayment|transfer|prepayment|cash_in_office',
                    'для GC-5 /new/gift-card можно передавать options: amount|nominal|size, card_type=online|offline, whom, hide_nom',
                    'для FC-1 /family-constructor можно передавать options: size, holst_id, packaging_id/compl_id, production_mode, ram_id',
                    'для HM-3 /collage можно передавать options: size, form_id/formId, holst_id, decor_id, compl_id, ram_id, production_mode',
                    'для HM-43 /modular-generator можно передавать options: size, form_id/formId, holst_id (finish), packaging_id/compl_id, execution_id/executionId, production_mode, wall_size_mod',
                    'для HM-44 /new/gallery exact item можно передавать options: gallery_item_id|item_id|product_id, size, holst_id, decor_id, packaging_id/compl_id, ram_id, execution_id/executionId, production_mode',
                    'exact modular price: options.layout_svg (preferred, alias collageSvgImage) или options.layout_blocks[{width,height}]'
                ],
                notes: [
                    'lead_id в ответе = orders.id',
                    'дубль idempotency_key вернет status=duplicate',
                    'телефон lead.client.phone нормализуется и валидируется как +########### (7-15 цифр)',
                    'lead.recipient.phone нормализуется так же; если блок recipient передан частично, недостающие поля добираются из payer/delivery',
                    'lead.billing.company мапится в реальные поля orders.ur_*',
                    'legacy aliases по payment: online -> online_paysera, online_banking -> online_paysera, bank -> transfer',
                    'в preset JSON блок lead.pricing добавлен в нейтральном виде: coupon_code=\"\" и use_bonus=false; для реальной проверки меняйте только одно из полей',
                    'coupon_code и use_bonus=true взаимоисключаемы; giftcard/free_delivery/universal и другие типы определяются по реальным флагам записи coupons',
                    'для GC-5 pricing не используется: coupon_code/use_bonus приведут к VALIDATION_ERROR',
                    'для GC-5 nominal берется из реальной таблицы gift_card_noms; online-карта использует delivery.method=email и delivery price = 0',
                    'для FC-1 price/sizes берутся из family_constructor.sizes, а сам basket-item формируется как construct-flow с холстом, упаковкой и сроками',
                    'use_bonus работает только если найден реальный user с положительным балансом bonuses',
                    'free_delivery не меняет сумму товара, а только обнуляет delivery.deliv_price',
                    'для HM-3 /collage create-lead теперь формирует constructor-like item, а не generic service payload',
                    'для HM-43 /modular-generator create-lead теперь формирует construct-style item, близкий к реальному сайту',
                    'если для HM-43 передан layout_svg/layout_blocks, цена считается по реальной геометрии модулей; иначе используется fallback server mapping',
                    'для HM-44 exact item backend умеет работать по конкретному gallery_items.id; alias-поля: gallery_item_id, item_id, product_id',
                    'в текущем header menu видимы 11 продуктов; HM-44 /new/gallery существует в БД, но скрыт (is_show=0); FC-1 идет как отдельный synthetic service вне header_menu'
                ],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'new_lead_coupon': {
                title: '3. Новый лид с купоном',
                required: ['idempotency_key', 'source=SA', 'lead.client.phone', 'lead.channel', 'lead.pricing.coupon_code'],
                defaults: [
                    'service_request по умолчанию заполняется как HM-2 canvas',
                    'coupon_code берется из defaults БД; fallback = 15%',
                    'lead.recipient и lead.billing.company можно дополнять вручную при необходимости',
                    'lead.delivery.payment по умолчанию transfer; допустимые коды как в обычном new_lead'
                ],
                notes: [
                    'использует тот же endpoint /api/sa/leads и тот же contract, что обычный new_lead',
                    'купон должен существовать в таблице coupons и быть валидным для текущего пользователя',
                    'если указать несуществующий coupon_code, API вернет VALIDATION_ERROR',
                    'use_bonus в этом preset оставлен false, потому что coupon_code и use_bonus=true взаимоисключаемы'
                ],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'new_lead_bonus': {
                title: '4. Новый лид со списанием бонусов',
                required: ['idempotency_key', 'source=SA', 'lead.client.phone', 'lead.channel', 'lead.pricing.use_bonus=true'],
                defaults: [
                    'service_request по умолчанию заполняется как HM-2 canvas',
                    'client phone/email/name берутся из defaults БД по реальному пользователю с bonuses > 0',
                    'coupon_code в этом preset пустой',
                    'lead.delivery.payment по умолчанию transfer; допустимые коды как в обычном new_lead'
                ],
                notes: [
                    'preset рассчитан на уже существующего пользователя с бонусами в users.bonuses',
                    'если в defaults не найден пользователь с бонусами, сценарий нужно вручную скорректировать',
                    'API спишет бонусы через обычную механику Orders::saveOrder() и выставит orders.use_bonus = 1'
                ],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'new_lead_gift_card': {
                title: '4.1. Новый лид Gift Card',
                required: ['idempotency_key', 'source=SA', 'lead.client.phone', 'lead.channel', 'lead.service_request.service_id=GC-5'],
                defaults: [
                    'options.amount|nominal|size: если не передан, возьмется первый номинал из gift_card_noms',
                    'options.card_type по умолчанию online',
                    'для online-карты default delivery = email, deliv_price = 0',
                    'lead.pricing не должен содержать coupon_code/use_bonus',
                    'lead.delivery.payment по умолчанию transfer; допустимые коды как в обычном new_lead'
                ],
                notes: [
                    'GC-5 не зависит от header_menu и строится из реальных таблиц gift_card + gift_card_noms',
                    'sizes/price-by-size для GC-5 работают не по размерам, а по номиналам',
                    'для online-карты допустим только delivery.method=email; если метод не передан, API выставит его сам',
                    'в заказ пишется item с pid=5, basketType=5, is_gift_card=1, card_type=online|offline'
                ],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'new_lead_family_constructor': {
                title: '4.2. Новый лид Family Constructor',
                required: ['idempotency_key', 'source=SA', 'lead.client.phone', 'lead.channel', 'lead.service_request.service_id=FC-1'],
                defaults: [
                    'options.size: если не передан, возьмется первый размер из family_constructor.sizes',
                    'options.holst_id по умолчанию берется из gallery_holsts default/first',
                    'options.packaging_id по умолчанию regular packing',
                    'options.production_mode: standard|express',
                    'lead.delivery.payment по умолчанию transfer; допустимые коды как в обычном new_lead'
                ],
                notes: [
                    'FC-1 не зависит от header_menu и строится из реальной таблицы family_constructor',
                    'sizes/price-by-size для FC-1 работают по family_constructor.sizes',
                    'create-lead формирует construct-item с basketType=1, is_construct=1, holst/pack/terms'
                ],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'new_lead_hm44_exact': {
                title: '4.3. Новый лид HM-44 (Exact Item)',
                required: ['idempotency_key', 'source=SA', 'lead.client.phone', 'lead.channel', 'lead.service_request.service_id=HM-44', 'lead.service_request.options.gallery_item_id'],
                defaults: [
                    'gallery_item_id и size берутся из реальной таблицы gallery_items',
                    'если size не передан, backend возьмет первый доступный размер выбранной картины',
                    'execution_id/executionId по умолчанию 2 (print), production_mode по умолчанию standard',
                    'lead.delivery.payment по умолчанию transfer; допустимые коды как в обычном new_lead'
                ],
                notes: [
                    'для HM-44 exact поддерживаются alias-поля gallery_item_id|item_id|product_id',
                    'sizes/price-by-size/create-lead используют custom_size_prices конкретной записи gallery_items.id',
                    'если передан несуществующий gallery_item_id или размер недоступен для выбранной картины, API вернет VALIDATION_ERROR'
                ],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'inbound_msg': {
                title: '5. Сообщение (текст)',
                required: ['event_id (UUID)', 'event_type=message.created', 'idempotency_key', 'data.conversation_id', 'data.message.message_id', 'data.message.direction', 'data.message.sent_at'],
                defaults: ['data.lead_id опционален: сообщение можно принять до создания заказа', 'message.text может быть пустым при вложениях', 'data.message.from.phone опционален, но если передан, то нормализуется и валидируется как +########### (7-15 цифр)'],
                notes: ['сообщение может храниться отдельно от заказа по conversation_id', 'после createLead с тем же lead.external_ids.conversation_id накопленные сообщения будут привязаны к orders.id', 'если заказ уже найден, входящие сообщения синхронизируются в комментарии заказа', 'телефон в data.message.from.phone очищается от пробелов/скобок/дефисов перед сохранением', 'дубль event_id вернет status=duplicate'],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'inbound_msg_preorder': {
                title: '5.1. Сообщение без заказа',
                required: ['event_id (UUID)', 'event_type=message.created', 'idempotency_key', 'data.conversation_id', 'data.message.message_id', 'data.message.direction', 'data.message.sent_at'],
                defaults: ['lead_id специально не передается', 'достаточно unique conversation_id и client phone', 'после createLead с таким же external_ids.conversation_id диалог будет автоматически привязан к заказу'],
                notes: ['используйте этот preset для первого обращения клиента до создания заказа', 'после отправки диалог появится в /admin/sa-conversations и будет отмечен как непрочитанный', 'менеджер сможет ответить в эту же цепочку без lead_id'],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'inbound_msg_existing_conversation': {
                title: '5.2. Сообщение в существующее обращение',
                required: ['event_id (UUID)', 'event_type=message.created', 'idempotency_key', 'data.conversation_id', 'data.message.message_id', 'data.message.direction', 'data.message.sent_at'],
                defaults: ['использует conversation_id из simulator defaults', 'lead_id в этом preset намеренно не передается: продолжение цепочки идет только по conversation_id', 'client phone/name подставляются из текущей conversation'],
                notes: ['используйте этот preset, когда хотите дописать новое входящее сообщение в уже существующую цепочку', 'если нужен сценарий именно с lead_id, используйте базовый preset 5. Сообщение (Текст) в сущ. заказ', 'после отправки unread-счетчик должен обновиться без перезагрузки', 'если открыт /admin/sa-conversations, строка диалога должна обновиться автоматически'],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'bot_message': {
                title: '5.3. Сообщение бота клиенту',
                required: ['event_id (UUID)', 'event_type=message.created', 'idempotency_key', 'data.conversation_id', 'data.message.message_id', 'data.message.direction=outbound', 'data.message.from.type=bot', 'data.message.sent_at'],
                defaults: ['data.lead_id опционален: можно отправлять ответ бота до создания заказа', 'data.message.to.phone берется из текущего client phone', 'status по умолчанию sent'],
                notes: ['используйте этот preset для фиксации ответа SA-бота в общей истории переписки', 'в админке сообщение отображается как Бот, а не как Менеджер', 'outbound от bot помечает dialog как непрочитанный для менеджера', 'вся цепочка связывается по conversation_id'],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'inbound_media': {
                title: '6. Сообщение (вложение)',
                required: ['как для message.created', 'attachments[0].url (доступный URL)'],
                defaults: ['attachments в целом опциональны, но для этого сценария обязательны'],
                notes: ['файлы копируются в локальное хранилище', 'в текст чата может добавляться строка про вложение'],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'bot_handoff': {
                title: '7. Эскалация от бота',
                required: ['event_id (UUID)', 'event_type=crm.bot_control', 'idempotency_key', 'data.lead_id или data.conversation_id', 'data.action', 'data.changed_by.type', 'data.changed_by.id'],
                defaults: ['если заказа еще нет, бот можно остановить по conversation_id', 'если есть только заказ сайта, можно передать только lead_id', 'action: pause_bot|resume_bot|handoff_to_manager'],
                notes: ['action мапится в bot_mode в ответе', 'если conversation уже привязан к заказу, API автоматически вернет data.resolved_lead_id', 'для pre-order диалога resolved_lead_id будет null, но bot_mode сохранится в sa_conversations', 'для заказа без conversation_id режим сохранится в orders.sa_bot_mode'],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'update_stage': {
                title: '8. Смена стадии лида',
                required: ['idempotency_key', 'source=SA', 'update (object)'],
                defaults: ['update.stage.from.id опционален и может быть выведен автоматически', 'можно сразу ставить любой допустимый статус: watching|pegging|in_production|sended|send_lubanas|completed'],
                notes: ['неизвестный lead_id -> LEAD_NOT_FOUND (404)', 'последовательность переходов не требуется: менеджер может сразу установить нужный финальный статус'],
                errors: ['LEAD_NOT_FOUND', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '404 (status=error, code=LEAD_NOT_FOUND)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'message_status': {
                title: '9. Статус сообщения',
                required: ['event_id (UUID)', 'event_type=message.status', 'idempotency_key', 'data.message_id', 'data.status', 'data.status_at'],
                defaults: ['status: received|sent|delivered|read|failed'],
                notes: ['ожидает существующий message_id в sa_messages'],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'send_message': {
                title: '10. CRM -> SA Отправка сообщения',
                required: ['event_id (UUID)', 'event_type=crm.message.send', 'idempotency_key', 'data.conversation_id', 'data.client.phone', 'data.manager.id', 'data.message.client_visible_sender', 'data.message.text'],
                defaults: ['data.lead_id опционален: можно отправлять сообщение в conversation до создания заказа', 'data.bot_control.mode_after_send опционален', 'data.client.phone нормализуется и проверяется по маске +########### (7-15 цифр)'],
                notes: ['если lead_id еще нет, сообщение сохраняется как отдельное sa_message с conversation_id и без orders_id', 'если заказ уже найден, исходящее сообщение синхронизируется в комментарии заказа', 'в ответе result.message_id и result.sa_message_id = реальный сохраненный OUT-* id из sa_messages', 'result.provider_message_id пока null, пока нет реального provider callback/id'],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'send_message_preorder': {
                title: '10.1. CRM -> SA Ответ без заказа',
                required: ['event_id (UUID)', 'event_type=crm.message.send', 'idempotency_key', 'data.conversation_id', 'data.client.phone', 'data.manager.id', 'data.message.client_visible_sender', 'data.message.text'],
                defaults: ['lead_id специально не передается', 'ответ уходит в существующий dialog до createLead', 'bot_control.mode_after_send по умолчанию handoff_to_manager'],
                notes: ['используйте этот preset после сценария 5.1 или любого pre-order входящего сообщения', 'ответ сохранится как outbound sa_message без orders_id, но в той же conversation', 'после создания заказа вся цепочка будет привязана к orders.id автоматически'],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'services_catalog': {
                title: '13. Каталог услуг',
                required: ['обязательных полей в payload нет (только query)'],
                defaults: [
                    'payload не используется; все параметры передаются в query строки endpoint',
                    'поддерживаются query-параметры: lang, updated_since, include_inactive, country, country_code, client_phone, phone',
                    'lang по умолчанию ru',
                    'include_inactive по умолчанию false',
                    'country и phone являются alias к country_code и client_phone',
                    'если country_code не передан, можно передать client_phone или phone для автоопределения pricing country',
                    'если страна не передана и не вычислена по телефону, используется fallback country LV'
                ],
                notes: [
                    'это краткий каталог: возвращает categories[], services[] и bundles[] без вложенного sizes[] и без фото товара',
                    'в services[] возвращается витринная минимальная цена услуги через service.price',
                    'service.price уже может содержать amount, original_amount и is_discounted, если у товара есть скидка',
                    'pricing country определяется в строгом порядке: country_code -> country -> client_phone/phone -> fallback LV',
                    'наценка/множитель берутся из реальной таблицы стран, а язык влияет только на локализацию названий и описаний',
                    'updated_since используется для delta-sync: вернутся только услуги, измененные после указанного ISO-8601 UTC timestamp',
                    'готовые примеры endpoint: /api/sa/services-catalog?lang=ru&include_inactive=false; /api/sa/services-catalog?lang=ru&client_phone=%2B358401234567; /api/sa/services-catalog?lang=en&country_code=DE&include_inactive=true'
                ],
                errors: ['VALIDATION_ERROR'],
                responses: ['200 OK (status=ok)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'catalog_full': {
                title: '13.1. Полный каталог',
                required: ['обязательных полей в payload нет (только query)'],
                defaults: [
                    'payload не используется; все параметры передаются в query строки endpoint',
                    'поддерживаются query-параметры: lang, updated_since, include_inactive, country, country_code, client_phone, phone',
                    'lang по умолчанию ru',
                    'include_inactive по умолчанию false',
                    'country и phone являются alias к country_code и client_phone',
                    'если country_code не передан, можно передать client_phone или phone для автоопределения pricing country',
                    'если страна не передана и не вычислена по телефону, используется fallback country LV'
                ],
                notes: [
                    'это полный каталог для синхронизации одним запросом: categories + services + bundles',
                    'каждый service уже содержит sizes[], photo и photos[]; отдельные запросы по каждому service_id не нужны',
                    'service.price содержит минимальную витринную цену услуги; sizes[*].price содержит цену конкретного размера',
                    'в price object возвращаются amount, original_amount и is_discounted, поэтому в ответе видны и текущая цена, и старая цена по скидке',
                    'meta всегда содержит currency, generated_at, version, country_code и country_multiplier для понимания ценового контекста',
                    'pricing country определяется в строгом порядке: country_code -> country -> client_phone/phone -> fallback LV',
                    'наценка/множитель зависят от страны; язык lang влияет только на локализацию текста и не выбирает pricing country автоматически',
                    'photo - приоритетное главное фото товара; photos[] - весь доступный набор найденных изображений для сервиса',
                    'updated_since используется для delta-sync: вернутся только услуги, измененные после указанного ISO-8601 UTC timestamp',
                    'готовые примеры endpoint: /api/sa/catalog-full?lang=ru&include_inactive=false; /api/sa/catalog-full?lang=ru&client_phone=%2B37129999999; /api/sa/catalog-full?lang=en&country_code=FI&include_inactive=true'
                ],
                errors: ['VALIDATION_ERROR'],
                responses: ['200 OK (status=ok)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'orders_lookup': {
                title: '18. Поиск заказа по номеру заказа',
                required: ['query-параметр order_id или lead_id'],
                defaults: [
                    'payload не используется; все параметры передаются в query строки endpoint',
                    'lead_id является alias к order_id, потому что lead_id в CRM = orders.id',
                    'client_phone является alias к phone',
                    'lang по умолчанию ru; поддерживает те же языки, что каталог',
                    'lang влияет только на человекочитаемые подписи status.title/payment.status_title, коды остаются неизменными',
                    'телефон автоматически очищается от пробелов, скобок, дефисов и других лишних символов',
                    'если по телефону найдено несколько заказов, API возвращает все заказы от новых к старым'
                ],
                notes: [
                    'используйте этот endpoint, когда клиент пишет с другого номера и бот уточнил номер заказа или старый телефон',
                    'по order_id возвращается конкретный заказ; по phone/client_phone идет поиск по users.phone, delivery.phone, delivery.payer_phone и SA phone',
                    'ответ содержит status, payment, pricing, client, recipient, delivery, billing, comments, products, artist и SA-связку conversation_id/bot_mode',
                    'для юр. лиц billing.company берется из реальных orders.ur_*: название, рег. номер, юр. адрес, VAT/PVN и банковские реквизиты',
                    'это lookup endpoint, поэтому idempotency_key/event_id не нужны',
                    'готовые примеры endpoint: /api/sa/orders/lookup?order_id=12345&lang=ru; /api/sa/orders/lookup?lead_id=12345&lang=en; /api/sa/orders/lookup?phone=%2B37129999999&lang=lv'
                ],
                errors: ['VALIDATION_ERROR', 'ORDERS_TABLE_NOT_FOUND'],
                responses: ['200 OK (status=ok)', '400 (status=error, code=VALIDATION_ERROR)', '500 (status=error, code=ORDERS_TABLE_NOT_FOUND)', '401/403 (unauthorized)']
            },
            'orders_lookup_phone': {
                title: '18.1. Поиск заказов по телефону',
                required: ['query-параметр phone или client_phone'],
                defaults: [
                    'payload не используется; все параметры передаются в query строки endpoint',
                    'client_phone является alias к phone',
                    'телефон автоматически очищается от пробелов, скобок, дефисов и других лишних символов',
                    'lang по умолчанию ru; поддерживает те же языки, что каталог',
                    'если заказов несколько, API возвращает все заказы от новых к старым'
                ],
                notes: [
                    'используйте этот preset, когда клиент пишет с нового номера, а бот уточнил старый телефон заказа',
                    'поиск идет по users.phone, delivery.phone, delivery.payer_phone и SA phone',
                    'ответ содержит тот же формат, что поиск по order_id: status, payment, pricing, products, artist, delivery, billing и comments',
                    'готовые примеры endpoint: /api/sa/orders/lookup?phone=%2B37129999999&lang=ru; /api/sa/orders/lookup?client_phone=%2B37258236186&lang=en'
                ],
                errors: ['VALIDATION_ERROR', 'ORDERS_TABLE_NOT_FOUND'],
                responses: ['200 OK (status=ok)', '400 (status=error, code=VALIDATION_ERROR)', '500 (status=error, code=ORDERS_TABLE_NOT_FOUND)', '401/403 (unauthorized)']
            },
            'blog_categories': {
                title: '19. Категории блога',
                required: ['обязательных полей в payload нет (только query)'],
                defaults: [
                    'payload не используется; все параметры передаются в query строки endpoint',
                    'updated_since опционален: ISO-8601 дата для delta-sync'
                ],
                notes: [
                    'возвращает blog_categories.id и translations по всем языкам сайта',
                    'slug/title/meta_title/meta_desc/seo отдаются отдельно для lv|lt|pl|ru|de|en|ee',
                    'используется внешней системой генерации для выбора категории статьи'
                ],
                errors: ['VALIDATION_ERROR'],
                responses: ['200 OK (status=ok)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'blog_authors': {
                title: '19.1. Авторы блога',
                required: ['обязательных полей в payload нет (только query)'],
                defaults: [
                    'payload не используется; updated_since опционален в query',
                    'возвращает id автора, имя, image/image_path, sort и translations'
                ],
                notes: [
                    'используется для выбора author_id при создании или обновлении статьи',
                    'если author_id не передать при создании, API возьмет первого автора по sort/id'
                ],
                errors: ['VALIDATION_ERROR'],
                responses: ['200 OK (status=ok)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'blog_author_create': {
                title: '19.2. Создать автора блога',
                required: ['либо name, либо translations.{locale}.name'],
                defaults: [
                    'lang по умолчанию en для single-language payload',
                    'image опционален: URL/path, например результат POST /api/blog/media',
                    'sort опционален, по умолчанию 0'
                ],
                notes: [
                    'создает запись в blog_authors и переводы name',
                    'повтор по такому же name или translations.*.name вернет status=duplicate',
                    'полученный data.author.id можно передавать как author_id при создании статьи'
                ],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['201 Created (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'blog_posts': {
                title: '20. Статьи блога',
                required: ['обязательных полей в payload нет (только query)'],
                defaults: [
                    'payload не используется; все параметры передаются в query строки endpoint',
                    'per_page по умолчанию 100, максимум 500',
                    'include_text по умолчанию true; false убирает HTML text',
                    'include_drafts по умолчанию true; false возвращает только published',
                    'category_id и updated_since опциональны'
                ],
                notes: [
                    'возвращает статьи с category_ids, categories, image, status, tags и translations',
                    'translations.*.text содержит HTML статьи',
                    'нужно генератору, чтобы не дублировать уже существующие темы'
                ],
                errors: ['VALIDATION_ERROR'],
                responses: ['200 OK (status=ok)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'blog_post_create': {
                title: '21. Создать статью блога',
                required: ['categories[min=1]', 'либо slug/title/content, либо translations.{locale}.slug/title/content'],
                defaults: [
                    'status по умолчанию draft',
                    'status: draft|published|publish',
                    'lang по умолчанию en для single-language payload',
                    'author_id опционален; если передан, должен существовать в blog_authors',
                    'tags, excerpt, image, image_preview, featured_media, meta опциональны',
                    'is_idea, is_stories, is_blogwant опциональны: 0/1 или true/false'
                ],
                notes: [
                    'поддерживает WP-подобный payload и расширенный multilingual payload',
                    'для отдельного slug под язык используйте translations.ru.slug, translations.en.slug и т.д.',
                    'is_idea = колонка "Идея", is_stories = "Истории людей", is_blogwant = "Как создают шедевры"',
                    'draft не попадает в публичный блог и sitemap',
                    'повтор по idempotency_key или существующему slug вернет status=duplicate',
                    'для новой картинки сначала используйте POST /api/blog/media и передайте data.media.path в image или image_preview'
                ],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['201 Created (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'blog_media_upload': {
                title: '21.1. Загрузить медиа блога',
                required: ['multipart/form-data поле file'],
                defaults: [
                    'разрешенные MIME: image/jpeg, image/png, image/webp, image/gif',
                    'максимальный размер: 5 MB',
                    'alt, title, slug опциональны',
                    'файл сохраняется в storage/app/public/blog/YYYY/MM'
                ],
                notes: [
                    'этот endpoint принимает multipart upload, а не JSON',
                    'generic кнопка "Отправить вебхук" в этом симуляторе отправляет JSON, поэтому для media используйте cURL из блока справки',
                    'ответ возвращает data.media.path и data.media.url; path можно передавать в image при создании/обновлении статьи'
                ],
                errors: ['VALIDATION_ERROR'],
                responses: ['201 Created (status=ok)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'blog_post_update': {
                title: '22. Обновить статью блога',
                required: ['post_id в endpoint path'],
                defaults: [
                    'payload частичный: можно отправить только изменяемые поля',
                    'status: draft|published|publish',
                    'author_id можно передать для смены автора',
                    'categories заменяют текущие категории, если переданы',
                    'is_idea, is_stories, is_blogwant обновляют соответствующие колонки Voyager',
                    'translations обновляют только указанные языки и поля'
                ],
                notes: [
                    'пример публикации черновика: {"status":"published"}',
                    'пример смены slug: translations.en.slug = new-slug',
                    'если новый slug занят другой статьей, API вернет VALIDATION_ERROR',
                    'если post_id не найден, API вернет POST_NOT_FOUND'
                ],
                errors: ['POST_NOT_FOUND', 'VALIDATION_ERROR'],
                responses: ['200 OK (status=ok)', '404 (status=error, code=POST_NOT_FOUND)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'pipeline_changed': {
                title: '11. Изменение стадии (Pipeline)',
                required: ['event_id (UUID)', 'event_type=crm.lead.stage_changed', 'idempotency_key', 'data.lead_id', 'data.pipeline.id', 'data.stage.to.id', 'data.changed_by.type', 'data.changed_by.id'],
                defaults: ['data.stage.from.id опционален', 'data.bot_control.mode опционален'],
                notes: ['использует реальные статусы orders.status: watching, pegging, in_production, sended, send_lubanas, completed', 'старые STG-* больше не поддерживаются и вернут VALIDATION_ERROR', 'можно сразу переводить в любой допустимый статус без соблюдения последовательности'],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'escalation': {
                title: '12. Эскалация',
                required: ['idempotency_key', 'source=SA', 'escalation.lead_id', 'escalation.conversation_id', 'escalation.priority', 'escalation.reason_code', 'escalation.dialog.channel', 'escalation.dialog.client', 'escalation.dialog.messages[min=1]'],
                defaults: ['bot_control.set_mode опционален', 'confidence опционален (0..1)', 'escalation.dialog.client.phone опционален, но если передан, то нормализуется и валидируется как +########### (7-15 цифр)'],
                notes: ['создает задачу эскалации и сохраняет payload в sa_escalations', 'телефон в escalation.dialog.client.phone очищается перед сохранением в dialog_json'],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'service_sizes': {
                title: '14. Размеры услуги',
                required: ['service_id in endpoint path (HM-*|GC-5|FC-1)'],
                defaults: ['country_code опционален', 'если матрицы нет, вернется пустой sizes[]', 'для HM-44 можно передать query gallery_item_id|item_id|product_id'],
                notes: ['GET endpoint, payload не используется', 'для GC-5 endpoint возвращает номиналы, format=nominal и width/height = null', 'для FC-1 endpoint возвращает размеры из family_constructor.sizes', 'для HM-44 exact endpoint вернет sizes конкретной записи gallery_items.id'],
                errors: ['SERVICE_NOT_FOUND', 'GALLERY_ITEM_NOT_FOUND', 'VALIDATION_ERROR'],
                responses: ['200 OK (status=ok)', '404 (status=error, code=SERVICE_NOT_FOUND)', '404 (status=error, code=GALLERY_ITEM_NOT_FOUND)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'service_sizes_hm44_exact': {
                title: '15. Размеры HM-44 (Exact Item)',
                required: ['service_id=HM-44 в endpoint path', 'gallery_item_id|item_id|product_id в query'],
                defaults: ['gallery_item_id и size берутся из реальной таблицы gallery_items', 'country_code по умолчанию LV'],
                notes: ['GET endpoint, payload не используется', 'использует custom_size_prices конкретной записи gallery_items.id', 'если gallery_item_id не найден, API вернет GALLERY_ITEM_NOT_FOUND'],
                errors: ['SERVICE_NOT_FOUND', 'GALLERY_ITEM_NOT_FOUND', 'VALIDATION_ERROR'],
                responses: ['200 OK (status=ok)', '404 (status=error, code=SERVICE_NOT_FOUND)', '404 (status=error, code=GALLERY_ITEM_NOT_FOUND)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'service_price_by_size': {
                title: '16. Цена по размеру',
                required: ['service_id в endpoint path (HM-*|GC-5|FC-1)', 'size query param (WxH или nominal для GC-5)'],
                defaults: ['country_code опционален', 'для HM-44 можно передать query gallery_item_id|item_id|product_id'],
                notes: ['неизвестный service -> SERVICE_NOT_FOUND (404)', 'неизвестный size -> SIZE_NOT_FOUND (404)', 'для GC-5 цена равна номиналу и не умножается на country multiplier', 'для FC-1 цена берется из family_constructor.sizes и умножается на country multiplier', 'для HM-44 exact цена берется из custom_size_prices конкретной записи gallery_items.id'],
                errors: ['SERVICE_NOT_FOUND', 'GALLERY_ITEM_NOT_FOUND', 'SIZE_NOT_FOUND', 'VALIDATION_ERROR'],
                responses: ['200 OK (status=ok)', '404 (status=error, code=SERVICE_NOT_FOUND)', '404 (status=error, code=GALLERY_ITEM_NOT_FOUND)', '404 (status=error, code=SIZE_NOT_FOUND)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'service_price_by_size_hm44_exact': {
                title: '17. Цена HM-44 по размеру (Exact Item)',
                required: ['service_id=HM-44 в endpoint path', 'size query param', 'gallery_item_id|item_id|product_id в query'],
                defaults: ['gallery_item_id и size берутся из реальной таблицы gallery_items', 'country_code по умолчанию LV'],
                notes: ['GET endpoint, payload не используется', 'использует custom_size_prices конкретной записи gallery_items.id', 'если размер не найден у выбранной картины, API вернет SIZE_NOT_FOUND'],
                errors: ['SERVICE_NOT_FOUND', 'GALLERY_ITEM_NOT_FOUND', 'SIZE_NOT_FOUND', 'VALIDATION_ERROR'],
                responses: ['200 OK (status=ok)', '404 (status=error, code=SERVICE_NOT_FOUND)', '404 (status=error, code=GALLERY_ITEM_NOT_FOUND)', '404 (status=error, code=SIZE_NOT_FOUND)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            },
            'new_lead_hm43_exact': {
                title: '2. Новый лид HM-43 (точная modular цена)',
                required: ['idempotency_key', 'source=SA', 'lead.client.phone', 'lead.channel', 'lead.service_request.service_id=HM-43', 'lead.service_request.options.size', 'lead.service_request.options.layout_svg'],
                defaults: [
                    'country_code по умолчанию LV в примере',
                    'execution_id/executionId по умолчанию 1',
                    'form_id/formId по умолчанию 1',
                    'lead.recipient и lead.billing.company можно передавать так же, как в обычном new_lead',
                    'lead.pricing можно передавать так же, как в обычном new_lead',
                    'lead.delivery.payment по умолчанию transfer; допустимые коды как в обычном new_lead',
                    'layout_svg preferred, collageSvgImage поддерживается как alias',
                    'если SVG нет, можно передать options.layout_blocks[{width,height}] как JSON fallback'
                ],
                notes: [
                    'используйте этот preset для exact pricing parity с modular-generator',
                    'цена считается по геометрии модулей внутри SVG, а не только по size',
                    'в orders.items сохраняются sa_modular_price_mode и sa_modular_layout_area',
                    'в preset JSON блок lead.pricing добавлен в нейтральном виде: coupon_code=\"\" и use_bonus=false; для реальной проверки меняйте только одно из полей',
                    'coupon_code/use_bonus обрабатываются тем же pricing contract, что и в обычном create-lead',
                    'если убрать layout_svg/layout_blocks, HM-43 уйдет в fallback server mapping'
                ],
                errors: ['VALIDATION_ERROR', 'duplicate'],
                responses: ['200 OK (status=ok)', '200 OK (status=duplicate)', '400 (status=error, code=VALIDATION_ERROR)', '401/403 (unauthorized)']
            }
        };
    }

    function renderList(targetId, items) {
        var el = document.getElementById(targetId);
        if (!el) {
            return;
        }
        el.innerHTML = '';
        (items || []).forEach(function(item) {
            var li = document.createElement('li');
            li.innerText = item;
            el.appendChild(li);
        });
    }

    function renderPresetDoc(key) {
        var docs = buildPresetDocs();
        var doc = docs[key] || {
            title: 'Пользовательская команда',
            required: ['Смотрите правила валидации endpoint'],
            defaults: ['Зависит от endpoint'],
            notes: ['Источник истины: выбранный payload'],
            errors: ['VALIDATION_ERROR'],
            responses: ['200 OK', '400 VALIDATION_ERROR', '401/403 unauthorized']
        };
        var endpoint = document.getElementById('endpoint').value || '';
        var method = resolveMethodByEndpoint(endpoint);

        var t = document.getElementById('docTitle');
        var m = document.getElementById('docMethod');
        var e = document.getElementById('docEndpoint');
        if (t) t.innerText = doc.title;
        if (m) m.innerText = method;
        if (e) e.innerText = endpoint;

        renderList('docRequired', doc.required);
        renderList('docDefaults', doc.defaults);
        renderList('docNotes', doc.notes);
        renderList('docErrors', doc.errors);
        renderList('docResponses', doc.responses);

        var curl = buildCurlCommand(endpoint);
        var curlEl = document.getElementById('docCurl');
        if (curlEl) {
            curlEl.innerText = curl;
        }
    }

    function buildCurlCommand(endpoint) {
        var payload = document.getElementById('payload').value || '{}';
        var base = '{{ rtrim(config("app.url"), "/") }}';
        var fullUrl = base + endpoint;
        var method = resolveMethodByEndpoint(endpoint || '');
        if (endpoint === '/api/blog/media') {
            return [
                'curl -X POST \"' + fullUrl + '\"',
                '  -H \"X-Api-Key: {{ config("services.sa_integration.api_key", "test-key") }}\"',
                '  -F \"file=@C:/path/to/image.jpg\"',
                '  -F \"alt=Article image alt\"',
                '  -F \"title=Article image title\"',
                '  -F \"slug=article-image\"'
            ].join(' \\\n');
        }

        var lines = [
            'curl -X ' + method + ' \"' + fullUrl + '\"',
            '  -H \"X-Api-Key: {{ config("services.sa_integration.api_key", "test-key") }}\"',
            '  -H \"Content-Type: application/json\"'
        ];

        if (method !== 'GET') {
            var oneLinePayload = payload.replace(/\r?\n/g, ' ').replace(/\s+/g, ' ').trim();
            lines.push('  -d \'' + oneLinePayload.replace(/'/g, "'\\''") + '\'');
        }

        return lines.join(' \\\n');
    }

    function buildPresets() {
        var now = Date.now();
        var defaults = window.saSimulatorDefaults || {};
        var leadId = defaults.lead_id || null;
        var conversationId = defaults.conversation_id || null;
        var knownMessageId = defaults.message_id || null;
        var clientPhone = defaults.client_phone || null;
        var clientName = defaults.client_name || null;
        var serviceId = defaults.service_id || null;
        var sampleSize = defaults.sample_size || null;
        var couponCode = defaults.coupon_code || '15%';
        var bonusClientPhone = defaults.bonus_client_phone || clientPhone || null;
        var bonusClientName = defaults.bonus_client_name || clientName || 'Bonus Client';
        var bonusClientEmail = defaults.bonus_client_email || null;
        var giftCardNominal = defaults.gift_card_nominal || '20';
        var familyConstructorSize = defaults.family_constructor_size || '40x60';
        var galleryItemId = defaults.gallery_item_id || null;
        var galleryItemSize = defaults.gallery_item_size || '30x20';
        var blogPostId = defaults.blog_post_id || 1;

        return {
            'new_lead': {
                endpoint: '/api/sa/leads',
                payload: {
                    "idempotency_key": "lead-sync-" + now,
                    "source": "SA",
                    "lead": {
                        "channel": "whatsapp",
                        "client": {
                            "phone": "+380" + Math.floor(10000000 + Math.random() * 90000000),
                            "name": "Test Lead"
                        },
                        "fields": {
                            "question": "How much is portrait 50x70?"
                        },
                        "pricing": {
                            "coupon_code": "",
                            "use_bonus": false
                        },
                        "external_ids": {
                            "conversation_id": conversationId
                        }
                    }
                }
            },
            'new_lead_coupon': {
                endpoint: '/api/sa/leads',
                payload: {
                    "idempotency_key": "lead-coupon-" + now,
                    "source": "SA",
                    "lead": {
                        "channel": "whatsapp",
                        "external_ids": {
                            "conversation_id": "CONV-COUPON-" + now
                        },
                        "client": {
                            "phone": "+37129" + String(now).slice(-6),
                            "name": "Coupon Test",
                            "email": "coupon.test." + now + "@example.com"
                        },
                        "service_request": {
                            "service_id": "HM-2",
                            "country_code": "LV",
                            "notes": "Simulator create-lead preset with coupon",
                            "options": {
                                "size": "60x80"
                            }
                        },
                        "pricing": {
                            "coupon_code": couponCode,
                            "use_bonus": false
                        },
                        "delivery": {
                            "method": "to_the_door",
                            "payment": "transfer",
                            "city": "Riga",
                            "address": "Coupon Preset Street 1",
                            "postal_index": "LV-1010",
                            "country": "LV"
                        },
                        "fields": {
                            "email": "coupon.test." + now + "@example.com",
                            "city": "Riga",
                            "country_code": "LV",
                            "postal_index": "LV-1010",
                            "address": "Coupon Preset Street 1"
                        }
                    }
                }
            },
            'new_lead_bonus': {
                endpoint: '/api/sa/leads',
                payload: {
                    "idempotency_key": "lead-bonus-" + now,
                    "source": "SA",
                    "lead": {
                        "channel": "whatsapp",
                        "external_ids": {
                            "conversation_id": "CONV-BONUS-" + now
                        },
                        "client": {
                            "phone": bonusClientPhone,
                            "name": bonusClientName,
                            "email": bonusClientEmail
                        },
                        "service_request": {
                            "service_id": "HM-2",
                            "country_code": "LV",
                            "notes": "Simulator create-lead preset with bonus usage",
                            "options": {
                                "size": "60x80"
                            }
                        },
                        "pricing": {
                            "coupon_code": "",
                            "use_bonus": true
                        },
                        "delivery": {
                            "method": "to_the_door",
                            "payment": "transfer",
                            "city": "Riga",
                            "address": "Bonus Preset Street 1",
                            "postal_index": "LV-1010",
                            "country": "LV"
                        },
                        "fields": {
                            "email": bonusClientEmail,
                            "city": "Riga",
                            "country_code": "LV",
                            "postal_index": "LV-1010",
                            "address": "Bonus Preset Street 1"
                        }
                    }
                }
            },
            'new_lead_gift_card': {
                endpoint: '/api/sa/leads',
                payload: {
                    "idempotency_key": "lead-gift-card-" + now,
                    "source": "SA",
                    "lead": {
                        "channel": "whatsapp",
                        "external_ids": {
                            "conversation_id": "CONV-GC5-" + now
                        },
                        "client": {
                            "phone": "+37129" + String(now).slice(-6),
                            "name": "Gift Card Test",
                            "email": "gift.card." + now + "@example.com"
                        },
                        "service_request": {
                            "service_id": "GC-5",
                            "country_code": "LV",
                            "notes": "Simulator create-lead preset for gift card purchase",
                            "options": {
                                "amount": giftCardNominal,
                                "card_type": "online"
                            }
                        },
                        "delivery": {
                            "method": "email",
                            "payment": "transfer",
                            "country": "LV"
                        },
                        "fields": {
                            "email": "gift.card." + now + "@example.com",
                            "country_code": "LV"
                        }
                    }
                }
            },
            'inbound_msg': {
                endpoint: '/api/sa/webhooks/messages',
                payload: {
                    "event_id": genUuidV4(),
                    "event_type": "message.created",
                    "idempotency_key": "msg-dedup-" + now,
                    "occurred_at": new Date().toISOString(),
                    "source": "SA",
                    "data": {
                        "lead_id": leadId || undefined,
                        "conversation_id": conversationId,
                        "channel": "whatsapp",
                        "message": {
                            "message_id": "MSG-IN-" + now,
                            "direction": "inbound",
                            "from": {
                                "type": "client",
                                "phone": clientPhone,
                                "name": clientName
                            },
                            "to": {
                                "type": "bot",
                                "id": "BOT-1"
                            },
                            "text": "How much for 50x70?",
                            "attachments": [],
                            "sent_at": new Date().toISOString(),
                            "status": "received"
                        }
                    }
                }
            },
            'inbound_msg_preorder': {
                endpoint: '/api/sa/webhooks/messages',
                payload: {
                    "event_id": genUuidV4(),
                    "event_type": "message.created",
                    "idempotency_key": "msg-preorder-" + now,
                    "occurred_at": new Date().toISOString(),
                    "source": "SA",
                    "data": {
                        "conversation_id": "PREORDER-CONV-" + now,
                        "channel": "whatsapp",
                        "message": {
                            "message_id": "MSG-PREORDER-" + now,
                            "direction": "inbound",
                            "from": {
                                "type": "client",
                                "phone": clientPhone || ("+3712" + Math.floor(1000000 + Math.random() * 9000000)),
                                "name": clientName || "Preorder Client"
                            },
                            "to": {
                                "type": "bot",
                                "id": "BOT-1"
                            },
                            "text": "Здравствуйте, хочу уточнить стоимость до оформления заказа",
                            "attachments": [],
                            "sent_at": new Date().toISOString(),
                            "status": "received"
                        }
                    }
                }
            },
            'inbound_msg_existing_conversation': {
                endpoint: '/api/sa/webhooks/messages',
                payload: {
                    "event_id": genUuidV4(),
                    "event_type": "message.created",
                    "idempotency_key": "msg-existing-conversation-" + now,
                    "occurred_at": new Date().toISOString(),
                    "source": "SA",
                    "data": {
                        "conversation_id": conversationId || ("PREORDER-CONV-" + now),
                        "channel": "whatsapp",
                        "message": {
                            "message_id": "MSG-EXISTING-" + now,
                            "direction": "inbound",
                            "from": {
                                "type": "client",
                                "phone": clientPhone || ("+3712" + Math.floor(1000000 + Math.random() * 9000000)),
                                "name": clientName || "Existing Client"
                            },
                            "to": {
                                "type": "bot",
                                "id": "BOT-1"
                            },
                            "text": "Это новое сообщение в уже существующее обращение",
                            "attachments": [],
                            "sent_at": new Date().toISOString(),
                            "status": "received"
                        }
                    }
                }
            },
            'bot_message': {
                endpoint: '/api/sa/webhooks/messages',
                payload: {
                    "event_id": genUuidV4(),
                    "event_type": "message.created",
                    "idempotency_key": "msg-bot-" + now,
                    "occurred_at": new Date().toISOString(),
                    "source": "SA",
                    "data": {
                        "lead_id": leadId || undefined,
                        "conversation_id": conversationId || ("PREORDER-CONV-" + now),
                        "channel": "whatsapp",
                        "message": {
                            "message_id": "MSG-BOT-" + now,
                            "direction": "outbound",
                            "from": {
                                "type": "bot",
                                "id": "SA-BOT-1",
                                "name": "SA Bot"
                            },
                            "to": {
                                "type": "client",
                                "phone": clientPhone || ("+3712" + Math.floor(1000000 + Math.random() * 9000000))
                            },
                            "text": "Здравствуйте! Подскажите, какой размер холста вас интересует?",
                            "attachments": [],
                            "sent_at": new Date().toISOString(),
                            "status": "sent"
                        }
                    }
                }
            },
            'inbound_media': {
                endpoint: '/api/sa/webhooks/messages',
                payload: {
                    "event_id": genUuidV4(),
                    "event_type": "message.created",
                    "idempotency_key": "msg-dedup-media-" + now,
                    "occurred_at": new Date().toISOString(),
                    "source": "SA",
                    "data": {
                        "lead_id": leadId || undefined,
                        "conversation_id": conversationId,
                        "channel": "whatsapp",
                        "message": {
                            "message_id": "MSG-MEDIA-" + now,
                            "direction": "inbound",
                            "from": {
                                "type": "client",
                                "phone": clientPhone,
                                "name": clientName
                            },
                            "to": {
                                "type": "bot",
                                "id": "BOT-1"
                            },
                            "text": "",
                            "attachments": [
                                {
                                    "url": "{{ rtrim(config('app.url'), '/') }}/storage/temp_test_image.jpg",
                                    "name": "temp_test_image.jpg",
                                    "mime": "image/jpeg",
                                    "size": 1024
                                }
                            ],
                            "sent_at": new Date().toISOString(),
                            "status": "received"
                        }
                    }
                }
            },
            'bot_handoff': {
                endpoint: '/api/crm/webhooks/bot-control',
                payload: {
                    "event_id": genUuidV4(),
                    "event_type": "crm.bot_control",
                    "idempotency_key": "handoff-" + now,
                    "occurred_at": new Date().toISOString(),
                    "source": "CRM",
                    "data": {
                        "lead_id": leadId || undefined,
                        "conversation_id": conversationId,
                        "action": "handoff_to_manager",
                        "changed_by": {
                            "type": "manager",
                            "id": "1",
                            "name": "Admin"
                        }
                    }
                }
            },
            'update_stage': {
                endpoint: leadId ? ('/api/sa/leads/' + leadId) : '/api/sa/leads/',
                payload: {
                    "idempotency_key": "lead-update-" + now,
                    "source": "SA",
                    "update": {
                        "stage": {
                            "from": {"id": "watching"},
                            "id": "pegging"
                        }
                    }
                }
            },
            'message_status': {
                endpoint: '/api/sa/webhooks/messages',
                payload: {
                    "event_id": genUuidV4(),
                    "event_type": "message.status",
                    "idempotency_key": "msg-status-" + now,
                    "occurred_at": new Date().toISOString(),
                    "source": "SA",
                    "data": {
                        "lead_id": leadId,
                        "conversation_id": conversationId,
                        "channel": "whatsapp",
                        "message_id": knownMessageId,
                        "status": "delivered",
                        "status_at": new Date().toISOString()
                    }
                }
            },
            'send_message': {
                endpoint: '/api/crm/webhooks/send-message',
                payload: {
                    "event_id": genUuidV4(),
                    "event_type": "crm.message.send",
                    "idempotency_key": "crm-send-" + now,
                    "occurred_at": new Date().toISOString(),
                    "source": "CRM",
                    "data": {
                        "lead_id": leadId,
                        "conversation_id": conversationId,
                        "channel": "whatsapp",
                        "client": {
                            "phone": clientPhone
                        },
                        "manager": {
                            "id": "1",
                            "name": "Admin"
                        },
                        "message": {
                            "client_visible_sender": "manager",
                            "text": "Manual CRM outbound message"
                        },
                        "bot_control": {
                            "mode_after_send": "handoff_to_manager"
                        }
                    }
                }
            },
            'send_message_preorder': {
                endpoint: '/api/crm/webhooks/send-message',
                payload: {
                    "event_id": genUuidV4(),
                    "event_type": "crm.message.send",
                    "idempotency_key": "crm-send-preorder-" + now,
                    "occurred_at": new Date().toISOString(),
                    "source": "CRM",
                    "data": {
                        "conversation_id": conversationId || ("PREORDER-CONV-" + now),
                        "channel": "whatsapp",
                        "client": {
                            "phone": clientPhone || ("+3712" + Math.floor(1000000 + Math.random() * 9000000))
                        },
                        "manager": {
                            "id": "1",
                            "name": "Admin"
                        },
                        "message": {
                            "client_visible_sender": "manager",
                            "text": "Здравствуйте! Подскажу все детали и помогу оформить заказ."
                        },
                        "bot_control": {
                            "mode_after_send": "handoff_to_manager"
                        }
                    }
                }
            },
            'services_catalog': {
                endpoint: '/api/sa/services-catalog?lang=en&include_inactive=false',
                payload: {}
            },
            'catalog_full': {
                endpoint: '/api/sa/catalog-full?lang=en&include_inactive=false',
                payload: {}
            },
            'orders_lookup': {
                endpoint: '/api/sa/orders/lookup?order_id=' + encodeURIComponent(leadId || '17335') + '&lang=ru',
                payload: {}
            },
            'orders_lookup_phone': {
                endpoint: '/api/sa/orders/lookup?phone=' + encodeURIComponent(clientPhone || '+37125618028') + '&lang=ru',
                payload: {}
            },
            'blog_categories': {
                endpoint: '/api/blog/categories',
                payload: {}
            },
            'blog_authors': {
                endpoint: '/api/blog/authors?updated_since=',
                payload: {}
            },
            'blog_author_create': {
                endpoint: '/api/blog/authors',
                payload: {
                    "image": "blog/authors/ai-editor.jpg",
                    "sort": 10,
                    "translations": {
                        "ru": {
                            "name": "AI редактор"
                        },
                        "en": {
                            "name": "AI editor"
                        }
                    }
                }
            },
            'blog_posts': {
                endpoint: '/api/blog/posts?per_page=5&include_text=true&include_drafts=true',
                payload: {}
            },
            'blog_post_create': {
                endpoint: '/api/blog/posts',
                payload: {
                    "idempotency_key": "blog-post-" + now,
                    "status": "draft",
                    "categories": [2],
                    "author_id": 1,
                    "image": "blog/2026/05/article-main.jpg",
                    "image_preview": "blog/2026/05/article-preview.jpg",
                    "tags": ["canvas", "gift"],
                    "is_idea": false,
                    "is_stories": false,
                    "is_blogwant": false,
                    "translations": {
                        "ru": {
                            "title": "Тестовая статья из API",
                            "slug": "testovaya-statya-api-" + now,
                            "excerpt": "Короткое описание тестовой статьи.",
                            "content": "<p>HTML текст статьи на русском.</p>",
                            "meta_title": "SEO title RU",
                            "meta_desc": "SEO description RU"
                        },
                        "en": {
                            "title": "API test article",
                            "slug": "api-test-article-" + now,
                            "excerpt": "Short test article excerpt.",
                            "content": "<p>English article HTML.</p>",
                            "meta_title": "SEO title EN",
                            "meta_desc": "SEO description EN"
                        }
                    }
                }
            },
            'blog_media_upload': {
                endpoint: '/api/blog/media',
                payload: {
                    "file": "multipart/form-data field; use cURL -F file=@C:/path/to/image.jpg",
                    "alt": "Article image alt",
                    "title": "Article image title",
                    "slug": "article-image"
                }
            },
            'blog_post_update': {
                endpoint: '/api/blog/posts/' + encodeURIComponent(blogPostId),
                payload: {
                    "status": "draft",
                    "categories": [2],
                    "author_id": 1,
                    "image": "blog/2026/05/article-main-updated.jpg",
                    "image_preview": "blog/2026/05/article-preview-updated.jpg",
                    "tags": ["updated", "api"],
                    "is_idea": false,
                    "is_stories": true,
                    "is_blogwant": false,
                    "translations": {
                        "ru": {
                            "title": "Обновленное название статьи",
                            "slug": "obnovlennaya-statya-api-" + now
                        },
                        "en": {
                            "slug": "updated-api-article-" + now,
                            "content": "<p>Updated English HTML from simulator.</p>"
                        }
                    }
                }
            },
            'pipeline_changed': {
                endpoint: '/api/crm/webhooks/pipeline-changed',
                payload: {
                    "event_id": genUuidV4(),
                    "event_type": "crm.lead.stage_changed",
                    "idempotency_key": "pipeline-change-" + now,
                    "occurred_at": new Date().toISOString(),
                    "source": "CRM",
                    "data": {
                        "lead_id": leadId,
                        "pipeline": {
                            "id": "PIPE-1"
                        },
                        "stage": {
                            "from": { "id": "watching" },
                            "to": { "id": "pegging" }
                        },
                        "changed_by": {
                            "type": "manager",
                            "id": "1",
                            "name": "Admin"
                        },
                        "bot_control": {
                            "mode": "paused"
                        }
                    }
                }
            },
            'escalation': {
                endpoint: '/api/sa/escalations',
                payload: {
                    "idempotency_key": "escalation-" + now,
                    "source": "SA",
                    "escalation": {
                        "lead_id": leadId,
                        "conversation_id": conversationId,
                        "priority": "high",
                        "reason_code": "complex_case",
                        "reason_text": "Client asks for complex custom flow",
                        "confidence": 0.87,
                        "suggested_next": {
                            "summary": "Need manager response",
                            "actions": ["handoff", "quote"]
                        },
                        "dialog": {
                            "channel": "whatsapp",
                            "client": {
                                "phone": clientPhone,
                                "name": clientName
                            },
                            "messages": [
                                {
                                    "role": "client",
                                    "text": "I need a custom order with revisions",
                                    "sent_at": new Date().toISOString()
                                }
                            ]
                        },
                        "bot_control": {
                            "set_mode": "handoff_to_manager",
                            "allow_manager_takeover": true
                        }
                    }
                }
            },
            'new_lead_family_constructor': {
                endpoint: '/api/sa/leads',
                payload: {
                    "idempotency_key": "lead-family-" + now,
                    "source": "SA",
                    "lead": {
                        "channel": "whatsapp",
                        "client": {
                            "phone": "+37127" + String(now).slice(-6),
                            "name": "Family Constructor Test",
                            "email": "family.constructor." + now + "@example.com"
                        },
                        "service_request": {
                            "service_id": "FC-1",
                            "country_code": "LV",
                            "notes": "Simulator create-lead preset for family constructor",
                            "options": {
                                "size": familyConstructorSize,
                                "holst_id": 2,
                                "packaging_id": 3,
                                "production_mode": "express"
                            }
                        },
                        "delivery": {
                            "method": "to_the_door",
                            "payment": "transfer",
                            "country": "LV",
                            "city": "Riga",
                            "address": "Family Test Address",
                            "postal_index": "LV-1010"
                        },
                        "fields": {
                            "email": "family.constructor." + now + "@example.com",
                            "city": "Riga",
                            "country_code": "LV",
                            "postal_index": "LV-1010",
                            "address": "Family Test Address"
                        }
                    }
                }
            },
            'new_lead_hm44_exact': {
                endpoint: '/api/sa/leads',
                payload: {
                    "idempotency_key": "lead-hm44-exact-" + now,
                    "source": "SA",
                    "lead": {
                        "channel": "whatsapp",
                        "external_ids": {
                            "conversation_id": "CONV-HM44-EXACT-" + now
                        },
                        "client": {
                            "phone": "+37126" + String(now).slice(-6),
                            "name": "HM44 Exact Test",
                            "email": "hm44.exact." + now + "@example.com"
                        },
                        "service_request": {
                            "service_id": "HM-44",
                            "country_code": "LV",
                            "notes": "Simulator HM-44 exact gallery item preset",
                            "options": {
                                "gallery_item_id": galleryItemId,
                                "size": galleryItemSize,
                                "holst_id": 2,
                                "decor_id": 5,
                                "packaging_id": 3,
                                "execution_id": 1,
                                "production_mode": "express"
                            }
                        },
                        "pricing": {
                            "coupon_code": "",
                            "use_bonus": false
                        },
                        "delivery": {
                            "method": "to_the_door",
                            "payment": "transfer",
                            "city": "Riga",
                            "address": "Simulator HM44 Exact Street 1",
                            "postal_index": "LV-1010",
                            "country": "LV"
                        },
                        "fields": {
                            "email": "hm44.exact." + now + "@example.com",
                            "city": "Riga",
                            "country_code": "LV",
                            "postal_index": "LV-1010",
                            "address": "Simulator HM44 Exact Street 1"
                        }
                    }
                }
            },
            'service_sizes': {
                endpoint: '/api/sa/services/' + (serviceId || 'HM-UNKNOWN') + '/sizes?country_code=LV' + (((serviceId || '') === 'HM-44' && galleryItemId) ? ('&gallery_item_id=' + encodeURIComponent(galleryItemId)) : ''),
                payload: {}
            },
            'service_sizes_hm44_exact': {
                endpoint: '/api/sa/services/HM-44/sizes?country_code=LV' + (galleryItemId ? ('&gallery_item_id=' + encodeURIComponent(galleryItemId)) : ''),
                payload: {}
            },
            'service_price_by_size': {
                endpoint: '/api/sa/services/' + (serviceId || 'HM-UNKNOWN') + '/price-by-size?size=' + encodeURIComponent((((serviceId || '') === 'HM-44') ? galleryItemSize : sampleSize) || '') + '&country_code=LV' + (((serviceId || '') === 'HM-44' && galleryItemId) ? ('&gallery_item_id=' + encodeURIComponent(galleryItemId)) : ''),
                payload: {}
            },
            'service_price_by_size_hm44_exact': {
                endpoint: '/api/sa/services/HM-44/price-by-size?size=' + encodeURIComponent(galleryItemSize || '') + '&country_code=LV' + (galleryItemId ? ('&gallery_item_id=' + encodeURIComponent(galleryItemId)) : ''),
                payload: {}
            },
            'new_lead_hm43_exact': {
                endpoint: '/api/sa/leads',
                payload: {
                    "idempotency_key": "lead-hm43-exact-" + now,
                    "source": "SA",
                    "lead": {
                        "channel": "whatsapp",
                        "external_ids": {
                            "conversation_id": "CONV-HM43-EXACT-" + now
                        },
                        "client": {
                            "phone": "+37129" + String(now).slice(-6),
                            "name": "HM43 Exact Test",
                            "email": "hm43.exact." + now + "@example.com"
                        },
                        "service_request": {
                            "service_id": "HM-43",
                            "country_code": "LV",
                            "notes": "Simulator HM-43 exact layout preset",
                            "options": {
                                "size": "120x80",
                                "holst_id": 5,
                                "packaging_id": 3,
                                "execution_id": 1,
                                "form_id": 1,
                                "production_mode": "standard",
                                "wall_size_mod": "120x80",
                                "layout_svg": "<svg viewBox=\"0 0 120 80\" xmlns=\"http://www.w3.org/2000/svg\"><rect x=\"0\" y=\"0\" width=\"60\" height=\"80\"/><rect x=\"60\" y=\"0\" width=\"60\" height=\"80\"/></svg>"
                            }
                        },
                        "pricing": {
                            "coupon_code": "",
                            "use_bonus": false
                        },
                        "delivery": {
                            "method": "to_the_door",
                            "payment": "transfer",
                            "city": "Riga",
                            "address": "Simulator Exact Layout Street 1",
                            "postal_index": "LV-1010",
                            "country": "LV"
                        },
                        "fields": {
                            "email": "hm43.exact." + now + "@example.com",
                            "city": "Riga",
                            "country_code": "LV",
                            "postal_index": "LV-1010",
                            "address": "Simulator Exact Layout Street 1"
                        }
                    }
                }
            }
        };
    }

    function normalizeValidationDetails(errorPayload) {
        var out = [];
        if (!errorPayload || typeof errorPayload !== 'object') {
            return out;
        }

        var details = errorPayload.details || errorPayload.fields || errorPayload.errors || null;
        if (!details) {
            if (errorPayload.message) {
                out.push(String(errorPayload.message));
            }
            return out;
        }

        if (Array.isArray(details)) {
            details.forEach(function(item) {
                if (typeof item === 'string') {
                    out.push(item);
                    return;
                }
                if (item && typeof item === 'object') {
                    var field = item.field || item.path || item.key || 'field';
                    var message = item.message || item.error || JSON.stringify(item);
                    out.push(field + ': ' + message);
                }
            });
            return out;
        }

        if (typeof details === 'object') {
            Object.keys(details).forEach(function(field) {
                var val = details[field];
                if (Array.isArray(val)) {
                    val.forEach(function(msg) {
                        out.push(field + ': ' + String(msg));
                    });
                } else if (val && typeof val === 'object') {
                    out.push(field + ': ' + JSON.stringify(val));
                } else {
                    out.push(field + ': ' + String(val));
                }
            });
            return out;
        }

        out.push(String(details));
        return out;
    }

    function renderValidationDetails(lines) {
        var block = document.getElementById('validationDetailsBlock');
        var list = document.getElementById('validationDetailsList');
        if (!block || !list) {
            return;
        }
        list.innerHTML = '';
        if (!Array.isArray(lines) || lines.length === 0) {
            block.style.display = 'none';
            return;
        }
        lines.forEach(function(line) {
            var li = document.createElement('li');
            li.innerText = line;
            list.appendChild(li);
        });
        block.style.display = 'block';
    }

    function resetApiResponse() {
        var responseBlock = document.getElementById('responseBlock');
        var responseOutput = document.getElementById('responseOutput');

        if (responseBlock) {
            responseBlock.style.display = 'none';
        }
        if (responseOutput) {
            responseOutput.innerText = '';
            responseOutput.style.color = '#0f0';
        }
        renderValidationDetails([]);
    }

    function loadPreset(key) {
        var p = buildPresets()[key];
        if (p) {
            resetApiResponse();
            currentPresetKey = key;
            document.getElementById('endpoint').value = p.endpoint;
            document.getElementById('payload').value = JSON.stringify(p.payload, null, 4);
            updateMethodBadge(p.endpoint);
            renderPresetDoc(key);
        }
    }

    loadPreset('new_lead');
    updateMethodBadge(document.getElementById('endpoint').value);
    renderPresetDoc(currentPresetKey);

    document.getElementById('endpoint').addEventListener('input', function(e) {
        updateMethodBadge(e.target.value || '');
        renderPresetDoc(currentPresetKey);
    });

    document.getElementById('payload').addEventListener('input', function() {
        renderPresetDoc(currentPresetKey);
    });

    document.getElementById('copyCurlBtn').addEventListener('click', function() {
        var curlText = (document.getElementById('docCurl').innerText || '').trim();
        if (!curlText) {
            toastr.error('Сначала выберите команду.');
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(curlText)
                .then(function() { toastr.success('cURL скопирован'); })
                .catch(function() { toastr.error('Не удалось скопировать cURL'); });
            return;
        }

        var ta = document.createElement('textarea');
        ta.value = curlText;
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            toastr.success('cURL скопирован');
        } catch (e) {
            toastr.error('Не удалось скопировать cURL');
        }
        document.body.removeChild(ta);
    });

    document.getElementById('simulatorForm').addEventListener('submit', function(e) {
        e.preventDefault();

        var btn = e.target.querySelector('button');
        var originalText = btn.innerHTML;
        btn.innerHTML = 'Отправка...';
        btn.disabled = true;

        var endpoint = document.getElementById('endpoint').value;
        var payload = document.getElementById('payload').value;
        var token = document.querySelector('input[name="_token"]').value;
        var responseOutput = document.getElementById('responseOutput');
        responseOutput.style.color = '#0f0';

        var defaults = window.saSimulatorDefaults || {};
        var payloadObj = null;
        try {
            payloadObj = JSON.parse(payload);
        } catch (jsonErr) {
            toastr.error('Некорректный JSON в payload: ' + jsonErr.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
            return;
        }

        function getByPath(obj, path) {
            if (!obj || typeof obj !== 'object') {
                return null;
            }
            var parts = path.split('.');
            var cur = obj;
            for (var i = 0; i < parts.length; i++) {
                var key = parts[i];
                if (cur && typeof cur === 'object' && Object.prototype.hasOwnProperty.call(cur, key)) {
                    cur = cur[key];
                } else {
                    return null;
                }
            }
            return cur;
        }

        var payloadLeadId = getByPath(payloadObj, 'data.lead_id')
            || getByPath(payloadObj, 'escalation.lead_id')
            || getByPath(payloadObj, 'lead_id');
        var payloadConversationId = getByPath(payloadObj, 'data.conversation_id')
            || getByPath(payloadObj, 'escalation.conversation_id')
            || getByPath(payloadObj, 'conversation_id');
        var payloadClientPhone = getByPath(payloadObj, 'lead.client.phone')
            || getByPath(payloadObj, 'data.client.phone')
            || getByPath(payloadObj, 'data.message.from.phone')
            || getByPath(payloadObj, 'escalation.dialog.client.phone');
        var payloadClientName = getByPath(payloadObj, 'lead.client.name')
            || getByPath(payloadObj, 'data.client.name')
            || getByPath(payloadObj, 'data.message.from.name')
            || getByPath(payloadObj, 'escalation.dialog.client.name');
        var payloadMessageId = getByPath(payloadObj, 'data.message_id')
            || getByPath(payloadObj, 'data.message.message_id');

        var effectiveLeadId = payloadLeadId || defaults.lead_id;
        var effectiveConversationId = payloadConversationId || defaults.conversation_id;
        var effectiveClientPhone = payloadClientPhone || defaults.client_phone;
        var effectiveClientName = payloadClientName || defaults.client_name;
        var effectiveMessageId = payloadMessageId || defaults.message_id;

        var requiresLeadAndConversation = (
            endpoint.indexOf('/api/crm/webhooks/pipeline-changed') === 0 ||
            endpoint.indexOf('/api/sa/escalations') === 0
        );
        var requiresLeadOrConversation = (
            endpoint.indexOf('/api/crm/webhooks/bot-control') === 0
        );
        var requiresConversationOnly = (
            endpoint.indexOf('/api/sa/webhooks/messages') === 0 ||
            endpoint.indexOf('/api/crm/webhooks/send-message') === 0
        );
        var requiresClientDataForCreateLead = endpoint === '/api/sa/leads';
        var requiresMessageId = endpoint.indexOf('/api/sa/webhooks/messages') === 0 && payload.indexOf('"event_type": "message.status"') !== -1;
        var requiresSampleSize = endpoint.indexOf('/api/sa/services/') === 0 && endpoint.indexOf('/price-by-size') !== -1;
        var isMultipartMediaUpload = endpoint === '/api/blog/media';

        if (isMultipartMediaUpload) {
            toastr.warning('Этот endpoint принимает multipart/form-data. Используйте cURL из блока справки ниже.');
            document.getElementById('responseBlock').style.display = 'block';
            responseOutput.innerText = 'POST /api/blog/media принимает multipart/form-data, а эта кнопка отправляет JSON. Скопируйте cURL из справки и замените путь к файлу.';
            responseOutput.style.color = '#d58512';
            btn.innerHTML = originalText;
            btn.disabled = false;
            return;
        }

        if (requiresLeadAndConversation) {
            if (!effectiveLeadId || !effectiveConversationId) {
                toastr.error('Недостаточно данных. Нужны: lead_id и conversation_id (из payload или defaults БД).');
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }
        }

        if (requiresLeadOrConversation) {
            if (!effectiveLeadId && !effectiveConversationId) {
                toastr.error('Недостаточно данных. Нужен lead_id или conversation_id (из payload или defaults БД).');
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }
        }

        if (requiresConversationOnly) {
            if (!effectiveConversationId) {
                toastr.error('Недостаточно данных. Нужен: conversation_id (из payload или defaults БД). lead_id для этого сценария может отсутствовать.');
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }
        }

        if (requiresClientDataForCreateLead) {
            if (!effectiveClientPhone || !effectiveClientName) {
                toastr.error('Недостаточно данных. Нужны: client_phone и client_name (из payload или defaults БД).');
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }
        }

        if (requiresMessageId && !effectiveMessageId) {
            toastr.error('Недостаточно данных. Нужен существующий message_id для message.status (из payload или defaults БД).');
            btn.innerHTML = originalText;
            btn.disabled = false;
            return;
        }

        if (requiresSampleSize && !/[?&]size=/.test(endpoint) && !defaults.sample_size) {
            toastr.error('Недостаточно данных. Нужен параметр size в endpoint (или sample_size в defaults БД).');
            btn.innerHTML = originalText;
            btn.disabled = false;
            return;
        }

        fetch('{{ route("admin.sa.simulator.trigger") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                endpoint: endpoint,
                payload: payload
            })
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('responseBlock').style.display = 'block';
            renderValidationDetails([]);
            var renderedData = data;

            if (data.status === 'ok' && data.http_code >= 200 && data.http_code < 300) {
                 var apiResp = (data.api_response && typeof data.api_response === 'object') ? data.api_response : null;
                 if (apiResp && apiResp.status === 'error' && apiResp.error && apiResp.error.code === 'VALIDATION_ERROR') {
                     var details = normalizeValidationDetails(apiResp.error);
                     renderedData.validation_error_details = details;
                     responseOutput.innerText = JSON.stringify(renderedData, null, 4);
                     renderValidationDetails(details);
                     toastr.warning('VALIDATION_ERROR: смотрите список полей ниже');
                     responseOutput.style.color = 'red';
                     return;
                 }
                 responseOutput.innerText = JSON.stringify(renderedData, null, 4);
                 toastr.success('Webhook принят');
            } else {
                 responseOutput.innerText = JSON.stringify(renderedData, null, 4);
                 toastr.error('API вернул ошибку, проверьте ответ');
                 responseOutput.style.color = 'red';

                 var wrapped = (data.api_response && typeof data.api_response === 'object') ? data.api_response : null;
                 if (wrapped && wrapped.error && wrapped.error.code === 'VALIDATION_ERROR') {
                     var wrappedDetails = normalizeValidationDetails(wrapped.error);
                     renderedData.validation_error_details = wrappedDetails;
                     responseOutput.innerText = JSON.stringify(renderedData, null, 4);
                     renderValidationDetails(wrappedDetails);
                 } else if (data.error && data.error.code === 'VALIDATION_ERROR') {
                     var topDetails = normalizeValidationDetails(data.error);
                     renderedData.validation_error_details = topDetails;
                     responseOutput.innerText = JSON.stringify(renderedData, null, 4);
                     renderValidationDetails(topDetails);
                 }
            }
        })
        .catch(err => {
            document.getElementById('responseBlock').style.display = 'block';
            responseOutput.innerText = err.toString();
            responseOutput.style.color = 'red';
            renderValidationDetails([]);
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;

            try {
                var pJson = JSON.parse(payload);
                if (pJson.idempotency_key) {
                    pJson.idempotency_key = 'dedup-' + Date.now() + Math.floor(Math.random() * 100);
                }
                if (pJson.event_id && typeof pJson.event_id === 'string') {
                    pJson.event_id = genUuidV4();
                }
                document.getElementById('payload').value = JSON.stringify(pJson, null, 4);
            } catch (e) {
                // keep as-is
            }
        });
    });
</script>

@endsection
