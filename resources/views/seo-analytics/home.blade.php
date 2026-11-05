@extends('seo-analytics.layout')

@section('seo-content')
    <h1>ViarCanvas SEO Analytics</h1>
    <p class="lead">{{ trans('seo_analytics.home.lead') }}</p>

    <h2>{{ trans('seo_analytics.home.what_title') }}</h2>
    <p>{{ trans('seo_analytics.home.what_text') }}</p>
    <p>{{ trans('seo_analytics.home.access_text') }}</p>

    <h2>{{ trans('seo_analytics.home.data_title') }}</h2>
    <p>{{ trans('seo_analytics.home.data_intro') }}</p>
    <ul>
        @foreach (trans('seo_analytics.home.data_items') as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
    <p>{{ trans('seo_analytics.home.data_limits') }}</p>

    <h2>{{ trans('seo_analytics.home.how_title') }}</h2>
    <ol>
        @foreach (trans('seo_analytics.home.steps') as $step)
            <li>{{ $step }}</li>
        @endforeach
    </ol>

    <h2>{{ trans('seo_analytics.home.support_title') }}</h2>
    <p>
        {{ trans('seo_analytics.home.support_intro') }}
        <a href="{{ route('seo-analytics.privacy') }}">{{ trans('seo_analytics.navigation.privacy') }}</a>
        {{ trans('seo_analytics.home.support_and') }}
        <a href="{{ route('seo-analytics.terms') }}">{{ trans('seo_analytics.navigation.terms') }}</a>.
        {{ trans('seo_analytics.home.support_contact') }}
        <a href="mailto:viarstudia@gmail.com">viarstudia@gmail.com</a>.
    </p>
@endsection
