<?php

Breadcrumbs::register('page', function ($breadcrumbs) {
    $breadcrumbs->push(__('breadcrumbs.home'), url('/'));
});

Breadcrumbs::register('basket', function ($breadcrumbs) {
    $breadcrumbs->parent('page');
    $breadcrumbs->push(__('breadcrumbs.cart'), url('basket'));
});

Breadcrumbs::register('thanks', function ($breadcrumbs) {
    $breadcrumbs->parent('page');
    $breadcrumbs->push(__('breadcrumbs.thanks'), url('/basket/thanks'));
});

Breadcrumbs::register('gallery_add', function ($breadcrumbs) {
    $breadcrumbs->parent('page');
    $breadcrumbs->push(__('breadcrumbs.gallery'), url('/gallery'));
});

Breadcrumbs::register('gallery_category_add', function ($breadcrumbs, $data) {
    $breadcrumbs->parent('gallery_add');
    $breadcrumbs->push($data->name, url('/gallery/' . $data->url));
});

Breadcrumbs::register('gallery_item_add', function ($breadcrumbs, $page, $item) {
    $breadcrumbs->parent('gallery_category_add', $page);
    $breadcrumbs->push($item->name, url('/gallery/' . $item->id));
});

Breadcrumbs::register('pages_add', function ($breadcrumbs, $data) {
    $breadcrumbs->parent('page');
    $breadcrumbs->push($data->title, url('/page' . $data->url));
});

Breadcrumbs::register('about', function ($breadcrumbs, $data) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($data->title, url('/page' . $data->url));
});
