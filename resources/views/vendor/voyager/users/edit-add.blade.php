@extends('voyager::master')

@section('page_title', __('voyager::generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular'))

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('voyager::generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular') }}
    </h1>
@stop

@section('content')
    <div class="page-content container-fluid">
        <form class="form-edit-add" role="form"
              action="@if(!is_null($dataTypeContent->getKey())){{ route('voyager.'.$dataType->slug.'.update', $dataTypeContent->getKey()) }}@else{{ route('voyager.'.$dataType->slug.'.store') }}@endif"
              method="POST" enctype="multipart/form-data" autocomplete="off">
            <!-- PUT Method if we are editing -->
            @if(isset($dataTypeContent->id))
                {{ method_field("PUT") }}
            @endif
            {{ csrf_field() }}

            <div class="row">
                <div class="col-md-8">
                    <div class="panel panel-bordered">
                    {{-- <div class="panel"> --}}
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="panel-body">

                            @if(isset($dataTypeContent->role))
                                @if($dataTypeContent->role->name  == 'painter')
                                <div class="form-group">
                                    <label>Заказы художника</label>
                                    <br>
                                    @php
                                        $painter_orders = \App\Models\User::get_painter_orders($dataTypeContent->id);
                                        $painter_selected = \App\Models\User::get_painter_selected_orders($dataTypeContent->id);
                                        $is_selected = 0;
                                    @endphp

                                    <select id="painter_cur_orders" name="painter_orders" multiple style="width:100%;height:200px;">
                                        <option value="">Нет</option>
                                        
                                        @foreach($painter_orders as $order_id)

                                            @if(is_array($painter_selected) && $painter_selected != [])

                                                @foreach($painter_selected as $selected_id)
                                                

                                                    @if($selected_id == $order_id)
                                                        @php 
                                                            $is_selected = 1;
                                                        @endphp
                                                        @break
                                                    @else
                                                        @php 
                                                            $is_selected = 0;
                                                        @endphp
                                                    @endif

                                                @endforeach

                                            @endif

                                            <option @if($is_selected == 1) selected @endif value="{{ $order_id }}">Заказ № {{ $order_id }}</option>
                                        @endforeach

                                    </select>

                                    <input type="hidden" name="painter_id" id="painter_id" value="{{ $dataTypeContent->id }}">
                                    <button type="button" class="btn btn-primary js_update_painter">Обновить</button>
                                </div>
                                @endif
                            @endif
                            
                            <div class="form-group">
                                <label for="email">{{ __('voyager::generic.email') }}</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="{{ __('voyager::generic.email') }}"
                                       value="{{ old('email', $dataTypeContent->email ?? '') }}">
                            </div>

                            <div class="form-group">
                                <label for="nick">Никнейм</label>
                                <input type="text" class="form-control" id="nick" name="nick" placeholder="Никнейм"
                                       value="{{ old('nick', $dataTypeContent->nick ?? '') }}">
                            </div>
                            
                            @can('editRoles', $dataTypeContent)
                                <div class="form-group">
                                    <label for="default_role">{{ __('voyager::profile.role_default') }}</label>
                                    @php
                                        $dataTypeRows = $dataType->{(isset($dataTypeContent->id) ? 'editRows' : 'addRows' )};

                                        $row     = $dataTypeRows->where('field', 'user_belongsto_role_relationship')->first();
                                        $options = $row->details;
                                    @endphp
                                    @include('voyager::formfields.relationship')
                                </div>
                                {{-- <div class="form-group">
                                    <label for="additional_roles">{{ __('voyager::profile.roles_additional') }}</label>
                                    @php
                                        $row     = $dataTypeRows->where('field', 'user_belongstomany_role_relationship')->first();
                                        $options = $row->details;
                                    @endphp
                                    @include('voyager::formfields.relationship')
                                </div> --}}
                            @endcan

                            <div class="form-group">
                                <label for="name">Имя</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" placeholder=""
                                       value="{{ $dataTypeContent->first_name }}">
                            </div>

                            <div class="form-group">
                                <label for="name">Фамилия</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" placeholder=""
                                       value="{{  $dataTypeContent->last_name }}">
                            </div>

                            <div class="form-group">
                                <label for="name">Телефон</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder=""
                                       value="{{  $dataTypeContent->phone }}">
                            </div>

                            
                            

                            <div class="form-group">
                                <label>Запрос на отзыв:</label><br>
                                @php
                                    $user_last_order_id = \App\Models\User::getUserLastOrderId($dataTypeContent->id);
                                @endphp
                                @if($user_last_order_id != '' && $user_last_order_id != null)
                                <a href="/admin/user/{{ $dataTypeContent->id }}/leave_rev/{{ $user_last_order_id }}">Запросить</a>
                                @else
                                <a href="/admin/user/{{ $dataTypeContent->id }}/leave_rev">Запросить</a>
                                @endif
                            </div>


                            <div class="form-group">
                                <label for="name">Адрес</label>
                                <input type="text" class="form-control" id="address" name="address" placeholder=""
                                       value="{{  $dataTypeContent->address }}">
                            </div>

                            <div class="form-group">
                                <label for="name">PrintScreen</label>
                                <a target="_blank" href="{{ $dataTypeContent->screenshot }}">Ссылка</a>
                            </div>

                            @php
                                $statuses = \App\Models\User::getRoles();
                            @endphp
                            <div class="form-group">
                                <label for="name">Статус клиента</label>
                                <select name="status" id="">
                                    <option value=""></option>
                                    @foreach($statuses as $status)
                                    <option 
                                        @if($dataTypeContent->client_status == $status->id)
                                            selected
                                        @endif
                                    value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="name">К-во бонусов</label>
                                <input type="text" class="form-control" id="bonuses" name="bonuses" placeholder=""
                                       value="{{  $dataTypeContent->bonuses }}">
                            </div>


                            <div class="form-group">
                                <label for="name">Пригласил друга?</label>
                                @if($dataTypeContent->inv_sale_code == 'alredy_used')
                                <p>да</p>
                                @else
                                <p>нет</p>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="name">Код для приглашения 
                                    (чтобы сбросить - введите "regenerate", сохраните и обновите страницу "stocks")</label>
                                <input type="text" class="form-control" id="inv_sale_code" name="inv_sale_code" placeholder=""
                                       value="{{  $dataTypeContent->inv_sale_code }}">
                            </div>

                            <div class="form-group">
                                <label for="name">Подарочная карта (купон)</label>
                                <input type="text" class="form-control" id="active_coupon" name="active_coupon" placeholder=""
                                       value="{{  $dataTypeContent->active_coupon }}">
                            </div>

                            <div class="form-group">
                                <label for="name">Дата-1</label>
                                <input type="text" class="form-control" id="date1" name="date1" placeholder=""
                                       value="{{  $dataTypeContent->date1 }}">
                            </div>

                            <div class="form-group">
                                <label for="name">Торжество-1</label>
                                <input type="text" class="form-control" id="torj1" name="torj1" placeholder=""
                                       value="{{  $dataTypeContent->torj1 }}">
                            </div>

                            <div class="form-group">
                                <label for="name">Дата-2</label>
                                <input type="text" class="form-control" id="date2" name="date2" placeholder=""
                                       value="{{  $dataTypeContent->date2 }}">
                            </div>

                            <div class="form-group">
                                <label for="name">Торжество-2</label>
                                <input type="text" class="form-control" id="torj2" name="torj2" placeholder=""
                                       value="{{  $dataTypeContent->torj2 }}">
                            </div>


                            <div class="form-group">
                                <label for="is_facebook_sale">Скидка за facebook активна?</label>
                                <select id="is_facebook_sale" name="is_facebook_sale">
                                    @if($dataTypeContent->is_facebook_sale == 1)
                                    <option selected value="1">да</option>
                                    @else
                                    <option value="1">да</option>
                                    @endif
                                    @if($dataTypeContent->is_facebook_sale == 0)
                                    <option selected value="0">нет</option>
                                    @else
                                    <option value="0">нет</option>
                                    @endif
                                </select>
                                {{-- <input class="form-control" 
                                name="is_facebook_sale" value="{{ $dataTypeContent->is_facebook_sale }}"> --}}
                            </div>
                            <div class="form-group">
                                <label for="password">{{ __('voyager::generic.password') }}</label>
                                @if(isset($dataTypeContent->password))
                                    <br>
                                    <small>{{ __('voyager::profile.password_hint') }}</small>
                                @endif
                                <input type="password" class="form-control" id="password" name="password" value="" autocomplete="new-password">
                            </div>

                            @php
                            if (isset($dataTypeContent->locale)) {
                                $selected_locale = $dataTypeContent->locale;
                            } else {
                                $selected_locale = config('app.locale', 'en');
                            }

                            @endphp
                            <div class="form-group">
                                <label for="locale">{{ __('voyager::generic.locale') }}</label>
                                <select class="form-control select2" id="locale" name="locale">
                                    @foreach (Voyager::getLocales() as $locale)
                                    <option value="{{ $locale }}"
                                    {{ ($locale == $selected_locale ? 'selected' : '') }}>{{ $locale }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="panel panel panel-bordered panel-warning">
                        <div class="panel-body">
                            <div class="form-group">
                                @if(isset($dataTypeContent->avatar))
                                    <img src="{{ filter_var($dataTypeContent->avatar, FILTER_VALIDATE_URL) ? $dataTypeContent->avatar : Voyager::image( $dataTypeContent->avatar ) }}" style="width:200px; height:auto; clear:both; display:block; padding:2px; border:1px solid #ddd; margin-bottom:10px;" />
                                @endif
                                <input type="file" data-name="avatar" name="avatar">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary pull-right save">
                {{ __('voyager::generic.save') }}
            </button>
        </form>

        <iframe id="form_target" name="form_target" style="display:none"></iframe>
        <form id="my_form" action="{{ route('voyager.upload') }}" target="form_target" method="post" enctype="multipart/form-data" style="width:0px;height:0;overflow:hidden">
            {{ csrf_field() }}
            <input name="image" id="upload_file" type="file" onchange="$('#my_form').submit();this.value='';">
            <input type="hidden" name="type_slug" id="type_slug" value="{{ $dataType->slug }}">
        </form>
    </div>
@stop

@section('javascript')
    <script>
        $('document').ready(function () {
            $('.toggleswitch').bootstrapToggle();
        });

        $('.js_update_painter').on('click', function (e) {

            let cur_painter_vals = $('#painter_cur_orders').val();
            let cur_user_id = $('#painter_id').val();

            let painterData = new FormData();
            painterData.append('orders', cur_painter_vals);
            painterData.append('user_id', cur_user_id);
                       
            $.ajax({
                processData: false,
                contentType: false,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('update_painter_orders') }}',
                data: painterData,
                success: function (response) {
                    var data = jQuery.parseJSON(response);
                    
                    if(data.succes){
                        alert('Успешно обновлено');
                    }
                },
                error: function (error) {
                    alert(error);
                    console.log(error);
                }
            });
            
        });


    </script>
@stop
