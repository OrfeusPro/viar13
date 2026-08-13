@extends(config('theme.resource').'.layouts.app')

@section('content')
	@if (isset($content))
		{!! $content !!}
	@endif
@endsection

@section('footer_scripts')
	@if (isset($footer_scripts))
		{!! $footer_scripts !!}
	@endif
@endsection

@section('modals')
	@if (isset($modals))
		{!! $modals !!}
	@endif
@endsection
