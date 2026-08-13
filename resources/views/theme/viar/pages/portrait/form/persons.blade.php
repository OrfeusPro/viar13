<div class="formalization-item">
<div class="formalization-box">
    <div class="formalization-tab">
        <p>{!! trans('portrait_buy_form.step4_title') !!}</p>
        <span class="tab-icon"></span>
    </div>
    <div class="formalization-content">
        <div class="formalization-content--inner">
            <div class="kviz-row">
                <div class="
                                  kviz-group__item
                                  kviz-c-group kviz-custom-flexcol
                                ">
                    <div class="
                                    kviz-radio
                                    js-checkbox
                                    kviz-radio_active
                                    kviz-types
                                  " data-stock="1" data-count="1">
                        <div class="check"></div>
                        <label>
                            <span>{!! trans('portrait_buy_form.step4_personal') !!}</span>
                            <input class="js_personal_checker" type="radio" name="count" value="1"/>
                        </label>
                    </div>
                    <div class="kviz-radio js-checkbox kviz-types" data-stock="2" data-count="2">
                        <div class="check"></div>
                        <label>
                            <span>{!! trans('portrait_buy_form.step4_group') !!}</span>
                            <input type="radio" name="count" value="Group"/>
                        </label>
                    </div>
                    <div class="input-group input_disabled">
                        <label for="count1">{!! trans('portrait_buy_form.sterp4_enter_persons_count') !!}</label>
                        <input id="count1" class="kviz-input" type="text" disabled/>
                    </div>
                </div>
                    <div class="kviz-group__item kviz-c-group input_disabled js__group__users">
                    @isset($item['custom_users_prices'])
                        @php
                            $custom_users_count = explode(',', $item['custom_users_prices']);
                        @endphp

                        @if(is_array($custom_users_count) && !empty($custom_users_count) && $custom_users_count[0] != "")
                            @foreach($custom_users_count as $user)
                                @php
                                    $price = get_string_between($user, '[', ']');
                                    $user_count = substr($user, 0, strpos($user, "["));
                                @endphp
                                <label class="js__personal_checker kviz-radio js-checkbox kviz-types" @if($loop->first) style="display:none;" @endif data-stock="1">
                                    <em class="check"></em>
                                    <div>
                                        <span>{{ $user_count }} {!! trans('gl.chel') !!} + <b>{{ $price * $contry_mult }}€</b></span>
                                        <input
                                               @if($loop->first) class="js_per_user" @endif
                                               type="radio" name="users"
                                               data-count="{{ $user_count }}"
                                               value="{{ $user_count }}"
                                               data-price="{{ $price * $contry_mult }}" disabled/>
                                    </div>
                                </label>
                            @endforeach
                        @endif
                    @endisset
                </div>
            </div>
        </div>
    </div>
</div>
<div class="formalization-prompt">
    <div class="formalization-prompt--wrapper">
        <div class="formalization-prompt--inner">
            <img src="{{ asset('images/prompt4.png') }}" alt=""/>
            <p>{!! trans('portrait_buy_form.step4_bot_desc') !!}</p>
        </div>
    </div>
</div></div>
