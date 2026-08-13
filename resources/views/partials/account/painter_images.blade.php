
	@if(Auth::user()->role->name == 'user' and $order->is_show_painter_images == 1)
		
		@php
			$pimg = preg_replace("/\s+/", "", $order->painter_sketch_images);
			$painter_sketch_images = trim($pimg, ',');
			$painter_sketch_images = explode(',',$painter_sketch_images);
		@endphp
		
		<div class="commentary commentary_sketch_painter js_hb_sketch_painter_{{$order->id}}" style="border: 1px solid #e3e3e3; padding:15px;">
				<p class="painter_title" style="margin-bottom:0px;padding: 15px;float:left">Набросок художника:</p>
				<div class="form_group painter_images_group" style="margin-top:20px;">
		@if((isset($painter_sketch_images[0]) && $painter_sketch_images[0]) || (isset($painter_sketch_images[1]) && $painter_sketch_images[1]))
					
					@foreach($painter_sketch_images as $painter_img)
						@php
							$ext = pathinfo($painter_img, PATHINFO_EXTENSION);
							$imgExists = order_image_exists($painter_img);
							$imgSrc = order_image_url($painter_img);
						@endphp
						<div class="painter_img" style="text-align: center;" data-src="{{ $painter_img }}">
							<a target="_blank" href="{{ $imgSrc }}">
								@if(!$imgExists)
								<img style="max-width:100%;max-height:100px;" src="{{ order_image_placeholder() }}" alt="">
								@elseif($ext == 'psd')
								<img style="max-width:100%;max-height:100px;" src="/img/psd.svg" alt="">
								@elseif ($ext == 'pdf')
								<img style="max-width:100%;max-height:100px;" src="/img/pdf.svg" alt="">
								@else
								<img style="max-width:100%;max-height:100px;" src="{{ $imgSrc }}" alt="">
								@endif
							</a>
						</div>
					@endforeach
		@endif
				</div>
		</div>
	
		@php
			$pimg = preg_replace("/\s+/", "", $order->painter_images);
			$painter_images = trim($pimg, ',');
			$painter_images = explode(',',$painter_images);
		@endphp	
		
		<div class="commentary commentary_painter js_hb_painter_{{$order->id}}">
				<p class="painter_title" style="margin-bottom:0px;">@lang('account.painter_images'):</p>
				<div class="form_group painter_images_group" style="margin-top:20px;">
		@if((isset($painter_images[0]) && $painter_images[0]) || (isset($painter_images[1]) && $painter_images[1]))

					
					@foreach($painter_images as $painter_img)
						@php
							$ext = pathinfo($painter_img, PATHINFO_EXTENSION);
							$imgExists = order_image_exists($painter_img);
							$imgSrc = order_image_url($painter_img);
						@endphp
						<div class="painter_img" style="text-align: center;" data-src="{{ $painter_img }}">
							<a target="_blank" href="{{ $imgSrc }}">
								@if(!$imgExists)
								<img style="max-width:100%;max-height:100px;" src="{{ order_image_placeholder() }}" alt="">
								@elseif($ext == 'psd')
								<img style="max-width:100%;max-height:100px;" src="/img/psd.svg" alt="">
								@elseif ($ext == 'pdf')
								<img style="max-width:100%;max-height:100px;" src="/img/pdf.svg" alt="">
								@else
								<img style="max-width:100%;max-height:100px;" src="{{ $imgSrc }}" alt="">
								@endif
							</a>
						</div>
					@endforeach
		@endif
				</div>
		</div>
	@endif
	
	@if(Auth::user()->role->name != 'user')
		@php
			$painter_sketch_images = explode(',',$order->painter_sketch_images);
		@endphp
		
		<div class="commentary commentary_sketch_painter js_hb_sketch_painter_{{$order->id}}" style="border: 1px solid #e3e3e3; padding:15px;">
			<p class="painter_title" style="margin-bottom:0px;float:left">Набросок художника:</p>
			@if(isset($order->painter_sketch_images_status['id']) && $order->painter_sketch_images_status && ((isset($painter_sketch_images[0]) && $painter_sketch_images[0]) || (isset($painter_sketch_images[1]) && $painter_sketch_images[1])))
				<div style="font-size:16px; float:right;">
					@if($order->painter_images_status_date)Статус: <strong><span style="color: {{$order->painter_sketch_images_status['color']}};">{{ $order->painter_sketch_images_status['title'] }}</span></strong> ({{$order->painter_images_status_date}}) @endif
				</div>
			<br>
			@endif
			<div class="form_group painter_images_group" style="margin-top:20px; width:100%; text-align: center;">
			@if((isset($painter_sketch_images[0]) && $painter_sketch_images[0]) || (isset($painter_sketch_images[1]) && $painter_sketch_images[1]))
				
				@php
					$i = -1;
				@endphp
									
				@foreach($painter_sketch_images as $painter_img)
					@if($painter_img)
						@php
							$ext = pathinfo($painter_img, PATHINFO_EXTENSION);
							$imgExists = order_image_exists($painter_img);
							$imgSrc = order_image_url($painter_img);
						@endphp
						<div class="painter_img" style="text-align: center;">
							<a target="_blank" href="{{ $imgSrc }}">
								@if(!$imgExists)
								<img style="max-width:100%;max-height:100px;" src="{{ order_image_placeholder() }}" alt="">
								@elseif($ext == 'psd')
								<img style="max-width:100%;max-height:100px;" src="/img/psd.svg" alt="">
								@elseif ($ext == 'pdf')
								<img style="max-width:100%;max-height:100px;" src="/img/pdf.svg" alt="">
								@else
								<img style="max-width:100%;max-height:100px;" src="{{ $imgSrc }}" alt="">
								@endif
							</a>
							@php
								$i++;
							@endphp
							@if(isset($order->painter_sketch_images_status['id']) && ($order->painter_sketch_images_status['id'] != 4 || $order->painter_sketch_images_status['id'] != 3))
							<a href="{{ route('remove_painter_sketch_image', ['id' => $order['id'], 'img_id' => $i]) }}"
							   class="btn btn-danger" type="submit">x</a>
							@endif
						</div>
					@endif
				@endforeach
			@endif
			</div>
		</div>
		
		@php
			$painter_images = explode(',',$order->painter_images);
		@endphp
		<div class="commentary commentary_painter js_hb_painter_{{$order->id}}">
			<p class="painter_title" style="margin-bottom:0px; float:left;">@lang('account.painter_images'):</p>
			@if(isset($order->painter_images_status['id']) && $order->painter_images_status && ((isset($painter_images[0]) && $painter_images[0]) || (isset($painter_images[1]) && $painter_images[1])))
				<div style="font-size:16px; float:right;">
					@if($order->painter_images_status_date)Статус: <strong><span style="color: {{$order->painter_images_status['color']}};">{{ $order->painter_images_status['title'] }}</span></strong> ({{$order->painter_images_status_date}}) @endif
				</div>
			<br>
			@endif
			<div class="form_group painter_images_group" style="margin-top:20px; width:100%; text-align: center;">
			@if((isset($painter_images[0]) && $painter_images[0]) || (isset($painter_images[1]) && $painter_images[1]))
				@php
					$i = -1;
				@endphp
				
				@foreach($painter_images as $painter_img)
					@if($painter_img)
						@php
							$ext = pathinfo($painter_img, PATHINFO_EXTENSION);
							$imgExists = order_image_exists($painter_img);
							$imgSrc = order_image_url($painter_img);
						@endphp
						<div class="painter_img" style="text-align: center;">
							<a target="_blank" href="{{ $imgSrc }}">
								@if(!$imgExists)
								<img style="max-width:100%;max-height:100px;" src="{{ order_image_placeholder() }}" alt="">
								@elseif($ext == 'psd')
								<img style="max-width:100%;max-height:100px;" src="/img/psd.svg" alt="">
								@elseif ($ext == 'pdf')
								<img style="max-width:100%;max-height:100px;" src="/img/pdf.svg" alt="">
								@else
								<img style="max-width:100%;max-height:100px;" src="{{ $imgSrc }}" alt="">
								@endif
							</a>
							@php
								$i++;
							@endphp
							@if(isset($order->painter_images_status['id']) && ($order->painter_images_status['id'] != 3 || $order->painter_images_status['id'] != 4))
								<a href="{{ route('remove_painter_image', ['id' => $order['id'], 'img_id' => $i]) }}" class="btn btn-danger" type="submit">x</a>
							@endif
						</div>
					@endif
				@endforeach
			@endif
			</div>
		</div>
	@endif





