<div class="rp-block rp-century hidden-block">
	<div class="rp-search">
		<form class="mc-search" action="{{ route("hb.gallery.list_age") }}">
			<div class="mc-input">
				<input type="search" name="search" placeholder="@lang('gallery.search')">
			</div>
			<button type="submit">
				<svg width="25" height="25" viewBox="0 0 25 25" fill="none"
					xmlns="http://www.w3.org/2000/svg">
					<g clip-path="url(#clip0_307_1523)">
						<path
							d="M25 23.8953L16.5272 15.4225C17.911 13.7884 18.75 11.6791 18.75 9.37501C18.75 4.20531 14.5447 0 9.37501 0C4.20536 0 0 4.20531 0 9.37501C0 14.5447 4.20531 18.75 9.37501 18.75C11.6791 18.75 13.7884 17.911 15.4225 16.5272L23.8953 25L25 23.8953ZM9.37501 17.1875C5.06745 17.1875 1.56252 13.6826 1.56252 9.37501C1.56252 5.06745 5.06745 1.56252 9.37501 1.56252C13.6826 1.56252 17.1875 5.06745 17.1875 9.37501C17.1875 13.6826 13.6826 17.1875 9.37501 17.1875Z"
							fill="white"></path>
					</g>
					<defs>
						<clipPath id="clip0_307_1523">
							<rect width="25" height="25" fill="white"></rect>
						</clipPath>
					</defs>
				</svg>
			</button>
		</form>
	</div>
	<div class="rp-century-row">
		<div class="select-century">
			<div class="p-text">
				@lang("gallery.rep_century_select"):
			</div>
			<ul>
				@foreach ($gallery_age as $age)
					<li><a href="{{ route('hb.gallery.age', ["alias"=> $age->alias]) }}">{{ translated_value($age, 'name', $age->name) }}</a></li>
				@endforeach
			</ul>
		</div>
		<div class="select-color">
			<div class="p-text">
				@lang("gallery.main_color"):
			</div>
			<ul class="color-grid">
				@foreach ($colors as $color)
					@if(isset($category) && $category)
						<li style="padding-right: 0px;"><a href="{{ route('hb.gallery.category', [$page->url, $category->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3; width: 100%;"></div></a></li>
					@else
						<li style="padding-right: 0px;"><a href="{{ route('hb.gallery.module', [$page->url, 'color'=> $color->id, 'size'=> \Request::get('size') ?? "", 'order'=> \Request::get('order') ?? "" ]) }}" name="{{ $color->id }}"><div style="background: {{ $color->color }}; height: 29px; border: 1px solid #f3f3f3:width: 100%;"></div></a></li>
					@endif
				@endforeach
			</ul>
		</div>
		<div class="rp-century-col">
			<ul class="rp-p-about">
				@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_about')
			</ul>
			<div class="rp-form">
				<div class="p-text">
					@lang("gallery.forma"):
				</div>
				<ul>
					<li>
						@if(isset($category) && $category)
							<a href="{{ route('hb.gallery.category', [$page->url, $category->url, 
							'color'=> \Request::get('color') ?? "", 
							'size'=> \Request::get('size') ?? "", 
							'order'=> \Request::get('order') ?? "", 
							'shape'=> "landscape" ]) }}">
						@else
							<a href="{{ route('hb.gallery.module', [$page->url, 
							'color'=> \Request::get('color') ?? "", 
							'size'=> \Request::get('size') ?? "", 
							'order'=> \Request::get('order') ?? "", 
							'shape'=> "landscape" ]) }}">
						@endif
							<img width="88" height="55" src="{{ asset(env('THEME') . 'images') }}/reproduction/1a.svg"
								alt="">
						</a>
					</li>
					<li>
						@if(isset($category) && $category)
							<a href="{{ route('hb.gallery.category', [$page->url, $category->url, 
							'color'=> \Request::get('color') ?? "", 
							'size'=> \Request::get('size') ?? "", 
							'order'=> \Request::get('order') ?? "", 
							'shape'=> "square" ]) }}">
						@else
							<a href="{{ route('hb.gallery.module', [$page->url, 
							'color'=> \Request::get('color') ?? "", 
							'size'=> \Request::get('size') ?? "", 
							'order'=> \Request::get('order') ?? "", 
							'shape'=> "square" ]) }}">
						@endif
							<img width="60" height="60" src="{{ asset(env('THEME') . 'images') }}/reproduction/3a.svg" alt="">
						</a>
					</li>
					<li>
						@if(isset($category) && $category)
							<a href="{{ route('hb.gallery.category', [$page->url, $category->url, 
							'color'=> \Request::get('color') ?? "", 
							'size'=> \Request::get('size') ?? "", 
							'order'=> \Request::get('order') ?? "", 
							'shape'=> "portrait" ]) }}">
						@else
							<a href="{{ route('hb.gallery.module', [$page->url, 
							'color'=> \Request::get('color') ?? "", 
							'size'=> \Request::get('size') ?? "", 
							'shape'=> \Request::get('order') ?? "", 
							'shape'=> "portrait" ]) }}">
						@endif
							<img width="55" height="81" src="{{ asset(env('THEME') . 'images') }}/reproduction/5a.svg" alt="">
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
