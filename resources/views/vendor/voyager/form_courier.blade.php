<form method="POST" action="{{ route('send_courier') }}">
    {{ csrf_field() }}
    <table>
        <tbody>
            <tr class="tr__r flex_child_td_50">
                <td>
                    <b>Отправитель</b>
                </td>
            </tr>
            <tr class="tr__r flex_child_td_50">
                <td>Название</td>
                <td>
                    <input class="w-100p" type="text" size="40" name="s_name" id="s_name" maxlength="60"
                        value="{{ $activeVenipakData->s_name }}" class="valid" aria-invalid="false" />
                </td>
            </tr>
            <tr class="tr__r flex_child_td_50">
                <td>Код предпр.</td>
                <td>
                    <input class="w-100p" type="text" size="40" name="s_code" id="s_code" maxlength="60"
                        value="{{ $activeVenipakData->s_code }}" />
                </td>
            </tr>
            <tr class="tr__r flex_child_td_50">
                <td>Страна</td>
                <td>
                    <select name="s_country" id="s_country" class="w-100p">
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
            <tr class="tr__r flex_child_td_50">
                <td>Адрес</td>
                <td>
                    <input class="w-100p" type="text" size="40" name="s_address" id="s_address" maxlength="50"
                        value="{{ $activeVenipakData->s_address }}" />
                </td>
            </tr>
            <tr class="tr__r flex_child_td_50">
                <td>Город</td>
                <td>
                    <input class="w-100p" type="text" size="40" name="s_city" id="s_city" maxlength="40"
                        value="{{ $activeVenipakData->s_city }}" />
                </td>
            </tr>
            <tr class="tr__r flex_child_td_50">
                <td>
                    Почтовый код <br /><span class="warning">При указании неверного почтового кода посылка не будет
                        взята у Отправителя</span>
                </td>
                <td>
                    <input class="w-100p" type="text" size="10" name="s_post" id="s_post" maxlength="8" value="{{ $activeVenipakData->s_post }}" />
                </td>
            </tr>
            <tr class="tr__r flex_child_td_50">
                <td valign="top">Контактное лицо</td>
                <td>
                    <input class="w-100p" type="text" size="40" name="s_contact_p" id="s_contact_p" maxlength="50"
                        value="{{ $activeVenipakData->s_contact_p }}" />
                </td>
            </tr>
            <tr class="tr__r flex_child_td_50">
                <td valign="top">Телефон</td>
                <td>
                    <input class="w-100p" type="text" size="40" name="s_contact_t" id="s_contact_t" maxlength="30"
                        value="{{ $activeVenipakData->s_contact_t }}" />
                </td>
            </tr>
            <tr class="tr__r flex_child_td_50">
                <td valign="top">Эл. почта</td>
                <td>
                    <input class="w-100p" type="email" name="contact_mail" value="{{ $activeVenipakData->email_sender ?? '' }}" />
                </td>
            </tr>
        </tbody>
    </table>

    <table class="table__full">
        <tbody>
            <tr class="tr__r flex_child_td_50">
                <td>Вес</td>
                <td>
                    <input required class="w-100p" type="text" size="10" id="svoris_s" name="svoris_s" maxlength="6"
                        value="" />
                </td>
            </tr>
            <tr class="tr__r flex_child_td_50">
                <td>Объем</td>
                <td>
                    <input class="w-100p" type="text" size="10" id="turis_s" name="turis_s" maxlength="6" value="" />
                </td>
            </tr>
            <tr class="tr__r flex_child_td_50">
                <td>Кол-во паллет</td>
                <td>
                    <select class="w-100p" name="pallets_s" id="palletes_s">
                        <option value="">-- Выберите --</option>
                        <option value="0">Hет паллет</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                        <option value="13">13</option>
                        <option value="14">14</option>
                        <option value="15">15</option>
                        <option value="16">16</option>
                        <option value="17">17</option>
                        <option value="18">18</option>
                        <option value="19">19</option>
                        <option value="20">20</option>
                        <option value="21">21</option>
                        <option value="22">22</option>
                        <option value="23">23</option>
                        <option value="24">24</option>
                        <option value="25">25</option>
                        <option value="26">26</option>
                        <option value="27">27</option>
                        <option value="28">28</option>
                        <option value="29">29</option>
                        <option value="30">30</option>
                        <option value="31">31</option>
                        <option value="32">32</option>
                        <option value="33">33</option>
                        <option value="34">34</option>
                        <option value="35">35</option>
                        <option value="36">36</option>
                        <option value="37">37</option>
                        <option value="38">38</option>
                        <option value="39">39</option>
                        <option value="40">40</option>
                    </select>
                </td>
            </tr>
            <tr class="tr__r flex_child_td_50">
                <td>Примечания</td>
                <td>
                    <input class="w-100p" type="text" size="40" name="pastabos_s" id="pastabos_s" maxlength="160"
                        value="" />
                </td>
            </tr>
            <tr class="tr__r flex_child_td_50">
                <td>№ док.</td>
                <td>
                    <input class="w-100p" type="text" size="40" name="doknr_s" id="doknr_s" maxlength="16" value="" />
                </td>
            </tr>
            <tr class="tr__r flex_child_td_50">
                <td valign="top">Время исполнения</td>
                <td>
                    <table cellpadding="3" cellspacing="0">
                        <tbody>
                            <tr class="tr__r flex_child_td_50">
                                <td>Дата:</td>
                                <td>
                                    <input class="w-100p" required type="date" id="datepicker" size="10" name="metai_s"
                                        maxlength="10" />
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td>С:</td>
                                <td nowrap="">
                                    <select name="val_nuo_s">
                                        <option value="08">08</option>
                                        <option value="09" selected="">09</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                        <option value="13">13</option>
                                        <option value="14">14</option>
                                        <option value="15">15</option>
                                        <option value="16">16</option>
                                        <option value="17">17</option>
                                        <option value="18">18</option>
                                        <option value="19">19</option>
                                        <option value="20">20</option>
                                        <option value="21">21</option>
                                        <option value="22">22</option>
                                    </select>
                                    :
                                    <select name="min_nuo_s">
                                        <option value="00">00</option>
                                        <option value="15">15</option>
                                        <option value="30">30</option>
                                        <option value="45">45</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="tr__r">
                                <td>По:</td>
                                <td>
                                    <select name="val_iki_s">
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                        <option value="13">13</option>
                                        <option value="14">14</option>
                                        <option value="15">15</option>
                                        <option value="16">16</option>
                                        <option value="17" selected="">17</option>
                                        <option value="18">18</option>
                                        <option value="19">19</option>
                                        <option value="20">20</option>
                                        <option value="21">21</option>
                                        <option value="22">22</option>
                                        <option value="23">23</option>
                                    </select>
                                    :
                                    <select name="min_iki_s">
                                        <option value="00">00</option>
                                        <option value="15">15</option>
                                        <option value="30">30</option>
                                        <option value="45">45</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <button class="btn btn-primary float-right" type="submit">Отправить</button>
</form>
