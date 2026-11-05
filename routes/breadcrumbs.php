<?php

Breadcrumbs::for('page', function ($breadcrumbs) {
    $breadcrumbs->push(__('breadcrumbs.home'), url('/'));
});

Breadcrumbs::for('basket', function ($breadcrumbs) {
    $breadcrumbs->parent('page');
    $breadcrumbs->push(__('breadcrumbs.cart'), url('basket'));
});

Breadcrumbs::for('thanks', function ($breadcrumbs) {
    $breadcrumbs->parent('page');
    $breadcrumbs->push(__('breadcrumbs.thanks'), url('/basket/thanks'));
});

Breadcrumbs::for('gallery_add', function ($breadcrumbs) {
    $breadcrumbs->parent('page');
    $breadcrumbs->push(__('breadcrumbs.gallery'), url('/gallery'));
});

Breadcrumbs::for('gallery_category_add', function ($breadcrumbs, $data) {
    $breadcrumbs->parent('gallery_add');
    $breadcrumbs->push($data->name, url('/gallery/' . $data->url));
});

Breadcrumbs::for('gallery_item_add', function ($breadcrumbs, $page, $item) {
    $breadcrumbs->parent('gallery_category_add', $page);
    $breadcrumbs->push($item->name, url('/gallery/' . $item->id));
});

Breadcrumbs::for('pages_add', function ($breadcrumbs, $data) {
    $breadcrumbs->parent('page');
    $breadcrumbs->push($data->title, url('/page' . $data->url));
});

Breadcrumbs::for('about', function ($breadcrumbs, $data) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($data->title, url('/page' . $data->url));
});