@if(Auth::user()->role->name == 'painter')

<div class="commentary commentary">
	<div class="row">
		<div class="col-12 col-md-6">
			<p class="painter_title">Загрузить набросок</p>
			<form action="" method="post" class="painter_form js_painter_form_images_upd_sketch" enctype="multipart/form-data">
				@csrf
				<input type="hidden" name="order_id" value="{{ $order->id }}">
				<div class="form_group">
					<input id="painter_sketch_imgs_order_{{ $order->id }}" type="file" accept=".png,.bmp,.jpg,.jpeg,.psd,.fig,.pdf,.heic,.heif" required multiple class="js_painter_sketch_images" name="painter_sketch_images[]">
				</div>
				<div class="form_group">
					<button class="painter_btn" type="submit" style="border-radius: 3px;">Отправить</button>
				</div>
				<div class="js_spinner"></div>
			</form>
		</div>

		<div class="col-12 col-md-6">
			<p class="painter_title">Загрузить рисунок</p>
			<form action="" method="post" class="painter_form js_painter_form_images_upd" enctype="multipart/form-data">
				@csrf
				<input type="hidden" name="order_id" value="{{ $order->id }}">
				<div class="form_group">
					<input id="painter_imgs_order_{{ $order->id }}" type="file" accept=".png,.bmp,.jpg,.jpeg,.psd,.fig,.pdf,.heic,.heif" required multiple class="js_painter_images" name="painter_images[]">
				</div>
				<div class="form_group">
					<button class="painter_btn" type="submit" style="border-radius: 3px;">Отправить</button>
				</div>
				<div class="js_spinner"></div>
			</form>
		</div>
	</div>
	{{--
    <form action="{{ route('update_painter_comment') }}" method="post" class="painter_form js_painter_form_with_comment" data-id="{{ $order->id }}" enctype="multipart/form-data">
        <p class="painter_title">Комментарий к заказу</p>
        @csrf
        <input type="hidden" name="order_id" value="{{  $order->id }}">
        <div class="form_group">
            <textarea required class="painter_msg" name="comment" cols="30" rows="10" placeholder="Комментарий">{{ $order->painter_comment }}</textarea>
        </div>
        <div class="form_group">
            <button class="painter_btn" type="submit" style="border-radius: 3px;">Отправить</button>
        </div>
        <div class="js_spinner"></div>
    </form>
	--}}
</div>

@endif
