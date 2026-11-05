@extends('seo-analytics.layout')

@section('seo-content')
    <h1>{{ trans('seo_analytics.terms.title') }}</h1>
    <p class="updated">{{ trans('seo_analytics.effective_date') }}</p>
    <p>{{ trans('seo_analytics.terms.intro') }}</p>

    <h2>{{ trans('seo_analytics.terms.service_title') }}</h2>
    <p>{{ trans('seo_analytics.terms.service_text') }}</p>

    <h2>{{ trans('seo_analytics.terms.eligibility_title') }}</h2>
    <p>{{ trans('seo_analytics.terms.eligibility_text') }}</p>

    <h2>{{ trans('seo_analytics.terms.acceptable_title') }}</h2>
    <p>{{ trans('seo_analytics.terms.acceptable_intro') }}</p>
    <ul>
        @foreach (trans('seo_analytics.terms.acceptable_items') as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>

    <h2>{{ trans('seo_analytics.terms.third_party_title') }}</h2>
    <p>{{ trans('seo_analytics.terms.third_party_text') }}</p>

    <h2>{{ trans('seo_analytics.terms.reports_title') }}</h2>
    <p>{{ trans('seo_analytics.terms.reports_text') }}</p>

    <h2>{{ trans('seo_analytics.terms.termination_title') }}</h2>
    <p>
        {{ trans('seo_analytics.terms.termination_before') }}
        <a href="{{ route('seo-analytics.privacy') }}">{{ trans('seo_analytics.navigation.privacy') }}</a>.
    </p>

    <h2>{{ trans('seo_analytics.terms.disclaimer_title') }}</h2>
    <p>{{ trans('seo_analytics.terms.disclaimer_text') }}</p>

    <h2>{{ trans('seo_analytics.terms.changes_title') }}</h2>
    <p>
        {{ trans('seo_analytics.terms.changes_text') }}
        <a href="mailto:viarstudia@gmail.com">viarstudia@gmail.com</a>.
    </p>
@endsection
