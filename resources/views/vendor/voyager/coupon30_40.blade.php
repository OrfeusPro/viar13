<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
@extends('voyager::master') @section('content')


    <div class="page-content">
        <table id="dataTable" class="table table-hover dataTable no-footer sortable" role="grid" aria-describedby="dataTable_info">
            <tr> <td>Пользователь</td> <td> Email</td> <td>Картинка</td><td>Подтвердить</td></tr>
            @foreach($users as $user)
                <tr>
                <td>{{ $user->first_name }} {{ $user->last_name }} </td>
                <td> {{ $user->email }}</td>
                    <td> <a href="{{ $user->screenshot2 }}"><img style="max-width: 200px;" src="{{ $user->screenshot2 }}"></a></td>

{{--  select/options with $user->is_facebook_sake options = 0 or 1 with default option depend on $user->is_facebook_sake  --}}
                    <td>

                        <select class="is_30_40_sale" data-id="{{ $user->id }}" name="is_30_40_sale">
                            <option value='0' {{ $user->is_30_40 == '0' ? 'selected' : '' }}>Нет</option>
                            <option value='1' {{ $user->is_30_40 == '1' ? 'selected' : '' }}>Да</option>
                            <option  {{ $user->is_30_40 === NULL ? 'selected' : '' }} disabled >Не проверено</option>
                        </select>
                    </td>

                </tr>
            @endforeach



        </table>




    </div>
	<style>

	</style>
	@stop @section('javascript')

	@include('vendor.voyager.partials.orders.bot_scripts')
@stop
<script>
    $(document).on('change', '.is_30_40_sale', function(e){
        var sale = $(this).val();
        var user_id = $(this).attr('data-id');

        e.preventDefault();


        var form_data = new FormData;
        form_data.append('sale', sale);
        form_data.append('user_id', user_id);

        $.ajax({
            processData: false,
            contentType: false,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/user/'+user_id+'/set_sale/2',
            // url: '/sale30_40/',
            data: form_data,
            success: function (response) {
                if (response) {

                }
            },
            error: function (error) {

                console.log(response)
                console.log(error);
            }
        });
    });



</script>
