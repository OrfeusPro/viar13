@foreach ($citys as $city)
	<a href="#" class="select__option @if($loop->index == 0)  @endif" data-value="{{ $city['city'] }}">{{ $city['city'] }}</a>
@endforeach