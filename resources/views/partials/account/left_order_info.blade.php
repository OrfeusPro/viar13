@if(Auth::user()->role->name == 'user' or Auth::user()->role->name == 'admin')
@if($order->is_show_painter_images == 1)
<form action="{{ route('send_client_painter_comments') }}" method="POST" class="f__user_order js_send_user_add_images">
    @csrf
    <input type="hidden" name="order_id" value="{{ $order->id }}">

    <p class="painter_title">Картины художника:</p>
    <div class="commentary commentary_painter js_hb_painter_{{$order->id}}">
        <ul class="user__painter__imgs__list">
            @php
            $pimg = preg_replace("/\s+/", "", $order->painter_images);
            $painter_images = trim($pimg, ',');
            $painter_images = explode(',',$painter_images);
            @endphp
            @foreach($painter_images as $painter_img)
            @php
            $ext = pathinfo($painter_img, PATHINFO_EXTENSION);
            $imgExists = order_image_exists($painter_img);
            $imgSrc = order_image_url($painter_img);
            @endphp
            @if($painter_img == '' or $painter_img == null) @continue
            @endif
            <li>
                <a href="{{ $imgSrc }}" target="_blank" data-src="{{ $painter_img }}">
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
            </li>
            @endforeach
        </ul>
    </div>
	
		@php
			$pimg = preg_replace("/\s+/", "", $order->painter_sketch_images);
			$painter_sketch_images = trim($pimg, ',');
			$painter_sketch_images = explode(',',$painter_sketch_images);
		@endphp
		
		<div class="commentary commentary_sketch_painter js_hb_sketch_painter_{{$order->id}}">
		@if((isset($painter_sketch_images[0]) && $painter_sketch_images[0]) || (isset($painter_sketch_images[1]) && $painter_sketch_images[1]))
				<p class="painter_title" style="margin-bottom:0px;">Набросок художника:</p>
				<div class="form_group painter_images_group" style="margin-top:20px;">
					
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
				</div>
		@endif
		</div>
	

    <div class="commentary commentary_painter">
        <p>Ваши комментарии:</p>
        @php
        $user_comments = \App\Models\User::get_user_acc_comments($order->id);
        @endphp

        <ul class="user__comments__list js_order_user_comments">
            @if($user_comments)
            @foreach($user_comments as $u_comment)
            <li>
                <span>{{ date( 'd.m.Y', strtotime($u_comment->created_at)) }} - </span>{{ $u_comment->comment }}
            </li>
            @endforeach
            @endif
        </ul>

        <p>Отправить комментарий</p>
        <div class="form-group">
            <textarea placeholder="Комментарий" name="user_comment"
                class="js_u_comm_textarea">{{  $order->client_comment }}</textarea>
        </div>

    </div>

    <div class="commentary commentary_painter">
        <p>Добавить ваши файлы:</p>
        <div class="form_group">
            <input type="file" accept=".png,.bmp,.jpg,.jpeg,.psd,.fig,.pdf,.heic,.heif" multiple class="js_painter_images"
                name="user_images[]" id="js_painter_images_{{  $order->id }}">
        </div>
        <button class="painter_btn" type="submit">Отправить</button>
        <div class="js_spinner"></div>
    </div>
</form>
@endif
@endif

@php
$client_imgs = $order->client_images;
$client_imgs = rtrim($client_imgs, ',');
$client_imgs_items = explode (",", $client_imgs);
@endphp

<div class="commentary commentary_painter js_user_all_images">
    @if(is_array($client_imgs_items) && !empty($client_imgs_items) && $client_imgs_items[0] != "")
    <p class="load__title">Загрузки клиента:</p>
    <ul class="user__painter__imgs__list">
        @foreach($client_imgs_items as $img_item)
        @php
        $imgSrc = order_image_url($img_item);
        @endphp
        <li>
            <a href="{{ $imgSrc }}" target="_blank">
                <img src="{{ $imgSrc }}" alt="" class="img__user_upl">
            </a>
        </li>
        @endforeach
    </ul>
    @else
    <ul class="user__painter__imgs__list">
    </ul>
    @endif
</div>
