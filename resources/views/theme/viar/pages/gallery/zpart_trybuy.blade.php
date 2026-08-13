@if(!$module_sale->isEmpty() || !$foto_sale->isEmpty() || !$repr_sale->isEmpty() )

@php if(isset($white) && $white == true)  {
		$white = true;
	}
	else {
		$white = false;
	}
@endphp

@if(!$white)
<div class="ellipse rotated-ell">
	<img alt="img" src="{{ asset(env('THEME') . 'images') }}/icon/ellipse-whete.svg" decoding="async" height="99" width="1374">
</div>
@endif

<div class="trybuy-main @if($white) stock-buy @endif">
	<div class="section-frame">
		<div class="trybuy-main__inner">
			<div class="trybuy-title page-title">
				@if($white)
					<p>@lang("gallery.trybuy_t1_title")</p>
					<span>@lang("gallery.trybuy_t1_desc")</span>
				@else
					<span>@lang("gallery.trybuy_t1_title")</span>
				@endif
			</div>
			<div class="category-pop--block">
				<div class="cp-tabs">
					<div class="cp-tabs-inner">

						@php


							if (!$module_sale->isEmpty()) {
								$module_sale_active = "active";
								$foto_sale_active = "";
								$repr_sale_active = "";
							} else if (!$foto_sale->isEmpty()) {
								$module_sale_active = "";
								$foto_sale_active = "active";
								$repr_sale_active = "";
							} else if (!$repr_sale->isEmpty()) {
								$module_sale_active = "";
								$foto_sale_active = "";
								$repr_sale_active = "active";
							} else {
								$module_sale_active = "";
								$foto_sale_active = "";
								$repr_sale_active = "";
							}
						@endphp
                        <?php // var_dump($module_sale); ?>

						@if(!$module_sale->isEmpty())
							<div class="cp-tab {{ $module_sale_active }}">
								{!! $gallery['modc_title'] !!}
							</div>
						@endif
						@if(!$repr_sale->isEmpty())
							<div class="cp-tab {{ $repr_sale_active }}">
								{!! $gallery['repr_title'] !!}
							</div>
						@endif
						@if(!$foto_sale->isEmpty())
							<div class="cp-tab {{ $foto_sale_active }}">
								{!! $gallery['fotoc_title'] !!}
							</div>
						@endif
					</div>
				</div>

				<div class="cp-blocks">
					@foreach ($module_sale as $item)
						@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_trybuy_item', ['item' => $item, 'active' => $module_sale_active])
						@break
					@endforeach

					@foreach ($repr_sale as $item)
						@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_trybuy_item', ['item' => $item, 'active' => $repr_sale_active])
						@break
					@endforeach

					@foreach ($foto_sale as $item)
						@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.zpart_trybuy_item', ['item' => $item, 'active' => $foto_sale_active])
						@break
					@endforeach

				</div>
			</div>
		</div>
	</div>
</div>
@endif
