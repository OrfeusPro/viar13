@extends('voyager::master') @section('content')
    {{-- new --}}
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        .icon-icon22,
        .icon-icon25 {
            position: relative;
        }

        i.icon-icon22:before {
            content: '<';
        }

        i.icon-icon25:before {
            content: '>';
        }

    </style>
    {{-- endnew --}}
    <div class="container-fluid">
        <h1 class="page-title"><i class="voyager-basket"></i> Клиенты</h1>

        <div class="page-content">
            @include('voyager::alerts')
            @include('voyager::dimmers')
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-bordered">
                        <div class="panel-body">
                            <div class="table-responsive">
                                <div id="dataTable_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
                                    <div class="row">
                                        <table id="dataTable" class="table table-hover dataTable no-footer" role="grid"
                                            aria-describedby="dataTable_info">
                                            <thead>
                                                <tr role="row">
                                                    <th>
                                                        ID
                                                    </th>
                                                    <th>
                                                        Email
                                                    </th>
                                                    <th>
                                                        Имя
                                                    </th>
                                                    <th>
                                                        Фамилия
                                                    </th>
                                                    <th>
                                                        Телефон
                                                    </th>
                                                    <th>
                                                        Адрес
                                                    </th>
                                                    <th>
                                                        Почтовый индекс
                                                    </th>
                                                    <th>
                                                        Страна
                                                    </th>
                                                    <th>
                                                        Подписан на новости
                                                    </th>
                                                    <th>
                                                        Заказы
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($users as $user)
                                                    <tr role="row">
                                                        <td><a
                                                                href="/admin/users/{{ $user->id }}/edit">{{ $user->id }}</a>
                                                        </td>
                                                        <td>{{ $user->email }}</td>
                                                        <td>{{ $user->first_name }}</td>
                                                        <td>{{ $user->last_name }}</td>
                                                        <td>{{ $user->phone }}</td>
                                                        <td>{{ $user->address }}</td>
                                                        <td>{{ $user->postal_index }}</td>
                                                        <td>{{ $user->country }}</td>
                                                        <td>{{ $user->news }}</td>
                                                        <td>
                                                            @php
                                                                $user_last_order_id = \App\Models\User::getUserLastOrderId($user->id);
                                                            @endphp
                                                            @if (!empty($user_last_order_id))
                                                                <a
                                                                    href="/admin/orders?user_id={{ $user->id }}">Список</a>
                                                            @else
                                                                Нет заказов
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @stop
    @section('javascript')

    </div>
