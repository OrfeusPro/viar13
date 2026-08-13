@php
    $orderUser = $order['user'] ?? null;
    $orderDelivery = (isset($order['delivery']) && is_array($order['delivery'])) ? $order['delivery'] : [];
    $deliveryMethod = (string) ($orderDelivery['sposob'] ?? '');
    $pickupMethods = ['pickup_at_viar_workshop', 'pickup_Riga', 'pickup_Daugavplis', 'pickup_Daugavpils'];
    $defaultDestination = in_array($deliveryMethod, $pickupMethods, true) ? 'pickup' : 'address';
    $selectedPickupWorkshopId = (int) ($orderDelivery['pickup_workshop_id'] ?? 0);
    $pickupWorkshopTitle = trim((string) ($orderDelivery['address'] ?? $orderDelivery['city'] ?? ''));

    if ($selectedPickupWorkshopId > 0 && isset($deliveryPickupAtViarWorkshop)) {
        $pickupWorkshop = collect($deliveryPickupAtViarWorkshop)->firstWhere('id', $selectedPickupWorkshopId);
        if ($pickupWorkshop) {
            $pickupWorkshopTitle = trim((string) $pickupWorkshop->title);
        }
    }

    $receiverName = $orderUser
        ? trim(((string) $orderUser->last_name) . ' ' . ((string) $orderUser->first_name))
        : trim(((string) ($orderDelivery['last_name'] ?? '')) . ' ' . ((string) ($orderDelivery['first_name'] ?? '')));
    $receiverPhone = $orderUser
        ? (string) $orderUser->phone
        : (string) ($orderDelivery['payer_phone'] ?? ($orderDelivery['phone'] ?? ''));
    $receiverEmail = $orderUser
        ? (string) $orderUser->email
        : (string) ($orderDelivery['email'] ?? '');
@endphp

