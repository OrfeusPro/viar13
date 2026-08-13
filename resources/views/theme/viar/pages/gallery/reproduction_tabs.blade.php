<div class="rp-container">
	<div class="section-frame">
		<div class="rp-tabs">
			<div class="rp-tabs__inner">
				<div class="rp-tab active">
					@lang("gallery.rep_painter")
				</div>
				<div class="rp-tab">
					@lang("gallery.rep_century")
				</div>
				<div class="rp-tab">
					@lang("gallery.rep_nationality")
				</div>
				<div class="rp-tab">
					@lang("gallery.rep_style")
				</div>
				<div class="rp-tab">
					@lang("gallery.rep_genre")
				</div>
			</div>
		</div>
	</div>
	<div class="section-frame">
		<div class="rp-content">
			<div class="rp-blocks">

				@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_tabs_painters', ['active'=> true])
				@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_tabs_century', ['active'=> true])
				@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_tabs_nation', ['active'=> true])
				@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_tabs_style', ['active'=> true])
				@include((config('theme.resource') ?: 'theme.viar.') . 'pages.gallery.reproduction_tabs_genre', ['active'=> true])
				
			</div>
		</div>
	</div>
</div>