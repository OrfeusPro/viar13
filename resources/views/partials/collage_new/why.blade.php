<section class="lozad portait-why">
	<div class="section-frame">
		<div class="top-title">
			<div class="page-title h2_old">{{ trans('portrait.des_help_title') }}</h2>
			<p>{{ trans('portrait.des_help_desc') }}</p>
		</div>
		<div class="why-list">
			<div class="why-box">
				<div class="why-item">
					<p>
						{!! trans('portrait.des_help_step1') !!}
					</p>
				</div>
				<div class="why-item">
					<p>
						{!! trans('portrait.des_help_step2') !!}
					</p>
				</div>
			</div>
			<div class="why-box">
				<div class="why-item">
					<p>
						{!! trans('portrait.des_help_step3') !!}
					</p>
				</div>
				<div class="why-item">
					<p>
						{!! trans('portrait.des_help_step4') !!}
					</p>
				</div>
			</div>
		</div>

		@include('partials.collage_new.why_form')
	</div>
</section>
