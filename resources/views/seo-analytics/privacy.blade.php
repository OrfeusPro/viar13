@extends('seo-analytics.layout')

@section('seo-content')
    <h1>{{ trans('seo_analytics.privacy.title') }}</h1>
    <p class="updated">{{ trans('seo_analytics.effective_date') }}</p>
    <p>{{ trans('seo_analytics.privacy.intro') }}</p>

    <h2>{{ trans('seo_analytics.privacy.summary_title') }}</h2>
    <dl class="seo-analytics-page__data-summary">
        <dt>{{ trans('seo_analytics.privacy.summary_access_label') }}</dt>
        <dd>{{ trans('seo_analytics.privacy.summary_access') }}</dd>
        <dt>{{ trans('seo_analytics.privacy.summary_use_label') }}</dt>
        <dd>{{ trans('seo_analytics.privacy.summary_use') }}</dd>
        <dt>{{ trans('seo_analytics.privacy.summary_storage_label') }}</dt>
        <dd>{{ trans('seo_analytics.privacy.summary_storage') }}</dd>
        <dt>{{ trans('seo_analytics.privacy.summary_sharing_label') }}</dt>
        <dd>{{ trans('seo_analytics.privacy.summary_sharing') }}</dd>
    </dl>

    <h2>{{ trans('seo_analytics.privacy.collect_title') }}</h2>
    <p>{{ trans('seo_analytics.privacy.collect_intro') }}</p>
    <ul>
        @foreach (trans('seo_analytics.privacy.collect_items') as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
    <p>{{ trans('seo_analytics.privacy.no_password') }}</p>

    <h2>{{ trans('seo_analytics.privacy.use_title') }}</h2>
    <p>{{ trans('seo_analytics.privacy.use_intro') }}</p>
    <ul>
        @foreach (trans('seo_analytics.privacy.use_items') as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
    <p>
        {{ trans('seo_analytics.privacy.google_policy_before') }}
        <a href="https://developers.google.com/terms/api-services-user-data-policy" rel="noopener noreferrer">Google API Services User Data Policy</a>,
        {{ trans('seo_analytics.privacy.google_policy_after') }}
    </p>
    <p>{{ trans('seo_analytics.privacy.prohibited_use_text') }}</p>

    <h2>{{ trans('seo_analytics.privacy.sharing_title') }}</h2>
    <p>{{ trans('seo_analytics.privacy.sharing_text') }}</p>

    <h2>{{ trans('seo_analytics.privacy.storage_title') }}</h2>
    <p>{{ trans('seo_analytics.privacy.storage_text') }}</p>
    <p>{{ trans('seo_analytics.privacy.security_text') }}</p>
    <p>{{ trans('seo_analytics.privacy.human_access_text') }}</p>

    <h2>{{ trans('seo_analytics.privacy.choices_title') }}</h2>
    <p>
        {{ trans('seo_analytics.privacy.revoke_before') }}
        <a href="https://myaccount.google.com/connections" rel="noopener noreferrer">Google Account connections</a>.
        {{ trans('seo_analytics.privacy.revoke_after') }}
    </p>
    <p>
        {{ trans('seo_analytics.privacy.deletion_before') }}
        <a href="mailto:viarstudia@gmail.com">viarstudia@gmail.com</a> {{ trans('seo_analytics.privacy.deletion_email_suffix') }}
        {{ trans('seo_analytics.privacy.deletion_after') }}
    </p>

    <h2>{{ trans('seo_analytics.privacy.children_title') }}</h2>
    <p>{{ trans('seo_analytics.privacy.children_text') }}</p>

    <h2>{{ trans('seo_analytics.privacy.changes_title') }}</h2>
    <p>
        {{ trans('seo_analytics.privacy.changes_text') }}
        <a href="mailto:viarstudia@gmail.com">viarstudia@gmail.com</a>.
    </p>

    <h2>{{ trans('seo_analytics.privacy.controller_title') }}</h2>
    <p>{{ trans('seo_analytics.privacy.controller_text') }}</p>
@endsection
