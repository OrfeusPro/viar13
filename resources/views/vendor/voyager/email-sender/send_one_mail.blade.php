@extends('voyager::master')

@section('page_title', 'Написать email')


@section('content')

    <div class="page-content browse container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        @if (\Session::has('status'))
                            <div class="alert alert-success">
                                <ul>
                                    <li>{!! \Session::get('status') !!}</li>
                                </ul>
                            </div>
                        @endif
                        <h1>Написать email</h1>

                        <form action="{{ route('mail.send') }}" method="post">

                            <input type="hidden" name="users[]" value="{{$user_id}}"/>

                            <h3 class="panel-title">Пользователю {{ $user_name }} {{ $user_lastname }}</h3>

                            <h3 class="panel-title">Email {{ $user_email }}</h3>

                            <h3 class="panel-title">Язык пользователя {{ $user_locale }} </h3>

                            <h3 class="panel-title">Тема</h3>

                            <input class="form-control" type="text" name="subject" value="Скидки!" required>
                            <h3 class="panel-title">Поздравление</h3>
                            <input class="form-control" type="text" name="greetings" value="Привет от viarcanvas" required>
                            <div class="col-lg-6">
                            <h3 class="panel-title">Основной текст</h3>
                            <textarea style="resize:none;" class="form-control" name="line" id="" cols="30" rows="10"
                                      required>{{ old('line') }}</textarea>
                            </div>
                            <div class="col-lg-6">
                                <h3 class="panel-title">Превью</h3>
                                <textarea class="form-control richTextBox" cols="30" rows="10" ></textarea>
                            </div>
                            <h3 class="panel-title">Прощание</h3>
                            <input class="form-control" type="text" name="salutation"
                                   value="Спасибо, что пользуетесь нашим ресурсом!" required>

                            @csrf
                            <button class="btn btn-success" type="submit">Отправить</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // var no_mce_image = 1;
    </script>

    <script src="{{ asset('admin/voyager-assets?path=js%2Fapp.js') }}"></script>
    <script src="{{ asset('admin_assets/js/custom_new_v2.js') }}"></script>

    <style>
        .richTextBox{
            max-width: 100%;
        }
        .mce-content-body img[data-mce-selected], .mce-content-body hr[data-mce-selected],
        img{
            max-width: 100%;
        }
    </style>
    <script>
        // $.extend(additionalConfig, "{}")
        $(document).ready(function() {
            var additionalConfig = {
                selector: 'textarea.richTextBox',
            }
            tinymce.init(window.voyagerTinyMCE.getConfig(additionalConfig));
        });
    </script>
@stop
