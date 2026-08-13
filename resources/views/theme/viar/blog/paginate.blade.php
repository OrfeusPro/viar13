@if ($paginator->hasPages())
	<div class="b-nav-inner bf-nav">

		@if ($paginator->onFirstPage())
			<a href="" class="b-nav-l disabled">
				<svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M11 0.999999L2 9.5L11 18" stroke="#C4C4C4" stroke-width="2" />
				</svg>
			</a>
		@else
			<a href="{{ $paginator->previousPageUrl() }}" class="b-nav-l">
				<svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M11 0.999999L2 9.5L11 18" stroke="#FA7846" stroke-width="2" />
				</svg>
			</a>
		@endif

		<ul>
			@foreach ($elements as $element)
				@if (is_string($element))
					<li class="active">
						<a href="#">{{ $element }}</a>
					</li>
				@endif

				@if (is_array($element))
					@foreach ($element as $page => $url)
						@if ($page == $paginator->currentPage())
							<li class="active"><a href="#">{{ $page }}</a></li>
						@else
							<li><a href="{{ $url }}">{{ $page }}</a></li>
						@endif
					@endforeach
				@endif
			@endforeach
		</ul>

		@if ($paginator->hasMorePages())
			<a href="{{ $paginator->nextPageUrl() }}" class="b-nav-r bf-nav">
				<svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M0.999999 0.999999L10 9.5L1 18" stroke="#FA7846" stroke-width="2" />
				</svg>
			</a>
		@else
			<a class="b-nav-r bf-nav">
				<svg width="12" height="19" viewBox="0 0 12 19" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M0.999999 0.999999L10 9.5L1 18" stroke="#C4C4C4" stroke-width="2" />
				</svg>
			</a>
		@endif
	</div>
@endif
