@extends('voyager::master')

@section('content')
    <style>
        #LocaleTemplate p {
            position: relative;
            padding: 5px 0;
        }

        #LocaleTemplate .input-template {
            position: absolute;
            top: 0px;
            left: 300px;
            width: 700px;
        }

    </style>
    <div id="LocaleTemplate" class="page-content locale-template" style="padding: 0 30px;">
        @include('voyager::alerts')
        @include('voyager::dimmers')

        <select name="lng" style="width: 150px">
            @foreach ($locales as $locale)
                <option value="{{ $locale->prefix }}">{{ $locale->name }}</option>
            @endforeach
        </select>

        <div class="block" style="margin-top: 30px;">
            <h2></h2>

            <div class="templates"></div>
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.5.0.min.js"
        integrity="sha256-xNzN2a4ltkB44Mc/Jz3pT4iU1cmeR0FkXs4pru/JxaQ=" crossorigin="anonymous"></script>
    <script>
        $(function() {
            var ajax = function(prefix) {
                $.ajax({
                    method: 'POST',
                    url: '/admin/ajax_admin_get_lng_template',
                    data: {
                        lng: prefix
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    success: function(templates) {
                        $('#LocaleTemplate .templates').html('');

                        for (var key in templates) {
                            $('#LocaleTemplate .templates').append('<h2>' + templates[key].name +
                                '</h2>')

                            for (var key2 in templates[key]['template']) {
                                var level2 = templates[key]['template'][key2]

                                if (typeof level2 === 'object') {
                                    $('#LocaleTemplate .templates').append('<h4>' + key2 + '</h4>')

                                    for (var key3 in level2) {
                                        var level3 = level2[key3]

                                        if (typeof level3 === 'object') {
                                            $('#LocaleTemplate .templates').append(
                                                '<h4> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + key3 +
                                                '</h4>')

                                            for (var key4 in level3) {
                                                var level4 = level3[key4]

                                                $('#LocaleTemplate .templates').append(
                                                    '<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>' +
                                                    key4 + ': </b> <input  template="' +
                                                    templates[key].name + '"  lvl="3" key="' +
                                                    key2 + ':' + key3 + ':' + key4 +
                                                    '"  class="input-template" type="text" value="' +
                                                    level4 + '"></p>')
                                            }
                                        } else {
                                            $('#LocaleTemplate .templates').append(
                                                '<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>' +
                                                key3 + ': </b> <input  template="' + templates[
                                                    key].name + '"  lvl="2" key="' + key2 +
                                                ':' + key3 +
                                                '" class="input-template" type="text" value="' +
                                                level3 + '"></p>')
                                        }
                                    }
                                } else {
                                    $('#LocaleTemplate .templates').append('<p><b>' + key2 +
                                        ': </b> <input template="' + templates[key].name +
                                        '" lvl="1" key="' + key2 +
                                        '" class="input-template" type="text" value="' +
                                        templates[key]['template'][key2] + '"></p>')
                                }
                            }
                        }
                    },
                    error: function(jqXHR, exception) {

                    }
                })
            }

            var lng = $('#LocaleTemplate select option:selected').html()
            var prefix = $('#LocaleTemplate select option:selected').val()
            $('#LocaleTemplate .block > h2').html(lng)

            ajax(prefix)

            $('body').on('blur', '#LocaleTemplate .input-template', function() {
                var key = $(this).attr('key'),
                    value = $(this).val(),
                    prefix = $('#LocaleTemplate select option:selected').val(),
                    template = $(this).attr('template')


                $.ajax({
                    method: 'POST',
                    url: '/admin/ajax_admin_set_lng_template',
                    data: {
                        key: key,
                        value: value,
                        prefix: prefix,
                        template: template
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    success: function(response) {

                    },
                    error: function(jqXHR, exception) {

                    }
                })
            })

            $('body').on('change', '#LocaleTemplate select', function() {
                var lng = $(this).find('option:selected').html()
                var prefix = $(this).val()
                $('#LocaleTemplate .block > h2').html(lng)

                ajax(prefix)
            })
        })

    </script>
@endsection