<div class="form__label__cover">
    <div class="close__btn js__close">×</div>
    <form
        method="POST"
        action="{{ route('create_label') }}"
        data-default-destination="{{ $defaultDestination }}"
        data-current-pickup-workshop-id="{{ $selectedPickupWorkshopId }}"
        data-current-pickup-title="{{ $pickupWorkshopTitle }}"
        data-current-pickup-address="{{ trim((string) ($orderDelivery['address'] ?? '')) }}"
    >
        {{ csrf_field() }}
        <div class="pak_dalys fonas">
            <h3>Посылка для заказа: №{{ $order['id'] }}</h3>
            <table width="100%" cellpadding="0" cellspacing="5" border="0">
                <tbody>
                    <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                    <tr class="tr__r">
                        <td style="font-weight: bold;">№ док. на посылку:</td>
                        <td>
                            <input type="text" name="doc_no" id="code" size="20" maxlength="16" value="" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="klientai"></div>

        <div class="row">
            <div class="col-xs-6">
                <div class="pak_dalys_50 fonas" id="receiverForm" style="margin: 0 0 10px 5px; min-height: 319px;">
                    <table style="padding: 2px; width: 100%;">
                        <tbody>
                            <tr class="tr__r">
                                <td colspan="2" align="center">
                                    <span style="
                                            font-size: 10pt;
                                            font-weight: bold;
                                        ">Получатель</span>
                                    <br />
                                </td>
                                <td>
                                    <select class="selectDestination" name="destination">
                                        <option value="address" {{ $defaultDestination === 'address' ? 'selected' : '' }}>На адрес</option>
                                        <option value="pickup" {{ $defaultDestination === 'pickup' ? 'selected' : '' }}>На отделение</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td colspan="2" height="5"></td>
                            </tr>
                            <tr class="tr__r">
                                <td style="width: 160px;"><b>Название</b></td>
                                <td>
                                    <input type="text" name="g_name" id="g_name" size="30" maxlength="60"
                                        value="{{ $receiverName }}"
                                        class="ui-autocomplete-input" />
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td><b>Код предпр.</b></td>
                                <td>
                                    <input type="text" name="g_code" id="g_code" size="30" maxlength="11" value="" />
                                </td>
                            </tr>
                            @if (isset($order['delivery']) && is_array($order['delivery']) && !empty($order['delivery']))
                                <tr class="tr__r">
                                    <td><b>Страна - {{ $order['delivery']['country'] ?? 'Не выбрана' }}</b></td>
                                    <td>
                                        <select name="r_country" id="r_country">
                                            @php
                                                $ee = '';
                                                $lv = '';
                                                $de = '';
                                                $lt = '';
                                                $fin = '';
                                                $pl = '';
                                                $se = '';
                                            @endphp
                                            @if (($order['delivery']['country'] ?? null) == 'EE')
                                                @php
                                                    $ee = 'selected="selected"';
                                                @endphp
                                            @endif

                                            @if (($order['delivery']['country'] ?? null) == 'LV')
                                                @php
                                                    $lv = 'selected="selected"';
                                                @endphp
                                            @endif

                                            @if (($order['delivery']['country'] ?? null) == 'DE')
                                                @php
                                                    $de = 'selected="selected"';
                                                @endphp
                                            @endif

                                            @if (($order['delivery']['country'] ?? null) == 'FIN')
                                                @php
                                                    $fin = 'selected="selected"';
                                                @endphp
                                            @endif

                                            @if (($order['delivery']['country'] ?? null) == 'LT')
                                                @php
                                                    $lt = 'selected="selected"';
                                                @endphp
                                            @endif
                                            <option value=""></option>
                                            <option value="EE" {{ $ee }}>Estonia</option>
                                            <option value="LT" {{ $lt }}>Lithuania</option>
                                            <option value="LV" {{ $lv }}>Latvia</option>
                                            <option value="FI" {{ $fin }}>Finland</option>
                                            <option value="DK" {{ $de }}>Denmark</option>
                                            <option value="PL">Poland</option>
                                            <option value="SE">Sweden</option>
                                            {{-- <option value="FI" {{ $fin }}>Finland</option>
                                            <option value="DK">Denmark</option>
                                            <option value="SE">Sweden</option> --}}
                                        </select>
                                    </td>
                                </tr>
                            @endif
                            {{-- @php
                                $user = auth()->user();
                                $c_tels = \App\Models\CountryTel::orderBy('sort', 'asc')->get()->translate(strtolower(App::getLocale()), 'ru');
                                $sale_towns= \App\Models\ADeliveryTown::with(['translations' => function ($query) {
                                    $query->where('locale', app()->getLocale());
                                }])->get();
                                $warehouses = $citys = [];
                            @endphp --}}
                            <tr class="tr__r pickup_data_loader" style="display: none;">
                                <td colspan="3">
                                    <span class="warehousesSelectLoader"></span>
                                </td>
                            </tr>
                            <tr class="tr__r pickup_data" style="display: none;">
                                <td colspan="2">
                                    <b>Выбор города для пик-ап пункта</b>
                                </td>
                                <td>
                                    <select class="citysSelect" style="width: 135px;">
                                        <option value="">Все города</option>
                                        {{-- @foreach($citys as $city)
                                        <option value="{{ $city['city'] }}">{{ $city['city'] }}</option>
                                        @endforeach --}}
                                    </select>
                                </td>
                            </tr>
                            <tr class="tr__r pickup_data" style="display: none;">
                                <td colspan="2">
                                    <b>Выбор пик-ап пункта</b>
                                </td>
                                <td>
                                    <select class="warehousesSelect" name="g_address_pickup" style="width: 235px;">
                                        <option value="">Выберите пункт</option>
                                        {{-- @foreach($warehouses as $warehouse)
                                        <option data-city="{{ $warehouse['city'] }}" value="{{ $warehouse['id'] }}">{{ $warehouse['display_name'] }}</option>
                                        @endforeach --}}
                                    </select>

                                    <input type="hidden" name="g_city_pickup" value="">
                                    <input type="hidden" name="g_post_pickup" value="">
                                    <input type="hidden" name="g_name_pickup" value="">
                                    <input type="hidden" name="g_code_pickup" value="">
                                    <input type="hidden" name="pickup_workshop_id" value="{{ $selectedPickupWorkshopId }}">
                                </td>
                            </tr>

                            <tr class="tr__r address_data">
                                <td><b>Город</b></td>
                                <td>
                                    <input type="text" name="g_city" id="g_city" size="30" maxlength="40" required @if (isset($order['delivery']['city'])) value="{{ $order['delivery']['city'] }}" @endif />
                                </td>
                            </tr>
                            <tr class="tr__r address_data">
                                <td>
                                    <b>Улица, Дом-Квартира</b>
                                </td>
                                <td>
                                    <input type="text" name="g_address" id="g_address" size="19" maxlength="50"
                                        value="{{ $order['delivery']['address'] }}" class="ui-autocomplete-input" />,
                                    <input type="text" name="g_house" id="g_house" size="1" maxlength="15" value=""
                                        class="ui-autocomplete-input" />-
                                    <input type="text" name="g_flat" id="g_flat" size="1" maxlength="15" value="" />
                                </td>
                            </tr>
                            <tr class="tr__r address_data">
                                <td style="width: 160px;">
                                    <b>Почтовый код</b>
                                    <a id="g_post_search_link"
                                        href="http://www.pasts.lv/lv/kategorija/pasta_indeksa_meklesana/"
                                        target="_blank">[Искать]</a><br />
                                    <span style="color: red; font-size: 7pt;">При указании неверного почтового кода
                                        посылка не будет доставлена
                                        Получателю</span>
                                </td>
                                <td>
                                    <input type="text" name="g_post" id="g_post" size="30" required maxlength="6" @if (isset($order['delivery']['postal_index'])) value="{{ $order['delivery']['postal_index'] }}" @endif />
                                </td>
                            </tr>
                            <tr class="tr__r">
                                @php
                                    $count = 0;
                                    $order_sizes = '(';
                                    foreach ($order['items'] as  $key => $product) {
                                        if (isset($product['sizeId'])) {
                                            $order_sizes .= $product['sizeId'] . ', ';
                                            $count++;
                                        } else if (isset($product['size_name'])) {
                                            $order_sizes .= $product['size_name'] . ', ';
                                            $count++;
                                        }
                                    }

                                    if ($count > 0) {
                                        $order_sizes = rtrim($order_sizes, ', ');
                                    }

                                    $order_sizes .= ')';
                                @endphp
                                <td><b>Контактное лицо</b></td>
                                <td>
                                    <input type="text" name="g_contact_p" id="g_contact_p" size="30" maxlength="40"
                                        value="{{ $order['id'] . ' ' . $order_sizes }}" />
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td><b>Телефон</b></td>
                                <td>
                                    <input class="rmv-dft-val" type="text" name="g_contact_t" id="g_contact_t" size="30"
                                        maxlength="30" value="{{ $receiverPhone }}" />
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td><b>Эл. почта</b></td>
                                <td>
                                    <input type="text" name="email_receiver" id="email_receiver" size="30"
                                        maxlength="80" value="{{ $receiverEmail }}" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-xs-6">
                <div class="pak_dalys_50 fonas" style="margin: 0 5px 10px 0; min-height: 319px;">
                    <table style="padding: 2px; width: 100%;">
                        <tbody>
                            <tr class="tr__r">
                                <td colspan="2" align="center">
                                    <span style="
                                            font-size: 10pt;
                                            font-weight: bold;
                                        ">Отправитель</span>
                                    <!-- <a class="klsearch" href="kl_search.php?t=s"
                                        >[Искать]</a
                                    > -->
                                    <br />
                                    <!-- Save sender`s data for further shipments:
                                    <input
                                        type="checkbox"
                                        name="save_sender"
                                        value="1"
                                        id="save_sender_checkbox"
                                    /> -->
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td colspan="2" height="5"></td>
                            </tr>
                            <tr class="tr__r">
                                <td style="width: 160px;"><b>Название</b></td>
                                <td>
                                    <input type="text" name="s_name" id="s_name" size="30" maxlength="60"
                                        value="{{ $activeVenipakData->s_name }}" class="ui-autocomplete-input" />
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td><b>Код предпр.</b></td>
                                <td>
                                    <input type="text" name="s_code" id="s_code" size="30" maxlength="11"
                                        value="{{ $activeVenipakData->s_code }}" />
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td><b>Страна</b></td>
                                <td>
                                    <select name="s_country" id="s_country">
                                        <option value="DK" {{ ($activeVenipakData->s_country ?? 'LV') == 'DK' ? 'selected' : '' }}>Denmark</option>
                                        <option value="EE" {{ ($activeVenipakData->s_country ?? 'LV') == 'EE' ? 'selected' : '' }}>Estonia</option>
                                        <option value="FI" {{ ($activeVenipakData->s_country ?? 'LV') == 'FI' ? 'selected' : '' }}>Finland</option>
                                        <option value="LV" {{ ($activeVenipakData->s_country ?? 'LV') == 'LV' ? 'selected' : '' }}>Latvia</option>
                                        <option value="LT" {{ ($activeVenipakData->s_country ?? 'LV') == 'LT' ? 'selected' : '' }}>Lithuania</option>
                                        <option value="PL" {{ ($activeVenipakData->s_country ?? 'LV') == 'PL' ? 'selected' : '' }}>Poland</option>
                                        <option value="SE" {{ ($activeVenipakData->s_country ?? 'LV') == 'SE' ? 'selected' : '' }}>Sweden</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td><b>Город</b></td>
                                <td>
                                    <input type="text" name="s_city" id="s_city" size="30" maxlength="40"
                                        value="{{ $activeVenipakData->s_city }}" />
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td><b>Адрес</b></td>
                                <td>
                                    <input type="text" name="s_address" id="s_address" size="30" maxlength="50"
                                        value="{{ $activeVenipakData->s_address }}" />
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td style="width: 160px;">
                                    <b>Почтовый код</b>
                                    <a id="s_post_search_link"
                                        href="http://www.pasts.lv/lv/kategorija/pasta_indeksa_meklesana/"
                                        target="_blank">[Искать]</a><br />
                                    <span style="color: red; font-size: 7pt;">При указании неверного почтового кода
                                        посылка не будет взята у
                                        Отправителя</span>
                                </td>
                                <td>
                                    <input type="text" name="s_post" id="s_post" size="30" maxlength="6" value="{{ $activeVenipakData->s_post }}" />
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td><b>Контактное лицо</b></td>
                                <td>
                                    <input type="text" name="s_contact_p" id="s_contact_p" size="30" maxlength="40"
                                        value="{{ $activeVenipakData->s_contact_p }}" />
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td><b>Телефон</b></td>
                                <td>
                                    <input type="text" name="s_contact_t" id="s_contact_t" size="30" maxlength="30"
                                        value="{{ $activeVenipakData->s_contact_t }}" />
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td><b>Эл. почта</b></td>
                                <td>
                                    <input type="text" name="email_sender" id="email_sender" size="30" maxlength="80"
                                        value="{{ $activeVenipakData->email_sender }}" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-6">
                <div class="pak_dalys fonas" id="commentsWrapper">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tbody>
                            <tr class="tr__r">
                                <td id="commentsTitle" style="
                                        width: 100px;
                                        font-weight: bold;
                                        vertical-align: top;
                                    ">
                                    Коментарии:
                                </td>
                                <td id="commentsBody">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tbody>
                                            <tr class="tr__r" style="display: block;">
                                                <td style="
                                                        width: 201px;
                                                        vertical-align: top;
                                                    ">
                                                    <span class="d__code">Дверной код:</span>
                                                </td>
                                                <td style="
                                                        width: 100px;
                                                        vertical-align: top;
                                                    ">
                                                    <input type="text" name="door_code" id="door_code" size="10"
                                                        maxlength="10" value="" style="margin: 1px 0;" />
                                                </td>
                                                <td style="
                                                        width: 100px;
                                                        vertical-align: top;
                                                        text-align: right;
                                                        padding-right: 6px;
                                                        padding-left: 8px;
                                                    " rowspan="3"></td>
                                                <td style="
                                                        vertical-align: top;
                                                        padding-right: 3px;
                                                    " rowspan="3"></td>
                                            </tr>
                                            <tr class="tr__r" style="display: block;">
                                                <td style="
                                                        width: 201px;
                                                        vertical-align: top;
                                                    ">
                                                    Номер кабинета:
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <input style="margin: 1px 0;" type="text" name="office_no"
                                                        id="office_no" size="10" maxlength="10" value="" />
                                                </td>
                                            </tr>
                                            <tr class="tr__r" style="display: block;">
                                                <td style="
                                                        width: 201px;
                                                        vertical-align: top;
                                                    ">
                                                    Номер склада :
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <input style="margin: 1px 0;" type="text" name="warehous_no"
                                                        id="warehous_no" size="10" maxlength="10" value="" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-xs-6">
                <div class="pak_dalys fonas" id="deliveryTypesWrapper">
                    <div class="" id="deliveryTimesWrapper">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" id="tb_delivery_type">
                            <tbody>
                                <tr class="tr__r">
                                    <td style="
                                            width: 135px;
                                            vertical-align: text-top;
                                            font-weight: bold;
                                            margin-right: 15px;
                                        ">
                                        Время доставки :
                                    </td>
                                    <td style="
                                            width: 500px;
                                            vertical-align: text-top;
                                        " class="d-mr-inputs">
                                        <label><input type="radio" name="delivery_type" value="nwd" id="dtype_nwd"
                                                checked="" />Следующий рабочий день </label><br />
                                        <label>
                                            <input type="radio" name="delivery_type" value="nwd10"
                                                id="dtype_nwd10" />Следующий рабочий день до 10:00 ч.
                                            <span class="infobox" id="nwd10_info" style="
                                                    width: 300px;
                                                    height: 70px;
                                                    display: inline;
                                                ">
                                                <img class="infobox_sauktukas" id="infobox_sauktukas_nwd10_info" alt=""
                                                    src="https://venipak.uat.megodata.com/siunta2/images/information-icon.png"
                                                    style="
                                                        border: 0px;
                                                        display: ;
                                                    " />
                                                <div id="infobox_nwd10_info" style="
                                                        position: absolute;
                                                        width: 300px;
                                                        height: 70px;
                                                        display: none;
                                                        border: 2px solid
                                                            #c6e39f;
                                                        padding: 3px;
                                                        text-align: justify;
                                                        z-index: 100;
                                                    " class="fonas">
                                                    Дополнительный платеж и
                                                    только в Вильнюсе, Каунасе,
                                                    Клайпеде, Шяуляй,
                                                    Паневежисе, Алитусе, Rīge,
                                                    Tallinn
                                                </div>
                                            </span>
                                        </label>
                                        <br />
                                        <label>
                                            <input type="radio" name="delivery_type" value="nwd12"
                                                id="dtype_nwd12" />Следующий рабочий день до 12:00 ч.
                                            <span class="infobox" id="nwd12_info" style="
                                                    width: 300px;
                                                    height: 70px;
                                                    display: inline;
                                                ">
                                                <img class="infobox_sauktukas" id="infobox_sauktukas_nwd12_info" alt=""
                                                    src="https://venipak.uat.megodata.com/siunta2/images/information-icon.png"
                                                    style="
                                                        border: 0px;
                                                        display: ;
                                                    " />
                                                <div id="infobox_nwd12_info" style="
                                                        position: absolute;
                                                        width: 300px;
                                                        height: 70px;
                                                        display: none;
                                                        border: 2px solid
                                                            #c6e39f;
                                                        padding: 3px;
                                                        text-align: justify;
                                                        z-index: 100;
                                                    " class="fonas">
                                                    Дополнительный платеж и
                                                    только в Вильнюсе, Каунасе,
                                                    Клайпеде, Шяуляй,
                                                    Паневежисе, Алитусе, Rīge,
                                                    Tallinn
                                                </div>
                                            </span>
                                        </label>
                                        <br />
                                        <label>
                                            <input type="radio" name="delivery_type" value="nwd8_14"
                                                id="dtype_nwd8_14" />Следующий рабочий день 8:00-14:00
                                            ч.
                                            <span class="infobox" id="nwd14_info" style="
                                                    width: 300px;
                                                    height: 70px;
                                                    display: inline;
                                                ">
                                                <img class="infobox_sauktukas" id="infobox_sauktukas_nwd14_info" alt=""
                                                    src="https://venipak.uat.megodata.com/siunta2/images/information-icon.png"
                                                    style="
                                                        border: 0px;
                                                        display: ;
                                                    " />
                                                <div id="infobox_nwd14_info" style="
                                                        position: absolute;
                                                        width: 300px;
                                                        height: 70px;
                                                        display: none;
                                                        border: 2px solid
                                                            #c6e39f;
                                                        padding: 3px;
                                                        text-align: justify;
                                                        z-index: 100;
                                                    " class="fonas">
                                                    Дополнительный платеж и
                                                    только в Вильнюсе, Каунасе,
                                                    Клайпеде, Шяуляй,
                                                    Паневежисе, Алитусе, Rīge,
                                                    Daugavpile, Valmierai,
                                                    Liepoja, Tallinn
                                                </div>
                                            </span>
                                        </label>
                                        <br />
                                        <label>
                                            <input type="radio" name="delivery_type" value="nwd14_17"
                                                id="dtype_nwd14_17" />Следующий рабочий день 14:00-17:00
                                            ч.
                                            <span class="infobox" id="nwd17_info" style="
                                                    width: 300px;
                                                    height: 70px;
                                                    display: inline;
                                                ">
                                                <img class="infobox_sauktukas" id="infobox_sauktukas_nwd17_info" alt=""
                                                    src="https://venipak.uat.megodata.com/siunta2/images/information-icon.png"
                                                    style="
                                                        border: 0px;
                                                        display: ;
                                                    " />
                                                <div id="infobox_nwd17_info" style="
                                                        position: absolute;
                                                        width: 300px;
                                                        height: 70px;
                                                        display: none;
                                                        border: 2px solid
                                                            #c6e39f;
                                                        padding: 3px;
                                                        text-align: justify;
                                                        z-index: 100;
                                                    " class="fonas">
                                                    Дополнительный платеж и
                                                    только в Вильнюсе, Каунасе,
                                                    Клайпеде, Шяуляй,
                                                    Паневежисе, Алитусе, Rīge,
                                                    Daugavpile, Valmierai,
                                                    Liepoja, Tallinn
                                                </div>
                                            </span>
                                        </label>
                                        <br />
                                        <label>
                                            <input type="radio" name="delivery_type" value="nwd18_22"
                                                id="dtype_nwd18_22" />Следующий рабочий день 18:00-22:00
                                            ч.
                                            <span class="infobox" id="nwd22_info" style="
                                                    width: 300px;
                                                    height: 70px;
                                                    display: inline;
                                                ">
                                                <img class="infobox_sauktukas" id="infobox_sauktukas_nwd22_info" alt=""
                                                    src="https://venipak.uat.megodata.com/siunta2/images/information-icon.png"
                                                    style="
                                                        border: 0px;
                                                        display: ;
                                                    " />
                                                <div id="infobox_nwd22_info" style="
                                                        position: absolute;
                                                        width: 300px;
                                                        height: 70px;
                                                        display: none;
                                                        border: 2px solid
                                                            #c6e39f;
                                                        padding: 3px;
                                                        text-align: justify;
                                                        z-index: 100;
                                                    " class="fonas">
                                                    Дополнительный платеж и
                                                    только в Вильнюсе, Каунасе,
                                                    Клайпеде, Шяуляй,
                                                    Паневежисе, Алитусе, Rīge,
                                                    Daugavpile, Valmierai,
                                                    Liepoja, Tallinn
                                                </div>
                                            </span>
                                        </label>
                                        <br />
                                        <label for="dtype_sat">
                                            <input type="radio" name="delivery_type" value="sat"
                                                id="dtype_sat" />Суббота
                                            <span class="infobox" id="sat_info" style="
                                                    width: 300px;
                                                    height: 70px;
                                                    display: inline;
                                                ">
                                                <img class="infobox_sauktukas" id="infobox_sauktukas_sat_info" alt=""
                                                    src="https://venipak.uat.megodata.com/siunta2/images/information-icon.png"
                                                    style="
                                                        border: 0px;
                                                        display: ;
                                                    " />
                                                <div id="infobox_sat_info" style="
                                                        position: absolute;
                                                        width: 300px;
                                                        height: 70px;
                                                        display: none;
                                                        border: 2px solid
                                                            #c6e39f;
                                                        padding: 3px;
                                                        text-align: justify;
                                                        z-index: 100;
                                                    " class="fonas">
                                                    Дополнительный платеж и
                                                    только в Вильнюсе, Каунасе,
                                                    Клайпеде, Шяуляй,
                                                    Паневежисе, Алитусе
                                                </div>
                                            </span>
                                        </label>
                                    </td>
                                    <td style="vertical-align: text-top;">
                                        <div id="delivery_express_wrapper" style="" class="soft-remove">
                                            <span id="express_delivery_info">
                                                Доставка из
                                                <span style="font-weight: bold;" id="placeFrom">Daugavpils nov.,
                                                    maļinovas
                                                    pag</span>
                                                в
                                                <span style="font-weight: bold;" id="placeTo"></span>
                                                за: <br />
                                                <label>
                                                    <input type="radio" name="delivery_express" id="delivery_express_48"
                                                        value="0" checked="checked" />
                                                    48 часов (2 рабочих дня)
                                                </label>
                                                <br />
                                                <label>
                                                    <input type="radio" name="delivery_express" id="delivery_express_24"
                                                        value="1" />
                                                    24 часа (на следующий
                                                    рабочий день)
                                                </label>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-6">
                <div class="pak_dalys fonas" id="instructionsWrapper">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tbody>
                            <tr class="tr__r">
                                <td id="instructionsTitle" style="
                                        width: 100px;
                                        font-weight: bold;
                                        vertical-align: top;
                                    ">
                                    Указания :
                                </td>
                                <td id="instructionsBody">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tbody>
                                            <tr class="tr__r" id="instructionsCOD">
                                                <td style="
                                                        width: 250px !important;
                                                    ">
                                                    C.O.D.:
                                                </td>
                                                <td>
                                                    <label><input type="text" name="cod" id="cod" size="10"
                                                            maxlength="10" value="" /></label>
                                                    <label>
                                                        <select name="cod_type" id="cod_type">
                                                            <option value="EUR" selected="">EUR</option>
                                                            <option value="PLN">PLN</option>
                                                            <option value="CZK">CZK</option>
                                                        </select>
                                                    </label>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td colspan="2" style="height: 10px;"></td>
                                            </tr>

                                            <tr id="instructionsLDG">
                                                <td class="tr__r" style="
                                                        width: 250px;
                                                        vertical-align: text-top;
                                                    ">
                                                    Вернуть сопроводительные
                                                    документы по адресу:<br />
                                                    <span id="ldg_txt" style="
                                                            font-style: italic;
                                                        ">адрес отправителя</span>
                                                    <!-- <a class="klsearch" href="kl_ldg.php"
                                                        >[изменить ]</a
                                                    > -->
                                                </td>
                                                <!-- <td style="vertical-align: top;">
                                                    <label
                                                        ><input
                                                            type="checkbox"
                                                            name="return_doc"
                                                            value="1"
                                                            id="return_doc" /></label
                                                    ><br />
                                                    <input
                                                        type="hidden"
                                                        name="ldg_name"
                                                        id="ldg_name"
                                                        size="10"
                                                        maxlength="60"
                                                        value=""
                                                    />
                                                    <input
                                                        type="hidden"
                                                        name="ldg_code"
                                                        id="ldg_code"
                                                        size="10"
                                                        maxlength="11"
                                                        value=""
                                                    />
                                                    <input
                                                        type="hidden"
                                                        name="ldg_country"
                                                        id="ldg_country"
                                                        size="10"
                                                        maxlength="2"
                                                        value=""
                                                    />
                                                    <input
                                                        type="hidden"
                                                        name="ldg_city"
                                                        id="ldg_city"
                                                        size="10"
                                                        maxlength="40"
                                                        value=""
                                                    />
                                                    <input
                                                        type="hidden"
                                                        name="ldg_address"
                                                        id="ldg_address"
                                                        size="10"
                                                        maxlength="50"
                                                        value=""
                                                    />
                                                    <input
                                                        type="hidden"
                                                        name="ldg_post"
                                                        id="ldg_post"
                                                        size="10"
                                                        maxlength="6"
                                                        value=""
                                                    />
                                                    <input
                                                        type="hidden"
                                                        name="ldg_contact_p"
                                                        id="ldg_contact_p"
                                                        size="10"
                                                        maxlength="40"
                                                        value=""
                                                    />
                                                    <input
                                                        type="hidden"
                                                        name="ldg_contact_t"
                                                        id="ldg_contact_t"
                                                        size="10"
                                                        maxlength="30"
                                                        value=""
                                                    />
                                                </td> -->
                                            </tr>

                                            <tr>
                                                <td colspan="2" style="height: 10px;"></td>
                                            </tr>

                                            <tr class="tr__r">
                                                <td style="width: 250px;">
                                                    Перед доставкой позвонить:
                                                </td>
                                                <td>
                                                    <input type="checkbox" name="call" value="1" id="call" />
                                                </td>
                                            </tr>
                                            <tr class="tr__r">
                                                <td style="width: 250px;">
                                                    <span id="rp4_title">4 hands service</span>
                                                    <span class="infobox" id="4rp_info" style="
                                                            width: 300px;
                                                            height: 157px;
                                                            display: inline;
                                                        ">
                                                        <div id="infobox_4rp_info" style="
                                                                position: absolute;
                                                                width: 300px;
                                                                height: 157px;
                                                                display: none;
                                                                border: 2px
                                                                    solid
                                                                    #c6e39f;
                                                                padding: 3px;
                                                                text-align: justify;
                                                                z-index: 100;
                                                            " class="fonas">
                                                            Shipment carrying up
                                                            by 2 people.
                                                            Additional fee is
                                                            applied and only in
                                                            Vilnius, Kaunas,
                                                            Klaipėda, Šiauliai,
                                                            Panevėžys, Alytus,
                                                            Rīga, Daugavpils,
                                                            Valmiera, Liepoja,
                                                            Tallinn.<br />
                                                            <br />
                                                            2 x 4 hands service
                                                            - packages weighting
                                                            more than 60kg will
                                                            be double charged.
                                                            Service available
                                                            for packages under
                                                            100kg.<br />
                                                            <br />
                                                            For packages
                                                            weighting more than
                                                            100kg service is
                                                            available only with
                                                            separate agreement
                                                        </div>
                                                    </span>
                                                    :
                                                </td>
                                                <td>
                                                    <label for="rp4"><input type="checkbox" name="rp4" value="1"
                                                            id="rp4" /></label>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-xs-6">
                <div id="insurance" class="pak_dalys fonas" title="">
                    <div style="margin: 2px 2px 8px 2px;">
                        <span style="font-size: 10pt; font-weight: bold;">Выберите страхование посылки</span>
                        <span class="infobox" id="insurance_disclaimer" style="
                                width: 300px;
                                height: 80px;
                                display: inline;
                                font-size: 8pt;
                            ">
                            <img class="infobox_sauktukas" id="infobox_sauktukas_insurance_disclaimer" alt=""
                                src="https://venipak.uat.megodata.com/siunta2/images/information-icon.png"
                                style="border: 0px; display: ;" />
                            <div id="infobox_insurance_disclaimer" style="
                                    position: absolute;
                                    width: 300px;
                                    height: 80px;
                                    display: none;
                                    border: 2px solid #c6e39f;
                                    padding: 3px;
                                    text-align: justify;
                                    z-index: 100;
                                " class="fonas">
                                Cтандартная oтветственность перевозчика в рамках
                                конвенций CMR 8.33 SDR за каждый 1 кг посылки
                                (около 10 евро за 1 кг). Cумма будет
                                компенсированa в соответствии с выбранной
                                страховой суммой, но она не может быть больше
                                стоимости посылки.
                            </div>
                        </span>
                    </div>

                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        <tbody>
                            <tr class="tr__r">
                                <td style="width: 100px; font-weight: bold;">
                                    &nbsp;
                                </td>
                                <td style="width: 250px;">
                                    Страховая стоимость посылки:
                                </td>
                                <td>
                                    <div style="
                                            float: left;
                                            height: 17px;
                                            line-height: 17px;
                                            width: 250px;
                                        ">
                                        <select name="insur" id="insur">
                                            <option value="" selected="">Выберите</option>
                                            <option value="10"
                                                title="Cтандартная oтветственность перевозчика в рамках конвенций CMR 8.33 SDR за каждый 1 кг посылки (около 10 евро за 1 кг). Cумма будет компенсированa в соответствии с выбранной страховой суммой, но она не может быть больше стоимости посылки.">
                                                Ответственность перевозчика
                                                Стандарт ~10 евро за 1 кг
                                                отгрузки
                                            </option>
                                            <option value="100">до 100 EUR за весь
                                                посылки</option>
                                            <option value="200">до 200 EUR за весь
                                                посылки</option>
                                            <option value="300">до 300 EUR за весь
                                                посылки</option>
                                            <option value="400">до 400 EUR за весь
                                                посылки</option>
                                            <option value="500">до 500 EUR за весь
                                                посылки</option>
                                            <option value="1000">до 1000 EUR за весь
                                                посылки</option>
                                            <option value="2000">до 2000 EUR за весь
                                                посылки</option>
                                            <option value="3000">до 3000 EUR за весь
                                                посылки</option>
                                            <option value="4000">до 4000 EUR за весь
                                                посылки</option>
                                            <option value="5000">до 5000 EUR за весь
                                                посылки</option>
                                            <option value="10000">до 10000 EUR за весь
                                                посылки</option>
                                        </select>
                                        <script>
                                            $('#insur option[value="10"]').attr(
                                                "title",
                                                "Cтандартная oтветственность перевозчика в рамках конвенций CMR 8.33 SDR за каждый 1 кг посылки (около 10 евро за 1 кг). Cумма будет компенсированa в соответствии с выбранной страховой суммой, но она не может быть больше стоимости посылки."
                                            );

                                        </script>
                                        <style>
                                            select#insur {
                                                width: 240px;
                                            }

                                            select#insur+label {
                                                position: relative;
                                                left: 40px;
                                            }

                                        </style>
                                    </div>
                                    <div style="
                                            float: left;
                                            height: 17px;
                                            line-height: 17px;
                                            width: 200px;
                                        "></div>
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td style="padding-top: 10px;">&nbsp;</td>
                                <td colspan="1">Цена страховки:</td>
                                <td>
                                    <b><span id="calc-res">0</span><span id="calc-res-disabled">0</span>
                                        EUR</b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="pak_dalys fonas" id="packages">
            <p>Упаковки</p>
            <div style="margin: 2px 2px; font-weight: bold;" id="pakuotes" class="content">
                <span style="font-weight: normal;">
                    &nbsp;&nbsp;
                    <!-- или<br /> -->
                    <br />
                    &nbsp;2 вариант – вес, объем и № документа для каждой
                    упаковки в отдельности:
                </span>
                <br />
                <p class="js__pack_one">
                    &nbsp; Вес:
                    <input type="text" id="p_svoris" required name="p_svoris[]" class="number" size="7"
                        maxlength="10" />
                    Объем:
                    <input type="text" name="p_turis[]" class="number" size="7" maxlength="10" />
                    <!-- № док. на упаковку:
          <input
            required
            type="text"
            name="p_doc_nr[]"
            size="12"
            maxlength="21"
          /> -->
                    <!-- № посылки (0000001 - ): -->
                    <!-- <input
                        required
                        type="text"
                        name="n_pos[]"
                        size="12"
                        value="0000001"
                        maxlength="21"
                    /> -->
                    Pallet:
                    <select name="p_pallet[]">
                        <option value="0" selected="">None</option>
                        <option value="2">1.2m/0.8m</option>
                        <option value="6">1.2m/1m</option>
                        <option value="7">1.2m/1.2m</option>
                        <option value="3">0.8m/0.6m</option>
                        <option value="4">other</option>
                    </select>
                </p>
                <span style="font-size: 8pt; font-weight: normal;"><a class="js__add__pack" href="#">[Добавить
                        упаковку]</a></span>
            </div>
        </div>


        <div class="pak_dalys">
            <button class="btn btn-primary float-right" type="submit">Внести данные</button>
        </div>
    </form>
</div>
