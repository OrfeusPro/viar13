@if (count($breadcrumbs))
<div class="bread-crumbs">
    <i class="icon-icon3"></i>
    <ul vocab="https://schema.org/" typeof="BreadcrumbList">
        @foreach ($breadcrumbs as $breadcrumb)
        @if ($breadcrumb->url && !$loop->last)
        <li property="itemListElement" typeof="ListItem">
            <a property="item" typeof="WebPage" href="{{ $breadcrumb->url }}">
                <span property="name">{{ $breadcrumb->title }}</span></a>
            <meta property="position" content="{{ $loop->index+1 }}">
        </li>
        @else
        <li property="itemListElement" typeof="ListItem" class="breadcrumb-item active">
            <span property="name">{{ $breadcrumb->title }}</span>
            <meta property="position" content="{{ $loop->index+1 }}">
        </li>
        @endif
        @endforeach
    </ul>
</div>
@endif