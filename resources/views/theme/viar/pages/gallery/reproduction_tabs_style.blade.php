<div class="rp-block rp-nation rp-style hidden-block">
	<div class="rp-p-row">
		<div class="rp-search">
			<form class="mc-search" action="{{ route("hb.gallery.list_styles") }}">
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
		<ul class="rp-p-about">
			@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_about')
		</ul>
	</div>
	<div class="rp-nations">
		<div class="p-text">
			@lang("gallery.select_style"):
		</div>
		<div class="rp-results">
			<ul>
				@foreach ($gallery_style as $style)
					<li><a href="{{ route('hb.gallery.style', ["alias"=> $style->alias]) }}">{{ translated_value($style, 'name', $style->name) }}</a></li>
				@endforeach
			</ul>
		</div>
	</div>
	<ul class="rp-p-about rp-p-tb">
		@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_about')
	</ul>
</div>
