@foreach ($warehouses as $warehouse)
    @if ($warehouse["type"] == 3)
        @continue;
    @endif
	<div class="cart-delivery-point__pickup-point--item @if ($loop->index == 0) js-active @endif" data-city="{{ $warehouse['city'] }}" data-id="{{ $warehouse['id'] }}" data-name="{{ $warehouse['display_name'] }} {{ $warehouse['address'] }}">
		<span class="warehouse">
			{{ $warehouse['display_name'] }}<br>
			{{ $warehouse['address'] }}<br>
			@foreach (json_decode($warehouse['working_hours'], true) as $time)
				@php
					if ($time['from_h'] == 0) {
					$time['from_h'] = '00';
					}
					if ($time['from_m'] == 0) {
					$time['from_m'] = '00';
					}
					if ($time['to_h'] == 0) {
					$time['to_h'] = '00';
					}
					if ($time['to_m'] == 0) {
					$time['to_m'] = '00';
					}
				@endphp
				{{ $time['from_h'] }}:{{ $time['from_m'] }} - {{ $time['to_h'] }}:{{ $time['to_m'] }}<br>
			@endforeach
			{{-- {{ $warehouse['city'] }} --}}
		</span>
	</div>
@endforeach
