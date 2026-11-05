<?php

// Explicit frontend image inventory. Dynamic sources are declarative model bindings.
return [
    'about.blade.php' => [
        'images' => [
            ['line' => 70, 'alt' => '', 'title' => '', 'translations' => ['account.index1', 'gl.choose_pic'], 'path' => static function () { return (asset('images/about-img2.png')); }],
            ['line' => 73, 'alt' => '', 'title' => '', 'translations' => ['gl.choose_pic'], 'path' => static function () { return (asset('images/about-link.png')); }],
            ['line' => 77, 'alt' => '', 'title' => '', 'translations' => ['gl.choose_pic'], 'path' => static function () { return (asset('images/logo-about.png')); }],
            ['line' => 79, 'alt' => '', 'title' => '', 'translations' => ['gl.choose_pic'], 'path' => static function () { return (asset('images/about-link.png')); }],
        ],
    ],
    'account.blade.php' => [
        'images' => [
            ['line' => 101, 'alt' => '', 'title' => '', 'translations' => ['account.index8', 'account.index9', 'account.index4', 'account.index10', 'account.index5', 'account.index11'], 'path' => static function () { return (asset('img/filter-img1.png')); }],
            ['line' => 106, 'alt' => '', 'title' => '', 'translations' => ['account.index9', 'account.index4', 'account.index10', 'account.index5', 'account.index11', 'account.index6'], 'path' => static function () { return (asset('img/filter-img2.png')); }],
            ['line' => 112, 'alt' => '', 'title' => '', 'translations' => ['account.index4', 'account.index10', 'account.index5', 'account.index11', 'account.index6', 'account.index12'], 'path' => static function () { return (asset('img/filter-img3.png')); }],
            ['line' => 297, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['client_images', 'painter_images', 'painter_sketch_images'], 'csv' => true, 'order_image' => true]],
            ['line' => 371, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['client_images', 'painter_images', 'painter_sketch_images'], 'csv' => true, 'order_image' => true]],
            ['line' => 441, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['client_images', 'painter_images', 'painter_sketch_images'], 'csv' => true, 'order_image' => true]],
        ],
    ],
    'all_big.blade.php' => [
        'images' => [
            ['line' => 10, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]],
        ],
    ],
    'all_styles.blade.php' => [
        'images' => [
            ['line' => 153, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.header.top_sale', 'account.index1'], 'path' => static function () { return (asset(env('THEME') . 'images/sale.webp')); }],
            ['line' => 167, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.header.top_sale', 'account.index1'], 'path' => static function () { return (asset('img/icons/angle-arrow.svg')); }],
            ['line' => 222, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/error.svg')); }],
            ['line' => 132, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/head-bg.svg'; }],
        ],
    ],
    'canvas_new.blade.php' => [
        'images' => [
            ['line' => 100, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
            ['line' => 109, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
            ['line' => 114, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
            ['line' => 142, 'alt' => '', 'title' => '', 'translations' => ['gl.suc'], 'path' => static function () { return (asset(env('THEME').'img/checkmark_circle.1.png')); }],
            ['line' => 159, 'alt' => '', 'title' => '', 'translations' => ['gl.inv_filesize_or_ext'], 'path' => static function () { return (asset(env('THEME').'img/cross1.png')); }],
        ],
    ],
    'collage.blade.php' => [
        'images' => [
            ['line' => 87, 'alt' => 'loading', 'title' => '', 'path' => static function () { return (asset('img/loading.gif')); }],
            ['line' => 174, 'alt' => '', 'title' => '', 'translations' => ['gl.suc', 'gl.inv_filesize_or_ext'], 'path' => static function () { return (asset('img/checkmark_circle.1.png')); }],
            ['line' => 184, 'alt' => '', 'title' => '', 'translations' => ['gl.suc', 'gl.inv_filesize_or_ext', 'portrait_buy_form.popup_thanks_text1'], 'path' => static function () { return (asset(env('THEME').'img/cross1.png')); }],
            ['line' => 195, 'alt' => 'img', 'title' => '', 'translations' => ['gl.inv_filesize_or_ext', 'portrait_buy_form.popup_thanks_text1', 'portrait_buy_form.popup_thanks_text2', 'portrait_buy_form.popup_thanks_text3', 'portrait_buy_form.popup_thanks_text4'], 'path' => static function () { return (asset('images/icon/check-done.svg')); }],
        ],
    ],
    'delivery.blade.php' => [
        'images' => [
            ['line' => 193, 'alt' => '', 'title' => '', 'translations' => ['pages.delivery_second_block_box1'], 'path' => static function () { return '/images/icon/check-circle.svg'; }],
            ['line' => 214, 'alt' => '', 'title' => '', 'translations' => ['pages.delivery_second_block_box2'], 'path' => static function () { return '/images/icon/check-circle.svg'; }],
            ['line' => 236, 'alt' => '', 'title' => '', 'translations' => ['pages.delivery_second_block_box3', 'pages.delivery_second_title', 'pages.delivery_second_text'], 'path' => static function () { return '/images/icon/check-circle.svg'; }],
            ['line' => 333, 'alt' => '', 'title' => '', 'path' => static function () { return image_original_url(env('THEME') . 'images/contacts/d1.png'); }],
            ['line' => 347, 'alt' => '', 'title' => '', 'path' => static function () { return image_original_url(env('THEME') . 'images/contacts/d2.png'); }],
            ['line' => 361, 'alt' => '', 'title' => '', 'translations' => ['pages.delivery_del_block_info'], 'path' => static function () { return image_original_url(env('THEME') . 'images/contacts/d3.png'); }],
            ['line' => 374, 'alt' => '', 'title' => '', 'translations' => ['pages.delivery_del_block_info', 'pages.delivery_del_block_contact'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/contacts/whatsapp.png'; }],
            ['line' => 422, 'alt' => '', 'title' => '', 'translations' => ['pages.delivery_third_block_box1'], 'path' => static function () { return '/images/icon/check-circle.svg'; }],
            ['line' => 443, 'alt' => '', 'title' => '', 'translations' => ['pages.delivery_third_block_box2'], 'path' => static function () { return '/images/icon/check-circle.svg'; }],
            ['line' => 464, 'alt' => '', 'title' => '', 'translations' => ['pages.delivery_third_block_box3', 'pages.delivery_third_block_text'], 'path' => static function () { return '/images/icon/check-circle.svg'; }],
        ],
    ],
    'errors/404.blade.php' => [
        'images' => [
            ['line' => 19, 'alt' => 'ViarCanvas', 'title' => '', 'translations' => ['gl.err_title', 'gl.err_link_text'], 'path' => static function () { return 'https://viarcanvas.com/img/banner-img.png'; }],
        ],
    ],
    'faq.blade.php' => [
        'images' => [
            ['line' => 38, 'alt' => '', 'title' => '', 'translations' => ['account.index1'], 'path' => static function () { return (asset('img/faq-img1.png')); }],
            ['line' => 52, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/faq-img2.png')); }],
        ],
    ],
    'gallery.blade.php' => [
        'images' => [
            ['line' => 19, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/gallery-banner-img.png')); }],
            ['line' => 29, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/gallery-icon1.png')); }],
            ['line' => 32, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/gallery-icon2.png')); }],
            ['line' => 34, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/gallery-icon3.png')); }],
            ['line' => 50, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/moudlar_v2.png')); }],
            ['line' => 72, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/photo-img.png')); }],
            ['line' => 94, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/reproductions-img.png')); }],
        ],
    ],
    'gallery_block_category.blade.php' => [
        'images' => [
            ['line' => 62, 'alt' => '', 'title' => '', 'binding' => ['sources' => [['model' => 'App\\Models\\SliderMod', 'fields' => ['image'], 'media' => []], ['model' => 'App\\Models\\SliderPhoto', 'fields' => ['image'], 'media' => []], ['model' => 'App\\Models\\SliderRepr', 'fields' => ['image'], 'media' => []]]]],
            ['line' => 81, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/foto-img1.png')); }],
            ['line' => 82, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/foto-img2.png')); }],
            ['line' => 117, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]],
            ['line' => 158, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]],
            ['line' => 194, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]],
            ['line' => 79, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/foto-slider-bg.png')); }],
        ],
    ],
    'gift_card.blade.php' => [
        'images' => [
            ['line' => 62, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/front_') . app()->getLocale() . '.png'); }],
            ['line' => 65, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/back_') . app()->getLocale() . '.png'); }],
            ['line' => 147, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/benefits-img1.png')); }],
            ['line' => 153, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/benefits-img2.png')); }],
            ['line' => 159, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/benefits-img3.png')); }],
            ['line' => 165, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/benefits-img4.png')); }],
            ['line' => 171, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/benefits-img5.png')); }],
            ['line' => 177, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/benefits-img6.png')); }],
        ],
    ],
    'gift_card/gift_card.blade.php' => [
        'images' => [
            ['line' => 83, 'alt' => 'img', 'title' => '', 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg'; }],
            ['line' => 266, 'alt' => 'img', 'title' => '', 'translations' => ['gift_card.rules'], 'path' => static function () { return './images/icon/ellipse-black.svg'; }],
            ['line' => 283, 'alt' => 'Viar', 'title' => '', 'translations' => ['gift_card.vozvrat', 'gift_card.card_payment', 'gift_card.gift_cart_friends'], 'path' => static function () { return (asset(env('THEME') . 'images/gift')) . '/gal.svg'; }],
            ['line' => 287, 'alt' => 'Viar', 'title' => '', 'translations' => ['gift_card.vozvrat', 'gift_card.card_payment', 'gift_card.gift_cart_friends', 'gift_card.sertificate'], 'path' => static function () { return (asset(env('THEME') . 'images/gift')) . '/gal.svg'; }],
            ['line' => 291, 'alt' => 'Viar', 'title' => '', 'translations' => ['gift_card.vozvrat', 'gift_card.card_payment', 'gift_card.gift_cart_friends', 'gift_card.sertificate', 'gift_card.code_sertificate'], 'path' => static function () { return (asset(env('THEME') . 'images/gift')) . '/gal.svg'; }],
            ['line' => 295, 'alt' => 'Viar', 'title' => '', 'translations' => ['gift_card.card_payment', 'gift_card.gift_cart_friends', 'gift_card.sertificate', 'gift_card.code_sertificate', 'gift_card.card_value'], 'path' => static function () { return (asset(env('THEME') . 'images/gift')) . '/gal.svg'; }],
            ['line' => 299, 'alt' => 'Viar', 'title' => '', 'translations' => ['gift_card.gift_cart_friends', 'gift_card.sertificate', 'gift_card.code_sertificate', 'gift_card.card_value'], 'path' => static function () { return (asset(env('THEME') . 'images/gift')) . '/gal.svg'; }],
            ['line' => 303, 'alt' => 'Viar', 'title' => '', 'translations' => ['gift_card.sertificate', 'gift_card.code_sertificate', 'gift_card.card_value'], 'path' => static function () { return (asset(env('THEME') . 'images/gift')) . '/gal.svg'; }],
        ],
    ],
    'graph_portrait.blade.php' => [
        'images' => [
            ['line' => 29, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/compositions-form-img.png')); }],
            ['line' => 57, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-img.png')); }],
            ['line' => 61, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-content-img.png')); }],
            ['line' => 64, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon1.png')); }],
            ['line' => 68, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon2.png')); }],
            ['line' => 72, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon3.png')); }],
            ['line' => 76, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon4.png')); }],
            ['line' => 80, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon5.png')); }],
            ['line' => 84, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon6.png')); }],
            ['line' => 88, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon7.png')); }],
            ['line' => 92, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon8.png')); }],
            ['line' => 108, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/packaging-img.png')); }],
        ],
    ],
    'graphic-portrait.blade.php' => [
        'images' => [
            ['line' => 75, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/front_') . app()->getLocale() . '.png'); }],
            ['line' => 78, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/back_') . app()->getLocale() . '.png'); }],
            ['line' => 18, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/graphic-portrait-bg.png')); }],
        ],
    ],
    'inner_blog.blade.php' => [
        'images' => [
            ['line' => 53, 'alt' => '', 'title' => '', 'translations' => ['gl.share_text'], 'path' => static function () { return (asset('img/social-blog1.png')); }],
        ],
    ],
    'layots/head.blade.php' => [
        'images' => [
            ['line' => 246, 'alt' => '', 'title' => '', 'translations' => ['gl.suc_send', 'gl.suc_send_dates', 'gl.suc', 'gl.suc_send_fb_sale', 'gl.req_field', 'gl.err_price'], 'path' => static function () { return (asset(env('THEME').'images/sale.webp')); }],
            ['line' => 283, 'alt' => 'loading', 'title' => '', 'translations' => ['header.account_form_button', 'header.enter', 'header.registration'], 'path' => static function () { return (asset('img/loading.gif')); }],
        ],
    ],
    'oil-portrait.blade.php' => [
        'images' => [
            ['line' => 88, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon.png')); }],
            ['line' => 90, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon2.png')); }],
            ['line' => 92, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon3.png')); }],
            ['line' => 94, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon4.png')); }],
            ['line' => 125, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitsHard', 'fields' => ['img1'], 'media' => []]],
            ['line' => 128, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitsHard', 'fields' => ['img2'], 'media' => []]],
            ['line' => 147, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortPrevItem', 'fields' => ['image'], 'media' => []]],
            ['line' => 171, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/complexity-img.png')); }],
            ['line' => 45, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/oil-portrait-bg.png')); }],
        ],
    ],
    'page.blade.php' => [
        'images' => [
            ['line' => 60, 'alt' => '', 'title' => '', 'translations' => ['gl.choose_pic'], 'path' => static function () { return (asset('images/about-img2.png')); }],
            ['line' => 62, 'alt' => '', 'title' => '', 'translations' => ['gl.choose_pic'], 'path' => static function () { return (asset('images/about-link.png')); }],
            ['line' => 66, 'alt' => '', 'title' => '', 'translations' => ['gl.choose_pic'], 'path' => static function () { return (asset('images/logo-about.png')); }],
            ['line' => 68, 'alt' => '', 'title' => '', 'translations' => ['gl.choose_pic'], 'path' => static function () { return (asset('images/about-link.png')); }],
        ],
    ],
    'pages/_partials/_includes/_calc/_effects.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/effects-item1.png')); }],
            ['line' => 11, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/effects-item2.png')); }],
            ['line' => 18, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/effects-item3.png')); }],
        ],
    ],
    'pages/_partials/_includes/_calc/_executions.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => '', 'title' => '', 'translations' => ['modular_pictures.index46', 'modular_pictures.index45'], 'path' => static function () { return (asset('img/execution-item1.png')); }],
            ['line' => 11, 'alt' => '', 'title' => '', 'translations' => ['modular_pictures.index46', 'modular_pictures.index45'], 'path' => static function () { return (asset('img/execution-item2.png')); }],
        ],
    ],
    'pages/_partials/_includes/_calc/_primitive_forms.blade.php' => [
        'images' => [
            ['line' => 2, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shapes-img1.png')); }],
            ['line' => 4, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shapes-img2.png')); }],
            ['line' => 6, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shapes-img3.png')); }],
        ],
    ],
    'partials/account/left_order_info.blade.php' => [
        'images' => [
            ['line' => 26, 'alt' => '', 'title' => '', 'path' => static function () { return (order_image_placeholder()); }],
            ['line' => 28, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/psd.svg'; }],
            ['line' => 30, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/pdf.svg'; }],
            ['line' => 32, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['painter_images'], 'csv' => true, 'order_image' => true, 'exclude_extensions' => ['pdf', 'psd']]],
            ['line' => 60, 'alt' => '', 'title' => '', 'path' => static function () { return (order_image_placeholder()); }],
            ['line' => 62, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/psd.svg'; }],
            ['line' => 64, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/pdf.svg'; }],
            ['line' => 66, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['painter_sketch_images'], 'csv' => true, 'order_image' => true, 'exclude_extensions' => ['pdf', 'psd']]],
            ['line' => 129, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['client_images'], 'csv' => true, 'order_image' => true]],
        ],
    ],
    'partials/account/painter_images.blade.php' => [
        'images' => [
            ['line' => 24, 'alt' => '', 'title' => '', 'path' => static function () { return (order_image_placeholder()); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'translations' => ['account.painter_images'], 'path' => static function () { return '/img/psd.svg'; }],
            ['line' => 28, 'alt' => '', 'title' => '', 'translations' => ['account.painter_images'], 'path' => static function () { return '/img/pdf.svg'; }],
            ['line' => 30, 'alt' => '', 'title' => '', 'translations' => ['account.painter_images'], 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['painter_sketch_images'], 'csv' => true, 'order_image' => true, 'exclude_extensions' => ['pdf', 'psd']]],
            ['line' => 60, 'alt' => '', 'title' => '', 'translations' => ['account.painter_images'], 'path' => static function () { return (order_image_placeholder()); }],
            ['line' => 62, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/psd.svg'; }],
            ['line' => 64, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/pdf.svg'; }],
            ['line' => 66, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['painter_images'], 'csv' => true, 'order_image' => true, 'exclude_extensions' => ['pdf', 'psd']]],
            ['line' => 106, 'alt' => '', 'title' => '', 'path' => static function () { return (order_image_placeholder()); }],
            ['line' => 108, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/psd.svg'; }],
            ['line' => 110, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/pdf.svg'; }],
            ['line' => 112, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['painter_sketch_images'], 'csv' => true, 'order_image' => true, 'exclude_extensions' => ['pdf', 'psd']]],
            ['line' => 156, 'alt' => '', 'title' => '', 'path' => static function () { return (order_image_placeholder()); }],
            ['line' => 158, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/psd.svg'; }],
            ['line' => 160, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/pdf.svg'; }],
            ['line' => 162, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['painter_images'], 'csv' => true, 'order_image' => true, 'exclude_extensions' => ['pdf', 'psd']]],
        ],
    ],
    'partials/all_basket_order_items.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => '', 'title' => '', 'path' => static function () { return '/theme/viar/img/icons/gift.svg'; }],
        ],
    ],
    'partials/all_styles/bot_items.blade.php' => [
        'images' => [
            ['line' => 10, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/vertical.webp')); }],
            ['line' => 25, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\AllStylesPage', 'fields' => ['image'], 'media' => [], 'where' => ['is_show' => 1]]],
        ],
    ],
    'partials/all_styles/center_form.blade.php' => [
        'images' => [
            ['line' => 16, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/pinned-photo.webp')); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/upload.svg')); }],
            ['line' => 27, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/image-gallery.svg')); }],
            ['line' => 42, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/mail.svg')); }],
            ['line' => 51, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/phone.svg')); }],
        ],
    ],
    'partials/all_styles/center_items.blade.php' => [
        'images' => [
            ['line' => 10, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/vertical.webp')); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\AllStylesPage', 'fields' => ['image'], 'media' => [], 'where' => ['is_show' => 1]]],
        ],
    ],
    'partials/all_styles/header.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/logo.svg')); }],
            ['line' => 9, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.header_col1_name'], 'path' => static function () { return (asset('img/icons/facebook.svg')); }],
            ['line' => 14, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.header_col1_name'], 'path' => static function () { return (asset('img/icons/instagram.svg')); }],
            ['line' => 24, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.header_col1_name'], 'path' => static function () { return (asset('img/icons/angle-down.svg')); }],
            ['line' => 34, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/angle-arrow.svg')); }],
            ['line' => 63, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.header_col2_name'], 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 80, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.header_col2_name'], 'path' => static function () { return (asset('img/icons/angle-down.svg')); }],
            ['line' => 90, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/angle-arrow.svg')); }],
            ['line' => 115, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.header_col3_name'], 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 132, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.header_col3_name'], 'path' => static function () { return (asset('img/icons/angle-down.svg')); }],
            ['line' => 142, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/angle-arrow.svg')); }],
            ['line' => 167, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.header_col4_name'], 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 194, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.header_col4_name'], 'path' => static function () { return (asset('img/icons/user.svg')); }],
            ['line' => 201, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/user.svg')); }],
            ['line' => 210, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/cart.svg')); }],
            ['line' => 218, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/flag/' . app()->getLocale() . '.svg')); }],
            ['line' => 227, 'alt' => '', 'title' => '', 'binding' => ['glob' => 'images/flag/*.svg']],
            ['line' => 232, 'alt' => '', 'title' => '', 'binding' => ['glob' => 'images/flag/*.svg']],
            ['line' => 242, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/burger.svg')); }],
            ['line' => 245, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/cancel.svg')); }],
        ],
    ],
    'partials/all_styles/modals.blade.php' => [
        'images' => [
            ['line' => 66, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.ths_text_3', 'portrait_buy_form.ths_we_will_process_your_order_instantly', 'portrait_buy_form.setting_whatsapp_phone_href', 'portrait_buy_form.ths_contact_us_now', 'portrait_buy_form.ths_back_to_shopping'], 'path' => static function () { return 'https://viarcanvas.com/images/deadline.svg'; }],
            ['line' => 71, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.ths_text_3', 'portrait_buy_form.ths_we_will_process_your_order_instantly', 'portrait_buy_form.setting_whatsapp_phone_href', 'portrait_buy_form.ths_contact_us_now', 'portrait_buy_form.ths_back_to_shopping'], 'path' => static function () { return 'https://viarcanvas.com/images/premium-icon-whatsapp.svg'; }],
        ],
    ],
    'partials/all_styles/top_items.blade.php' => [
        'images' => [
            ['line' => 17, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/vertical.webp')); }],
            ['line' => 40, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\AllStylesPage', 'fields' => ['image'], 'media' => [], 'where' => ['is_show' => 1]]],
        ],
    ],
    'partials/basket_img.blade.php' => [
        'images' => [
            ['line' => 2, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage']]],
            ['line' => 22, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage']]],
            ['line' => 35, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage']]],
            ['line' => 38, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage']]],
            ['line' => 43, 'alt' => 'Gift', 'title' => '', 'path' => static function () { return (asset('img/benefits-img1.png')); }],
        ],
    ],
    'partials/blog/scripts.blade.php' => [
        'images' => [
            ['line' => 40, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\BlogPost', 'fields' => ['image'], 'where' => ['status' => 'published', 'is_idea' => 0, 'is_stories' => 0]]],
        ],
    ],
    'partials/canvas/after_gen.blade.php' => [
        'images' => [
            ['line' => 16, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/compositions-form-img.png')); }],
            ['line' => 48, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-img.png')); }],
            ['line' => 52, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-content-img.png')); }],
            ['line' => 55, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon1.png')); }],
            ['line' => 59, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon2.png')); }],
            ['line' => 63, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon3.png')); }],
            ['line' => 67, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon4.png')); }],
            ['line' => 71, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon5.png')); }],
            ['line' => 75, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon6.png')); }],
            ['line' => 79, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon7.png')); }],
            ['line' => 83, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon8.png')); }],
            ['line' => 99, 'alt' => '', 'title' => '', 'translations' => ['gl.our_w_title'], 'path' => static function () { return (asset('img/packaging-img.png')); }],
        ],
    ],
    'partials/canvas/header.blade.php' => [
        'images' => [
            ['line' => 25, 'alt' => '', 'title' => '', 'translations' => ['account.index1'], 'path' => static function () { return url('/images_single/canvas.png'); }],
            ['line' => 50, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon.png')); }],
            ['line' => 52, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon2.png')); }],
            ['line' => 54, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon3.png')); }],
            ['line' => 56, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon4.png')); }],
        ],
    ],
    'partials/canvas/price_3_block.blade.php' => [
        'images' => [
            ['line' => 9, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/prices-img1.png')); }],
            ['line' => 12, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn'], 'path' => static function () { return (asset('img/prices-img2.png')); }],
            ['line' => 17, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'path' => static function () { return (asset('img/prices-img3.png')); }],
        ],
    ],
    'partials/canvas/req_2_block.blade.php' => [
        'images' => [
            ['line' => 9, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/file-item-img1.png')); }],
            ['line' => 13, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/file-item-img2.png')); }],
            ['line' => 17, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/file-item-img3.png')); }],
            ['line' => 24, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/social-title-img.png')); }],
            ['line' => 56, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/take-item-img1.png')); }],
            ['line' => 58, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/take-item-after1.png')); }],
            ['line' => 64, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/take-item-img2.png')); }],
            ['line' => 66, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/take-item-after2.png')); }],
            ['line' => 86, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/paid-img.png')); }],
            ['line' => 89, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/paid-img2.png')); }],
            ['line' => 94, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/screen-img.png')); }],
            ['line' => 107, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasReq', 'fields' => ['req_link1_img'], 'media' => []]],
            ['line' => 112, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasReq', 'fields' => ['req_link2_img'], 'media' => []]],
            ['line' => 117, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn'], 'binding' => ['model' => 'App\\Models\\CanvasReq', 'fields' => ['req_link3_img'], 'media' => []]],
            ['line' => 122, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'binding' => ['model' => 'App\\Models\\CanvasReq', 'fields' => ['req_link4_img'], 'media' => []]],
        ],
    ],
    'partials/canvas/serv_quant_5_block.blade.php' => [
        'images' => [
            ['line' => 11, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img.png')); }],
            ['line' => 16, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img2.png')); }],
            ['line' => 21, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img3.png')); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img4.png')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img5.png')); }],
            ['line' => 36, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img6.png')); }],
            ['line' => 43, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img7.png')); }],
            ['line' => 50, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img8.png')); }],
        ],
    ],
    'partials/canvas/tab1.blade.php' => [
        'images' => [
            ['line' => 27, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shapes-img1.png')); }],
            ['line' => 30, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shapes-img2.png')); }],
            ['line' => 33, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shapes-img3.png')); }],
        ],
    ],
    'partials/canvas/timings_6_block.blade.php' => [
        'images' => [
            ['line' => 6, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shipping-title-icon1.png')); }],
            ['line' => 10, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shipping-item-img1.png')); }],
            ['line' => 18, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shipping-title-icon2.png')); }],
            ['line' => 22, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shipping-item-img2.png')); }],
            ['line' => 33, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shipping-title-icon3.png')); }],
            ['line' => 39, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/sum-item-img1.png')); }],
            ['line' => 46, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/sum-item-img2.png')); }],
            ['line' => 54, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/partner-img1.png')); }],
            ['line' => 55, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/partner-img2.png')); }],
            ['line' => 61, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shipping-title-icon4.png')); }],
            ['line' => 65, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'path' => static function () { return (asset('img/shipping-item-img3.png')); }],
        ],
    ],
    'partials/canvas/what_1_block.blade.php' => [
        'images' => [
            ['line' => 9, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/what-item-img1.png')); }],
            ['line' => 14, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/what-item-img2.png')); }],
            ['line' => 19, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/what-item-img3.png')); }],
            ['line' => 24, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/what-item-img4.png')); }],
            ['line' => 29, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'path' => static function () { return (asset('img/what-item-img5.png')); }],
            ['line' => 34, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'path' => static function () { return (asset('img/what-item-img6.png')); }],
        ],
    ],
    'partials/canvas/work_4_block.blade.php' => [
        'images' => [
            ['line' => 26, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'binding' => ['model' => 'App\\Models\\CanvasHeader', 'fields' => ['our_works'], 'media' => []]],
        ],
    ],
    'partials/canvas_new/also_like_examples.blade.php' => [
        'images' => [
            ['line' => 32, 'alt' => '', 'title' => '', 'path' => static function () { return 'images/vertical.webp'; }],
        ],
    ],
    'partials/canvas_new/composition.blade.php' => [
        'images' => [
            ['line' => 16, 'alt' => '', 'title' => '', 'translations' => ['canvas.comp_title', 'canvas.comp_desc', 'canvas.comp_step1_title'], 'path' => static function () { return (asset('images/canvas/composition.jpg')); }],
            ['line' => 73, 'alt' => 'img', 'title' => '', 'translations' => ['canvas.enter_num'], 'path' => static function () { return (asset('images/flag/lv.svg')); }],
            ['line' => 78, 'alt' => 'img', 'title' => '', 'translations' => ['canvas.enter_num'], 'binding' => ['glob' => 'images/flag/*.svg']],
        ],
    ],
    'partials/canvas_new/form/final.blade.php' => [
        'images' => [
            ['line' => 25, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.final_pack'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
        ],
    ],
    'partials/canvas_new/form/step1.blade.php' => [
        'images' => [
            ['line' => 12, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step1_title', 'portrait_buy_form.step1_desc', 'homepage_new.load_photo', 'homepage_new.pree_to_add_photo'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 48, 'alt' => '', 'title' => '', 'translations' => ['portrait.form_files_loaded', 'canvas.form_step1_need_imp_photo'], 'path' => static function () { return (asset('images/canvas/additional.svg')); }],
            ['line' => 87, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 121, 'alt' => '', 'title' => '', 'translations' => ['canvas.form_step1_tarif_1'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 133, 'alt' => '', 'title' => '', 'translations' => ['canvas.form_step1_tarif_1', 'canvas.form_step1_tarif_2'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 144, 'alt' => '', 'title' => '', 'translations' => ['canvas.form_step1_tarif_2', 'canvas.form_step1_tarif_3'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
        ],
    ],
    'partials/canvas_new/form/step2.blade.php' => [
        'images' => [
            ['line' => 14, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step2_title'], 'path' => static function () { return (asset('images/form1.svg')); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/form2.svg')); }],
            ['line' => 38, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/form3.svg')); }],
        ],
    ],
    'partials/canvas_new/form/step4.blade.php' => [
        'images' => [
            ['line' => 24, 'alt' => '', 'title' => '', 'translations' => ['canvas.form_print'], 'path' => static function () { return (asset('images/canvas/execution-item2.svg')); }],
            ['line' => 45, 'alt' => '', 'title' => '', 'translations' => ['canvas.form_print_oil'], 'path' => static function () { return (asset('images/canvas/execution-item1.svg')); }],
        ],
    ],
    'partials/canvas_new/form/step4_show.blade.php' => [
        'images' => [
            ['line' => 14, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
        ],
    ],
    'partials/canvas_new/form/step5.blade.php' => [
        'images' => [
            ['line' => 15, 'alt' => '', 'title' => '', 'translations' => ['canvas.form_step5_title'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 38, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 49, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 61, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
        ],
    ],
    'partials/canvas_new/form/tab2.blade.php' => [
        'images' => [
            ['line' => 35, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio1.jpg')); }],
            ['line' => 67, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio2.jpg')); }],
            ['line' => 99, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio3.jpg')); }],
            ['line' => 131, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio4.jpg')); }],
            ['line' => 163, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_interer_text2'], 'path' => static function () { return (asset('images/canvas/interio5.jpg')); }],
            ['line' => 323, 'alt' => 'Белый', 'title' => 'Белый', 'translations' => ['collage_new.z7_generator_ramma_text4'], 'path' => static function () { return (asset('images/canvas/rama1.jpg')); }],
            ['line' => 355, 'alt' => 'Белый', 'title' => 'Белый', 'translations' => ['collage_new.z7_generator_ramma_text5'], 'path' => static function () { return (asset('images/canvas/rama2.jpg')); }],
        ],
    ],
    'partials/canvas_new/header.blade.php' => [
        'images' => [
            ['line' => 31, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasNew', 'fields' => ['canv_head_desk_img'], 'media' => [], 'prefix' => '', 'image_original' => true]],
        ],
    ],
    'partials/canvas_new/modals/canv_form.blade.php' => [
        'images' => [
            ['line' => 167, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/portrait-form.png')); }],
        ],
    ],
    'partials/canvas_new/modals/popup_add_to_cart.blade.php' => [
        'images' => [
            ['line' => 16, 'alt' => '', 'title' => '', 'translations' => ['canvas.modal_btn1', 'canvas.modal_btn2'], 'path' => static function () { return (asset('images/canvas/canvas-popup.png')); }],
        ],
    ],
    'partials/canvas_new/photo_work.blade.php' => [
        'images' => [
            ['line' => 152, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasNew', 'fields' => [], 'media' => ['photo_work_optimal']]],
            ['line' => 165, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_work_before', 'canvas.photo_work_after'], 'binding' => ['model' => 'App\\Models\\CanvasNew', 'fields' => [], 'media' => ['photo_work_optimal_after']]],
            ['line' => 247, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasNew', 'fields' => [], 'media' => ['photo_work_premium']]],
            ['line' => 260, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_work_before', 'canvas.photo_work_after'], 'binding' => ['model' => 'App\\Models\\CanvasNew', 'fields' => [], 'media' => ['photo_work_premium_after']]],
        ],
    ],
    'partials/canvas_new/sizes.blade.php' => [
        'images' => [
            ['line' => 2, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/union.png')); }],
            ['line' => 12, 'alt' => 'img', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/size-bg.jpg')); }],
            ['line' => 19, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/10.webp')); }],
            ['line' => 24, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/9.webp')); }],
            ['line' => 97, 'alt' => 'img', 'title' => '', 'translations' => ['gl.cm'], 'path' => static function () { return (asset('images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'partials/canvas_new/slider.blade.php' => [
        'images' => [
            ['line' => 35, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasSlider', 'fields' => ['png'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 138, 'alt' => 'Viar Image', 'title' => '', 'path' => static function () { return (asset('images/collage/gift.svg')); }],
            ['line' => 166, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'partials/canvas_new/steps.blade.php' => [
        'images' => [
            ['line' => 17, 'alt' => '', 'title' => '', 'translations' => ['canvas.order_steps_title', 'canvas.order_steps_desc', 'canvas.order_step1_title', 'canvas.order_step1_desc'], 'path' => static function () { return (asset('images/photo.png')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'translations' => ['canvas.order_step1_title', 'canvas.order_step1_desc', 'canvas.order_step2_title', 'canvas.order_step2_desc'], 'path' => static function () { return (asset('images/conversation.png')); }],
            ['line' => 45, 'alt' => '', 'title' => '', 'translations' => ['canvas.order_step2_title', 'canvas.order_step2_desc', 'canvas.order_step3_title', 'canvas.order_step3_desc'], 'path' => static function () { return (asset('images/portrait.png')); }],
            ['line' => 59, 'alt' => '', 'title' => '', 'translations' => ['canvas.order_step3_title', 'canvas.order_step3_desc', 'canvas.order_step4_title', 'canvas.order_step4_desc'], 'path' => static function () { return (asset('images/canvas.png')); }],
            ['line' => 73, 'alt' => '', 'title' => '', 'translations' => ['canvas.order_step4_title', 'canvas.order_step4_desc', 'canvas.order_step5_title', 'canvas.order_step5_desc'], 'path' => static function () { return (asset('images/delivery1.png')); }],
            ['line' => 94, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/work/work-4.svg')); }],
            ['line' => 98, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/work/work-5.svg')); }],
            ['line' => 131, 'alt' => '', 'title' => '', 'translations' => ['portrait.form_add_comment', 'portrait.form_photo'], 'path' => static function () { return (asset('img/icons/phone.svg')); }],
            ['line' => 197, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_title', 'canvas.photo_sec_step1'], 'path' => static function () { return (asset('images/canvas/advantages.png')); }],
            ['line' => 206, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_title', 'canvas.photo_sec_step1', 'canvas.photo_sec_step2', 'canvas.photo_sec_step3'], 'path' => static function () { return (asset('images/canvas/tree.svg')); }],
            ['line' => 210, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_title', 'canvas.photo_sec_step1', 'canvas.photo_sec_step2', 'canvas.photo_sec_step3', 'canvas.photo_sec_step4'], 'path' => static function () { return (asset('images/canvas/leaf.svg')); }],
            ['line' => 214, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_title', 'canvas.photo_sec_step1', 'canvas.photo_sec_step2', 'canvas.photo_sec_step3', 'canvas.photo_sec_step4', 'canvas.photo_sec_step5'], 'path' => static function () { return (asset('images/canvas/brush.svg')); }],
            ['line' => 218, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_step1', 'canvas.photo_sec_step2', 'canvas.photo_sec_step3', 'canvas.photo_sec_step4', 'canvas.photo_sec_step5', 'canvas.photo_sec_step6'], 'path' => static function () { return (asset('images/canvas/shield.svg')); }],
            ['line' => 222, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_step2', 'canvas.photo_sec_step3', 'canvas.photo_sec_step4', 'canvas.photo_sec_step5', 'canvas.photo_sec_step6', 'canvas.photo_sec_step7'], 'path' => static function () { return (asset('images/canvas/picture.svg')); }],
            ['line' => 226, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_step3', 'canvas.photo_sec_step4', 'canvas.photo_sec_step5', 'canvas.photo_sec_step6', 'canvas.photo_sec_step7'], 'path' => static function () { return (asset('images/canvas/clock.svg')); }],
            ['line' => 230, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_step4', 'canvas.photo_sec_step5', 'canvas.photo_sec_step6', 'canvas.photo_sec_step7'], 'path' => static function () { return (asset('images/canvas/package.svg')); }],
        ],
    ],
    'partials/canvas_new/tabs/fifth.blade.php' => [
        'images' => [
            ['line' => 5, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab5__inner__title'], 'path' => static function () { return (ver_asset('images/clock.png')); }],
            ['line' => 14, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab5__inner__title', 'canvas.tab5__inner__block1_title', 'canvas.tab5__inner__block1_text'], 'path' => static function () { return (ver_asset('images/three-days.jpg')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab5__inner__block1_title', 'canvas.tab5__inner__block1_text', 'canvas.tab5__inner__block2_title', 'canvas.tab5__inner__block2_text'], 'path' => static function () { return (ver_asset('images/one-day.jpg')); }],
            ['line' => 48, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab5__inner__block2_title', 'canvas.tab5__inner__block2_text', 'canvas.tab5__inner__block3_title', 'canvas.tab5__inner__block3_title_after'], 'path' => static function () { return (ver_asset('images/on-date.jpg')); }],
            ['line' => 64, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab5__inner__block3_title', 'canvas.tab5__inner__block3_title_after', 'canvas.tab5__delivery__title'], 'path' => static function () { return (ver_asset('images/delivery.png')); }],
            ['line' => 73, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab5__delivery__title', 'canvas.tab5__delivery__block1_title', 'canvas.tab5__delivery__block1_title_after'], 'path' => static function () { return (ver_asset('images/van.jpg')); }],
            ['line' => 88, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab5__delivery__block1_title', 'canvas.tab5__delivery__block1_title_after', 'canvas.tab5__delivery__block2_title', 'canvas.tab5__delivery__block2_title_after'], 'path' => static function () { return (ver_asset('images/on-adress.jpg')); }],
            ['line' => 103, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab5__delivery__block2_title', 'canvas.tab5__delivery__block2_title_after', 'canvas.tab5__delivery__block3_title', 'canvas.tab5__delivery__block3_title_after', 'canvas.tab5__delivery__after_text'], 'path' => static function () { return (ver_asset('images/abroad.jpg')); }],
            ['line' => 122, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab5__delivery__block3_title_after', 'canvas.tab5__delivery__after_text', 'canvas.tab5__delivery__after_block1_text'], 'path' => static function () { return (ver_asset('images/venipak.png')); }],
            ['line' => 132, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab5__delivery__after_block1_text', 'canvas.tab5__delivery__after_block2_text'], 'path' => static function () { return (ver_asset('images/dpd.png')); }],
            ['line' => 133, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab5__delivery__after_block1_text', 'canvas.tab5__delivery__after_block2_text', 'canvas.tabs_show_btn_text', 'canvas.tabs_hide_btn_text'], 'path' => static function () { return (ver_asset('images/omniva-logo-41B019A1E9-seeklogo.com.png')); }],
        ],
    ],
    'partials/canvas_new/tabs/second.blade.php' => [
        'images' => [
            ['line' => 8, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab2_item1_desc'], 'path' => static function () { return (ver_asset('images/canvas/minimum-size.jpg')); }],
            ['line' => 20, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab2_item1_desc', 'canvas.tab2_item2_desc'], 'path' => static function () { return (ver_asset('images/canvas/social.jpg')); }],
            ['line' => 32, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab2_item2_desc', 'canvas.tab2_item3_desc'], 'path' => static function () { return (ver_asset('images/canvas/selfie.jpg')); }],
            ['line' => 62, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab2_soc_desc', 'canvas.tab2_list_item1_text', 'canvas.tab2_list_item2_text'], 'path' => static function () { return (ver_asset('images/canvas/social-min.png')); }],
        ],
    ],
    'partials/collage-constructor/comp_pic.blade.php' => [
        'images' => [
            ['line' => 3, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-img.png')); }],
            ['line' => 8, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-content-img2.png')); }],
            ['line' => 12, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon1.png')); }],
            ['line' => 17, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon2.png')); }],
            ['line' => 22, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon3.png')); }],
            ['line' => 27, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon4.png')); }],
            ['line' => 32, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon5.png')); }],
            ['line' => 37, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon6.png')); }],
            ['line' => 42, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon7.png')); }],
            ['line' => 47, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon8.png')); }],
        ],
    ],
    'partials/collage-constructor/devs.blade.php' => [
        'images' => [
            ['line' => 442, 'alt' => '', 'title' => '', 'binding' => ['inline_templates' => 'js/collage-templates.js']],
        ],
    ],
    'partials/collage-constructor/generate.blade.php' => [
        'images' => [
            ['line' => 88, 'alt' => '', 'title' => '', 'translations' => ['gl.help_add_sm'], 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 91, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 94, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 97, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 100, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 103, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 106, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 109, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 116, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 119, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 122, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 125, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 128, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 131, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 134, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 137, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 140, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 143, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 146, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 149, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 152, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 155, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 158, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
            ['line' => 161, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/smiles/kisspng-iphone-emoji-apple-ios-11-emojis-5abe1fe3bbe4f9.0061549515224094437696.png'; }],
        ],
    ],
    'partials/collage-constructor/packing.blade.php' => [
        'images' => [
            ['line' => 10, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/packaging-img.png')); }],
        ],
    ],
    'partials/collage_new/advantage_screen.blade.php' => [
        'images' => [
            ['line' => 22, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['collage_new.z0_advantage_screen_title', 'collage_new.z0_advantage_screen_subtitle'], 'binding' => ['model' => 'App\\Models\\ACollageAdvantageScreen', 'fields' => ['image'], 'media' => [], 'prefix' => '']],
        ],
    ],
    'partials/collage_new/doubt_screen.blade.php' => [
        'images' => [
            ['line' => 14, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['collage_new.z4_doubt_screen_title', 'collage_new.z4_doubt_screen_question', 'collage_new.z4_doubt_screen_question_text', 'collage_new.z4_doubt_screen_question_price'], 'path' => static function () { return (asset('images/collage/tel.svg')); }],
            ['line' => 39, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['collage_new.z4_doubt_screen_question_text2', 'collage_new.z4_doubt_screen_btn', 'collage_new.z4_doubt_screen_express_text1', 'collage_new.z4_doubt_screen_express_text2'], 'path' => static function () { return (asset('images/collage/express.svg')); }],
            ['line' => 43, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['collage_new.z4_doubt_screen_btn', 'collage_new.z4_doubt_screen_express_text1', 'collage_new.z4_doubt_screen_express_text2'], 'path' => static function () { return (asset('images/collage/gift.svg')); }],
            ['line' => 51, 'alt' => 'img', 'title' => '', 'translations' => ['collage_new.z4_doubt_screen_express_text1', 'collage_new.z4_doubt_screen_express_text2'], 'path' => static function () { return (asset('images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'partials/collage_new/f_order_screen.blade.php' => [
        'images' => [
            ['line' => 26, 'alt' => 'Viar', 'title' => '', 'translations' => ['collage_new.z3_f_order_screen_1_title', 'collage_new.z3_f_order_screen_1_text'], 'path' => static function () { return (asset('images/collage/fo1.jpg')); }],
            ['line' => 58, 'alt' => 'Viar', 'title' => '', 'translations' => ['collage_new.z3_f_order_screen_2_title', 'collage_new.z3_f_order_screen_2_text'], 'path' => static function () { return (asset('images/collage/fo3.jpg')); }],
            ['line' => 90, 'alt' => 'Viar', 'title' => '', 'translations' => ['collage_new.z3_f_order_screen_3_title', 'collage_new.z3_f_order_screen_3_text'], 'path' => static function () { return (asset('images/collage/fo2.jpg')); }],
            ['line' => 122, 'alt' => 'Viar', 'title' => '', 'translations' => ['collage_new.z3_f_order_screen_4_title', 'collage_new.z3_f_order_screen_4_text'], 'path' => static function () { return (asset('images/collage/fo4.jpg')); }],
        ],
    ],
    'partials/collage_new/generator.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => 'img', 'title' => '', 'translations' => ['collage_new.z7_generator_title'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg'; }],
            ['line' => 21, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_title', 'collage_new.z7_generator_subtitle'], 'path' => static function () { return (asset('images/collage/clg.svg')); }],
            ['line' => 50, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc1', 'collage_new.z7_generator_ctc2'], 'path' => static function () { return (asset('images/collage/ctc1.svg')); }],
            ['line' => 54, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc1', 'collage_new.z7_generator_ctc2', 'collage_new.z7_generator_ctc3'], 'path' => static function () { return (asset('images/collage/ctc2.svg')); }],
            ['line' => 58, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc2', 'collage_new.z7_generator_ctc3', 'collage_new.z7_generator_ctc4'], 'path' => static function () { return (asset('images/collage/ctc4.svg')); }],
            ['line' => 62, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc3', 'collage_new.z7_generator_ctc4', 'collage_new.z7_generator_ctc5'], 'path' => static function () { return (asset('images/collage/ctc3.svg')); }],
            ['line' => 66, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc4', 'collage_new.z7_generator_ctc5', 'collage_new.z7_generator_ctc6'], 'path' => static function () { return (asset('images/collage/ctc5.svg')); }],
            ['line' => 70, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc5', 'collage_new.z7_generator_ctc6', 'collage_new.z7_generator_ctc7'], 'path' => static function () { return (asset('images/collage/ctc6.svg')); }],
            ['line' => 74, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc6', 'collage_new.z7_generator_ctc7'], 'path' => static function () { return (asset('images/collage/ctc7.svg')); }],
            ['line' => 83, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls1', 'collage_new.z7_generator_controls2', 'collage_new.z7_generator_controls3'], 'path' => static function () { return (asset('images/collage/tool1.svg')); }],
            ['line' => 86, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls1', 'collage_new.z7_generator_controls2', 'collage_new.z7_generator_controls3', 'collage_new.z7_generator_controls4'], 'path' => static function () { return (asset('images/collage/tool2.svg')); }],
            ['line' => 89, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls2', 'collage_new.z7_generator_controls3', 'collage_new.z7_generator_controls4', 'collage_new.z7_generator_controls5'], 'path' => static function () { return (asset('images/collage/tool3.svg')); }],
            ['line' => 92, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls3', 'collage_new.z7_generator_controls4', 'collage_new.z7_generator_controls5', 'collage_new.z7_generator_controls6'], 'path' => static function () { return (asset('images/collage/tool4.svg')); }],
            ['line' => 95, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls4', 'collage_new.z7_generator_controls5', 'collage_new.z7_generator_controls6', 'collage_new.z7_generator_controls7'], 'path' => static function () { return (asset('images/collage/tool5.svg')); }],
            ['line' => 98, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls5', 'collage_new.z7_generator_controls6', 'collage_new.z7_generator_controls7', 'collage_new.z7_generator_controls8'], 'path' => static function () { return (asset('images/collage/tool6.svg')); }],
            ['line' => 101, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls6', 'collage_new.z7_generator_controls7', 'collage_new.z7_generator_controls8', 'collage_new.z7_generator_controls9'], 'path' => static function () { return (asset('images/collage/tool7.svg')); }],
            ['line' => 104, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls7', 'collage_new.z7_generator_controls8', 'collage_new.z7_generator_controls9', 'collage_new.z7_generator_controls10'], 'path' => static function () { return (asset('images/collage/tool8.svg')); }],
            ['line' => 107, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls8', 'collage_new.z7_generator_controls9', 'collage_new.z7_generator_controls10', 'collage_new.z7_generator_controls11'], 'path' => static function () { return (asset('images/collage/tool9.svg')); }],
            ['line' => 110, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls9', 'collage_new.z7_generator_controls10', 'collage_new.z7_generator_controls11'], 'path' => static function () { return (asset('images/collage/tool10.svg')); }],
            ['line' => 113, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls10', 'collage_new.z7_generator_controls11'], 'path' => static function () { return (asset('images/collage/tool11.svg')); }],
            ['line' => 171, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_foto_text5'], 'path' => static function () { return (asset('images/canvas/additional.svg')); }],
            ['line' => 193, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_foto_text8'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 204, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_foto_text9'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 215, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_foto_text10'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 283, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_foto_fon_text6'], 'binding' => ['model' => 'App\\Models\\ACollageFon', 'fields' => ['image'], 'media' => []]],
            ['line' => 351, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\ACollageStiker', 'fields' => ['image'], 'media' => []]],
            ['line' => 373, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/collage/angle-close.svg')); }],
            ['line' => 407, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio1.jpg')); }],
            ['line' => 426, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio2.jpg')); }],
            ['line' => 445, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio3.jpg')); }],
            ['line' => 465, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio4.jpg')); }],
            ['line' => 485, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio5.jpg')); }],
            ['line' => 598, 'alt' => '{!! trans(\'collage_new.z7_generator_ramma_text4\') !!}', 'title' => '{!! trans(\'collage_new.z7_generator_ramma_text4\') !!}', 'translations' => ['collage_new.z7_generator_ramma_text4'], 'path' => static function () { return (asset('images/canvas/rama1.jpg')); }],
            ['line' => 617, 'alt' => '{!! trans(\'collage_new.z7_generator_ramma_text5\') !!}', 'title' => '{!! trans(\'collage_new.z7_generator_ramma_text5\') !!}', 'translations' => ['collage_new.z7_generator_ramma_text5'], 'path' => static function () { return (asset('images/canvas/rama2.jpg')); }],
            ['line' => 656, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls14', 'collage_new.z7_generator_controls15', 'collage_new.z7_generator_controls16'], 'path' => static function () { return (asset('images/collage/tool12.svg')); }],
            ['line' => 660, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls15', 'collage_new.z7_generator_controls16'], 'path' => static function () { return (asset('images/collage/tool12.svg')); }],
            ['line' => 712, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 839, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_reviews_text', 'collage_new.z7_reviews_subtext'], 'binding' => ['sources' => [['model' => 'App\\Models\\OurWork', 'fields' => ['img'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']], ['model' => 'App\\Models\\Review', 'fields' => ['img'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']]]]],
            ['line' => 844, 'alt' => 'img', 'title' => '', 'binding' => ['sources' => [['model' => 'App\\Models\\OurWork', 'fields' => ['avatar'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']], ['model' => 'App\\Models\\Review', 'fields' => ['avatar'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']]]]],
        ],
    ],
    'partials/collage_new/generator_info.blade.php' => [
        'images' => [
            ['line' => 22, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z6_generator_info_title', 'collage_new.z6_generator_info_subtitle', 'collage_new.z6_generator_info_text1', 'collage_new.z6_generator_info_text2', 'collage_new.z6_generator_info_text3'], 'path' => static function () { return (asset('images/collage/vector14.svg')); }],
            ['line' => 28, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z6_generator_info_text1', 'collage_new.z6_generator_info_text2', 'collage_new.z6_generator_info_text3', 'collage_new.z6_generator_info_text4'], 'path' => static function () { return (asset('images/collage/cub1.svg')); }],
            ['line' => 34, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z6_generator_info_text2', 'collage_new.z6_generator_info_text3', 'collage_new.z6_generator_info_text4', 'collage_new.z6_generator_info_text5'], 'path' => static function () { return (asset('images/collage/cub2.svg')); }],
            ['line' => 42, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z6_generator_info_text3', 'collage_new.z6_generator_info_text4', 'collage_new.z6_generator_info_text5'], 'path' => static function () { return (asset('images/collage/vector15.svg')); }],
            ['line' => 51, 'alt' => 'Портрет по фото', 'title' => '', 'translations' => ['collage_new.z6_generator_info_text5', 'collage_new.z6_generator_info_text6'], 'path' => static function () { return (asset('images/collage/g1.jpg')); }],
            ['line' => 62, 'alt' => 'Портрет по фото', 'title' => '', 'translations' => ['collage_new.z6_generator_info_text6', 'collage_new.z6_generator_info_text7'], 'path' => static function () { return (asset('images/collage/g2.jpg')); }],
            ['line' => 73, 'alt' => 'Портрет по фото', 'title' => '', 'translations' => ['collage_new.z6_generator_info_text7'], 'path' => static function () { return (asset('images/collage/g3.jpg')); }],
        ],
    ],
    'partials/collage_new/js.blade.php' => [
        'images' => [
            ['line' => 486, 'alt' => '', 'title' => '', 'binding' => ['inline_templates' => 'theme/viar/js/collage-templates.js']],
        ],
    ],
    'partials/collage_new/love_screen.blade.php' => [
        'images' => [
            ['line' => 5, 'alt' => 'img', 'title' => '', 'translations' => ['collage_new.z5_love_screen_title', 'collage_new.z5_love_screen_text1'], 'path' => static function () { return (asset('images/icon/ellipse-whete.svg')); }],
            ['line' => 30, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z5_love_screen_text2', 'collage_new.z5_love_screen_text3'], 'path' => static function () { return (asset('images/collage/11.jpg')); }],
            ['line' => 50, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/collage/12.jpg')); }],
            ['line' => 61, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/collage/ellipse-orb.svg')); }],
        ],
    ],
    'partials/collage_new/order_screen.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => 'img', 'title' => '', 'translations' => ['collage_new.z2_order_screen_title', 'collage_new.z2_order_screen_subtitle'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg'; }],
            ['line' => 28, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z2_order_screen_title', 'collage_new.z2_order_screen_subtitle', 'collage_new.z2_order_screen_text1', 'collage_new.z2_order_screen_text2'], 'path' => static function () { return (asset('images/collage/ord1.svg')); }],
            ['line' => 36, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z2_order_screen_text1', 'collage_new.z2_order_screen_text2', 'collage_new.z2_order_screen_text3'], 'path' => static function () { return (asset('images/collage/ord2.svg')); }],
            ['line' => 44, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z2_order_screen_text2', 'collage_new.z2_order_screen_text3', 'collage_new.z2_order_screen_text4'], 'path' => static function () { return (asset('images/collage/ord3.svg')); }],
            ['line' => 52, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z2_order_screen_text3', 'collage_new.z2_order_screen_text4', 'collage_new.z2_order_screen_text5'], 'path' => static function () { return (asset('images/collage/ord4.svg')); }],
            ['line' => 60, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z2_order_screen_text4', 'collage_new.z2_order_screen_text5', 'collage_new.z2_order_screen_text6'], 'path' => static function () { return (asset('images/collage/ord5.svg')); }],
            ['line' => 68, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z2_order_screen_text5', 'collage_new.z2_order_screen_text6', 'collage_new.z2_order_screen_text7'], 'path' => static function () { return (asset('images/collage/ord6.svg')); }],
            ['line' => 76, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z2_order_screen_text6', 'collage_new.z2_order_screen_text7'], 'path' => static function () { return (asset('images/collage/ord7.svg')); }],
            ['line' => 103, 'alt' => 'img', 'title' => '', 'translations' => ['collage_new.z2_order_screen_btn'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg'; }],
        ],
    ],
    'partials/collage_new/slider.blade.php' => [
        'images' => [
            ['line' => 13, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['collage_new.c_slider_title'], 'binding' => ['model' => 'App\\Models\\ACollageSlider', 'fields' => ['{locale}'], 'media' => []]],
            ['line' => 91, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['collage_new.c_slider_z_text5', 'collage_new.c_slider_z_text6_btn', 'collage_new.c_slider_z_text7', 'collage_new.c_slider_z_text8'], 'path' => static function () { return (asset('images/collage/express.svg')); }],
            ['line' => 95, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['collage_new.c_slider_z_text5', 'collage_new.c_slider_z_text6_btn', 'collage_new.c_slider_z_text7', 'collage_new.c_slider_z_text8'], 'path' => static function () { return (asset('images/collage/gift.svg')); }],
            ['line' => 129, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'partials/collage_new/why_form.blade.php' => [
        'images' => [
            ['line' => 30, 'alt' => '', 'title' => '', 'translations' => ['collage_new.add_comment', 'collage_new.foto'], 'path' => static function () { return (asset('img/icons/phone.svg')); }],
            ['line' => 92, 'alt' => 'img', 'title' => '', 'translations' => ['portrait_buy_form.loading'], 'path' => static function () { return (asset('images/pinned-photo.png')); }],
        ],
    ],
    'partials/collage_new/why_screen.blade.php' => [
        'images' => [
            ['line' => 52, 'alt' => 'img', 'title' => '', 'translations' => ['collage_new.more_portraits'], 'path' => static function () { return (asset('./images/icon/load-more.png')); }],
        ],
    ],
    'partials/family/comp_pick.blade.php' => [
        'images' => [
            ['line' => 3, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-img.png')); }],
            ['line' => 7, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-content-img2.png')); }],
            ['line' => 10, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon1.png')); }],
            ['line' => 14, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon2.png')); }],
            ['line' => 18, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon3.png')); }],
            ['line' => 22, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon4.png')); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon5.png')); }],
            ['line' => 30, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon6.png')); }],
            ['line' => 34, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon7.png')); }],
            ['line' => 38, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon8.png')); }],
        ],
    ],
    'partials/family/evr.blade.php' => [
        'images' => [
            ['line' => 2, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/everything-img.png')); }],
        ],
    ],
    'partials/family/packs.blade.php' => [
        'images' => [
            ['line' => 10, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/packaging-img.png')); }],
        ],
    ],
    'partials/family/stages.blade.php' => [
        'images' => [
            ['line' => 7, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img1.png')); }],
            ['line' => 8, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after1.png')); }],
            ['line' => 11, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer1.png')); }],
            ['line' => 17, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img2.png')); }],
            ['line' => 18, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after2.png')); }],
            ['line' => 21, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer2.png')); }],
            ['line' => 27, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img3.png')); }],
            ['line' => 28, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after3.png')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer3.png')); }],
            ['line' => 37, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img4.png')); }],
            ['line' => 38, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after4.png')); }],
            ['line' => 41, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer4.png')); }],
            ['line' => 47, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img5.png')); }],
            ['line' => 48, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after5.png')); }],
            ['line' => 51, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer5.png')); }],
        ],
    ],
    'partials/faq.blade.php' => [
        'images' => [
            ['line' => 38, 'alt' => '', 'title' => '', 'translations' => ['account.index1'], 'path' => static function () { return (asset('img/faq-img1.png')); }],
            ['line' => 50, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/faq-img2.png')); }],
        ],
    ],
    'partials/gallery_item/comp_pics.blade.php' => [
        'images' => [
            ['line' => 3, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-img.png')); }],
            ['line' => 8, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-content-img.png')); }],
            ['line' => 12, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon1.png')); }],
            ['line' => 17, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon2.png')); }],
            ['line' => 22, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon3.png')); }],
            ['line' => 27, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon4.png')); }],
            ['line' => 32, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon5.png')); }],
            ['line' => 37, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon6.png')); }],
            ['line' => 42, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon7.png')); }],
            ['line' => 47, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon8.png')); }],
        ],
    ],
    'partials/gallery_item/compositions.blade.php' => [
        'images' => [
            ['line' => 5, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/compositions-form-img.png')); }],
        ],
    ],
    'partials/gallery_item/create_pic.blade.php' => [
        'images' => [
            ['line' => 6, 'alt' => '', 'title' => '', 'translations' => ['gl.cons_title', 'gl.cons_link_text', 'gl.cons_bot_text'], 'path' => static function () { return (asset('img/create-picture-img3.png')); }],
            ['line' => 10, 'alt' => '', 'title' => '', 'translations' => ['gl.cons_title', 'gl.cons_link_text', 'gl.cons_bot_text'], 'path' => static function () { return (asset('img/create-picture-img4.png')); }],
        ],
    ],
    'partials/gift_card.blade.php' => [
        'images' => [
            ['line' => 43, 'alt' => '', 'title' => '', 'translations' => ['account.index1'], 'path' => static function () { return (asset('img/front_').app()->getLocale().'.png'); }],
            ['line' => 46, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/back_').app()->getLocale().'.png'); }],
            ['line' => 131, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/benefits-img1.png')); }],
            ['line' => 137, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/benefits-img2.png')); }],
            ['line' => 143, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/benefits-img3.png')); }],
            ['line' => 149, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/benefits-img4.png')); }],
            ['line' => 155, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/benefits-img5.png')); }],
            ['line' => 161, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/benefits-img6.png')); }],
        ],
    ],
    'partials/graph_portrait/generator.blade.php' => [
        'images' => [
            ['line' => 42, 'alt' => '', 'title' => '', 'translations' => ['gl.pic_on_wall'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]],
            ['line' => 61, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/download-icon.png')); }],
            ['line' => 65, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/download-icon.png')); }],
            ['line' => 69, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/download-icon.png')); }],
            ['line' => 174, 'alt' => '', 'title' => '', 'translations' => ['modular_pictures.index46', 'modular_pictures.index45'], 'path' => static function () { return (asset('img/execution-item1.png')); }],
            ['line' => 181, 'alt' => '', 'title' => '', 'translations' => ['modular_pictures.index46', 'modular_pictures.index45'], 'path' => static function () { return (asset('img/execution-item2.png')); }],
        ],
    ],
    'partials/graph_portrait_promo/before_after.blade.php' => [
        'images' => [
            ['line' => 17, 'alt' => '', 'title' => '', 'translations' => ['gl.before_after1', 'gl.before_after2', 'gl.before_after3', 'gl.move_scroller', 'gl.before_after4'], 'binding' => ['model' => 'App\\Models\\PortBeforeAfter', 'fields' => ['img_before'], 'media' => []]],
            ['line' => 19, 'alt' => '', 'title' => '', 'translations' => ['gl.before_after1', 'gl.before_after2', 'gl.before_after3', 'gl.move_scroller', 'gl.before_after4'], 'binding' => ['model' => 'App\\Models\\PortBeforeAfter', 'fields' => ['img_after'], 'media' => []]],
            ['line' => 47, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortBeforeAfter', 'fields' => ['main_image'], 'media' => [], 'prefix' => '', 'image_original' => true]],
        ],
    ],
    'partials/graph_portrait_promo/everyth.blade.php' => [
        'images' => [
            ['line' => 2, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/everything-img.png')); }],
        ],
    ],
    'partials/graph_portrait_promo/granj.blade.php' => [
        'images' => [
            ['line' => 14, 'alt' => '', 'title' => '', 'translations' => ['gl.sh_title', 'gl.sh_sub', 'gl.all_obr', 'gl.group_obr', 'gl.group_obr_subtext'], 'binding' => ['model' => 'App\\Models\\SharjWorkEx', 'fields' => ['image'], 'media' => []]],
            ['line' => 39, 'alt' => '', 'title' => '', 'translations' => ['gl.all_obr', 'gl.group_obr', 'gl.group_obr_subtext'], 'binding' => ['model' => 'App\\Models\\SharjWorkGroupEx', 'fields' => ['image'], 'media' => []]],
        ],
    ],
    'partials/graph_portrait_promo/header.blade.php' => [
        'images' => [
            ['line' => 109, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon.png')); }],
            ['line' => 111, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon2.png')); }],
            ['line' => 113, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon3.png')); }],
            ['line' => 115, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon4.png')); }],
            ['line' => 29, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['add_image_bg'], 'where' => ['active' => 1]]],
        ],
    ],
    'partials/graph_portrait_promo/packs.blade.php' => [
        'images' => [
            ['line' => 10, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/packaging-img.png')); }],
        ],
    ],
    'partials/graph_portrait_promo/steps.blade.php' => [
        'images' => [
            ['line' => 7, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img1.png')); }],
            ['line' => 8, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after1.png')); }],
            ['line' => 11, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer1.png')); }],
            ['line' => 17, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img2.png')); }],
            ['line' => 18, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after2.png')); }],
            ['line' => 21, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer2.png')); }],
            ['line' => 27, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img3.png')); }],
            ['line' => 28, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after3.png')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer3.png')); }],
            ['line' => 37, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img4.png')); }],
            ['line' => 38, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after4.png')); }],
            ['line' => 41, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer4.png')); }],
            ['line' => 47, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img5.png')); }],
            ['line' => 48, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after5.png')); }],
            ['line' => 51, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer5.png')); }],
        ],
    ],
    'partials/graph_portrait_promo/tabs.blade.php' => [
        'images' => [
            ['line' => 18, 'alt' => '', 'title' => '', 'translations' => ['gl.what_is_text'], 'path' => static function () { return (asset('img/what-item-img20.png')); }],
            ['line' => 49, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/requirements-img3.png')); }],
            ['line' => 56, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/requirements-item-img1.png')); }],
            ['line' => 60, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'path' => static function () { return (asset('img/requirements-item-img2.png')); }],
            ['line' => 64, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'path' => static function () { return (asset('img/requirements-item-img3.png')); }],
            ['line' => 81, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn', 'gl.diff_sizes'], 'path' => static function () { return (asset('img/prices-content2.png')); }],
            ['line' => 365, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-img.png')); }],
            ['line' => 371, 'alt' => '', 'title' => '', 'path' => static function () { return ' ' . (asset('img/components-item-img2.png')); }],
            ['line' => 377, 'alt' => '', 'title' => '', 'path' => static function () { return ' ' . (asset('img/components-item-img3.png')); }],
            ['line' => 383, 'alt' => '', 'title' => '', 'path' => static function () { return ' ' . (asset('img/components-item-img4.png')); }],
            ['line' => 389, 'alt' => '', 'title' => '', 'path' => static function () { return ' ' . (asset('img/components-item-img5.png')); }],
            ['line' => 400, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/quality-img.png')); }],
            ['line' => 453, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\OilHeader', 'fields' => ['our_works']]],
            ['line' => 479, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['our_works'], 'media' => [], 'where' => ['active' => 1]]],
            ['line' => 506, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn'], 'path' => static function () { return (asset('img/service-item-img.png' )); }],
            ['line' => 510, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img2.png' )); }],
            ['line' => 514, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img3.png' )); }],
            ['line' => 518, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img4.png' )); }],
            ['line' => 522, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img5.png' )); }],
            ['line' => 526, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img6.png' )); }],
            ['line' => 532, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img7.png' )); }],
            ['line' => 538, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img8.png' )); }],
        ],
    ],
    'partials/index_new/emoj_4_5.blade.php' => [
        'images' => [
            ['line' => 57, 'alt' => 'img', 'title' => '', 'binding' => ['model' => 'App\\Models\\AllStyleFormStepItem', 'fields' => ['image'], 'media' => [], 'where' => ['name' => '1_default', 'step' => 4]]],
            ['line' => 125, 'alt' => 'img', 'title' => '', 'binding' => ['model' => 'App\\Models\\AllStyleFormStepItem', 'fields' => ['image'], 'media' => [], 'where' => ['name' => '1_default', 'step' => 4]]],
            ['line' => 145, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-1.png')); }],
            ['line' => 154, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-2.png')); }],
            ['line' => 163, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-3.png')); }],
            ['line' => 180, 'alt' => 'img', 'title' => '', 'binding' => ['model' => 'App\\Models\\AllStyleFormStepItem', 'fields' => ['image'], 'media' => [], 'where' => ['name' => '2_default', 'step' => 4]]],
            ['line' => 224, 'alt' => 'img', 'title' => '', 'binding' => ['model' => 'App\\Models\\AllStyleFormStepItem', 'fields' => ['image'], 'media' => [], 'where' => ['name' => '1_default', 'step' => 4]]],
            ['line' => 244, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-1.png')); }],
            ['line' => 253, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-2.png')); }],
            ['line' => 262, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-3.png')); }],
            ['line' => 279, 'alt' => 'img', 'title' => '', 'binding' => ['model' => 'App\\Models\\AllStyleFormStepItem', 'fields' => ['image'], 'media' => [], 'where' => ['name' => '3_default', 'step' => 4]]],
            ['line' => 312, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz-full/kviz-1.jpg')); }],
            ['line' => 339, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz-full/kviz-1.jpg')); }],
            ['line' => 358, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-1.png')); }],
            ['line' => 367, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-2.png')); }],
            ['line' => 376, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-3.png')); }],
            ['line' => 387, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/bg/kviz-finish.png')); }],
            ['line' => 409, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/phone.svg')); }],
            ['line' => 523, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-1.png')); }],
            ['line' => 532, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-2.png')); }],
            ['line' => 541, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-3.png')); }],
            ['line' => 550, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/icon/check-done.svg')); }],
        ],
    ],
    'partials/index_new/examples4.blade.php' => [
        'images' => [
            ['line' => 54, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.our_portraits_btn_title'], 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
        ],
    ],
    'partials/index_new/faq9.blade.php' => [
        'images' => [
            ['line' => 11, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.faq_title', 'homepage_new.faq_whats_title', 'homepage_new.faq_whats_desc', 'homepage_new.faq_write_whatsup'], 'path' => static function () { return (asset('images/faq.png')); }],
            ['line' => 45, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.faq_show_more_btn_text'], 'path' => static function () { return (asset('images/icon/load-more.png')); }],
        ],
    ],
    'partials/index_new/footer.blade.php' => [
        'images' => [
            ['line' => 7, 'alt' => 'viarcanvas', 'title' => '', 'path' => static function () { return (asset('images/logo.svg')); }],
            ['line' => 27, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.footer_phone_clean', 'header_footer_new.footer_phone', 'header_footer_new.footer_phone_clean2', 'header_footer_new.footer_phone2'], 'path' => static function () { return (asset('img/icons/phone-footer.svg')); }],
            ['line' => 34, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.footer_phone_clean', 'header_footer_new.footer_phone', 'header_footer_new.footer_phone_clean2', 'header_footer_new.footer_phone2', 'header_footer_new.footer_addr1_link'], 'path' => static function () { return (asset('img/icons/mail-footer.svg')); }],
            ['line' => 41, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.footer_phone_clean2', 'header_footer_new.footer_phone2', 'header_footer_new.footer_addr1_link', 'header_footer_new.footer_addr1', 'header_footer_new.footer_addr2_link'], 'path' => static function () { return (asset('img/icons/mail-footer.svg')); }],
            ['line' => 46, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.footer_phone2', 'header_footer_new.footer_addr1_link', 'header_footer_new.footer_addr1', 'header_footer_new.footer_addr2_link', 'header_footer_new.footer_addr2'], 'path' => static function () { return (asset('img/icons/location.svg')); }],
            ['line' => 51, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.footer_addr1_link', 'header_footer_new.footer_addr1', 'header_footer_new.footer_addr2_link', 'header_footer_new.footer_addr2', 'header_footer_new.we_in_socs'], 'path' => static function () { return (asset('img/icons/location.svg')); }],
            ['line' => 129, 'alt' => 'visa', 'title' => '', 'translations' => ['header_footer_new.footer_copyright'], 'path' => static function () { return (asset('images/icon/visa.svg')); }],
            ['line' => 130, 'alt' => 'mastercard', 'title' => '', 'translations' => ['header_footer_new.footer_copyright'], 'path' => static function () { return (asset('images/icon/master.svg')); }],
        ],
    ],
    'partials/index_new/happy3.blade.php' => [
        'images' => [
            ['line' => 6, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.you_feel_nice', 'homepage_new.you_feel_nice_1_text', 'homepage_new.you_feel_nice_2_text'], 'path' => static function () { return (asset('images/happy/happy-1.svg')); }],
            ['line' => 12, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.you_feel_nice', 'homepage_new.you_feel_nice_1_text', 'homepage_new.you_feel_nice_2_text', 'homepage_new.you_feel_nice_3_text'], 'path' => static function () { return (asset('images/happy/happy-2.svg')); }],
            ['line' => 18, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.you_feel_nice_1_text', 'homepage_new.you_feel_nice_2_text', 'homepage_new.you_feel_nice_3_text', 'homepage_new.you_feel_nice_4_text'], 'path' => static function () { return (asset('images/happy/happy-3.svg')); }],
            ['line' => 24, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.you_feel_nice_2_text', 'homepage_new.you_feel_nice_3_text', 'homepage_new.you_feel_nice_4_text'], 'path' => static function () { return (asset('images/happy/happy-4.svg')); }],
            ['line' => 38, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.you_feel_nice_4_text'], 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
            ['line' => 1, 'alt' => '', 'title' => '', 'path' => static function () { return ['https://viarcanvas.com/images/bg/happy-bg.webp', 'https://viarcanvas.com/images/bg/happy-bg.jpg']; }],
        ],
    ],
    'partials/index_new/header0.blade.php' => [
        'images' => [
            ['line' => 37, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 84, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 130, 'alt' => '', 'title' => '', 'translations' => ['settings.site_name'], 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 149, 'alt' => '', 'title' => '', 'translations' => ['settings.site_name', 'header_footer_new.header_col1_name'], 'path' => static function () { return (asset('images/logo.svg')); }],
            ['line' => 222, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.contacts', 'header_footer_new.prices', 'header_footer_new.stocks'], 'path' => static function () { return (asset('images/flag/' . app()->getLocale() . '.svg')); }],
            ['line' => 233, 'alt' => '', 'title' => '', 'binding' => ['glob' => 'images/flag/*.svg']],
        ],
    ],
    'partials/index_new/header1.blade.php' => [
        'images' => [
            ['line' => 5, 'alt' => '', 'title' => '', 'translations' => ['settings.site_name'], 'path' => static function () { return (asset('img/icons/logo.svg')); }],
            ['line' => 72, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/flag/' . app()->getLocale() . '.svg')); }],
            ['line' => 83, 'alt' => '', 'title' => '', 'binding' => ['glob' => 'images/flag/*.svg']],
        ],
    ],
    'partials/index_new/header_new_0.blade.php' => [
        'images' => [
            ['line' => 38, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 55, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['png'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 101, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 147, 'alt' => '', 'title' => '', 'translations' => ['settings.site_name'], 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 166, 'alt' => '', 'title' => '', 'translations' => ['settings.site_name', 'header_footer_new.header_col1_name'], 'path' => static function () { return (asset('images/logo.svg')); }],
            ['line' => 239, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.contacts', 'header_footer_new.prices', 'header_footer_new.stocks'], 'path' => static function () { return (asset('images/flag/' . app()->getLocale() . '.svg')); }],
            ['line' => 250, 'alt' => '', 'title' => '', 'binding' => ['glob' => 'images/flag/*.svg']],
        ],
    ],
    'partials/index_new/header_new_1.blade.php' => [
        'images' => [
            ['line' => 5, 'alt' => '', 'title' => '', 'translations' => ['settings.site_name', 'header_footer_new.header_col1_name'], 'path' => static function () { return (asset('img/icons/logo.svg')); }],
            ['line' => 73, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.stocks'], 'path' => static function () { return (asset('images/flag/' . app()->getLocale() . '.svg')); }],
            ['line' => 86, 'alt' => '', 'title' => '', 'binding' => ['glob' => 'images/flag/*.svg']],
            ['line' => 126, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['png'], 'where' => ['is_show' => 1], 'prefix' => 'storage/']],
            ['line' => 158, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['png'], 'where' => ['is_show' => 1], 'prefix' => 'storage/']],
            ['line' => 190, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['png'], 'where' => ['is_show' => 1], 'prefix' => 'storage/']],
        ],
    ],
    'partials/index_new/modals.blade.php' => [
        'images' => [
            ['line' => 16, 'alt' => 'img', 'title' => '', 'translations' => ['portrait_buy_form.popup_thanks_text1', 'portrait_buy_form.popup_thanks_text2', 'portrait_buy_form.popup_thanks_text3', 'portrait_buy_form.popup_thanks_text4'], 'path' => static function () { return (asset('images/icon/check-done.svg')); }],
            ['line' => 372, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.set_need_style'], 'path' => static function () { return (asset('images/portrait-form.png')); }],
        ],
    ],
    'partials/index_new/revs8.blade.php' => [
        'images' => [
            ['line' => 3, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
            ['line' => 12, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.gift_cart_banner_title', 'homepage_new.gift_cart_title', 'homepage_new.gift_cart_desc'], 'path' => static function () { return (asset('theme/viar/images/gift/certificate_'.Config::get('app.locale').'.png')); }],
            ['line' => 47, 'alt' => '', 'title' => '', 'binding' => ['sources' => [['model' => 'App\\Models\\OurWork', 'fields' => ['img'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']], ['model' => 'App\\Models\\Review', 'fields' => ['img'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']]]]],
            ['line' => 62, 'alt' => 'img', 'title' => '', 'binding' => ['sources' => [['model' => 'App\\Models\\OurWork', 'fields' => ['avatar'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']], ['model' => 'App\\Models\\Review', 'fields' => ['avatar'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']]]]],
            ['line' => 115, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
        ],
    ],
    'partials/index_new/slider1.blade.php' => [
        'images' => [
            ['line' => 11, 'alt' => '', 'title' => '', 'translations' => ['homepage_new.slider_title', 'homepage_new.slider_desc', 'homepage_new.slider_btn1_title'], 'binding' => ['model' => 'App\\Models\\NewhomeTopSlider', 'fields' => ['{locale}'], 'media' => []]],
            ['line' => 30, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.slider_btn1_title', 'homepage_new.slider_btn2_title', 'homepage_new.slider_image_text'], 'path' => static function () { return (asset('images/gift.png')); }],
            ['line' => 66, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'partials/index_new/tops6.blade.php' => [
        'images' => [
            ['line' => 3, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.top_sales_title', 'homepage_new.top_sales_desc'], 'path' => static function () { return (asset('images/icon/ellipse-cream.svg')); }],
            ['line' => 43, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.top_sales_btn_title', 'homepage_new.top_sales_more_btn_title', 'homepage_new.hide'], 'path' => static function () { return (asset('images/icon/load-more.png')); }],
        ],
    ],
    'partials/index_new/why7.blade.php' => [
        'images' => [
            ['line' => 46, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.why_pic_text8', 'homepage_new.why_pic_text9', 'homepage_new.why_pic_text10', 'homepage_new.why_pic_gift_text'], 'path' => static function () { return (asset('images/why-picture-girl.png')); }],
            ['line' => 50, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.why_pic_text10', 'homepage_new.why_pic_gift_text'], 'path' => static function () { return (asset('images/icon/gift.svg')); }],
        ],
    ],
    'partials/index_new/why7_form.blade.php' => [
        'images' => [
            ['line' => 65, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.loading'], 'path' => static function () { return (asset('img/icons/phone.svg')); }],
            ['line' => 84, 'alt' => 'img', 'title' => '', 'translations' => ['portrait_buy_form.loading'], 'path' => static function () { return (asset('images/pinned-photo.png')); }],
        ],
    ],
    'partials/index_new/works5.blade.php' => [
        'images' => [
            ['line' => 9, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_title', 'homepage_new.how_we_work_sub_title', 'homepage_new.how_we_work_step1_title', 'homepage_new.how_we_work_step1_desc'], 'path' => static function () { return (asset('images/work/work-1.svg')); }],
            ['line' => 19, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_step1_title', 'homepage_new.how_we_work_step1_desc', 'homepage_new.how_we_work_step2_title', 'homepage_new.how_we_work_step2_desc'], 'path' => static function () { return (asset('images/work/work-2.svg')); }],
            ['line' => 29, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_step2_title', 'homepage_new.how_we_work_step2_desc', 'homepage_new.how_we_work_step3_title', 'homepage_new.how_we_work_step3_desc', 'homepage_new.how_we_work_express'], 'path' => static function () { return (asset('images/work/work-3.svg')); }],
            ['line' => 38, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_step3_title', 'homepage_new.how_we_work_step3_desc', 'homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/work/work-4.svg')); }],
            ['line' => 42, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_step3_title', 'homepage_new.how_we_work_step3_desc', 'homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/work/work-5.svg')); }],
        ],
    ],
    'partials/inner_blog.blade.php' => [
        'images' => [
            ['line' => 57, 'alt' => '', 'title' => '', 'translations' => ['gl.share_text'], 'path' => static function () { return (asset('img/social-blog1.png')); }],
        ],
    ],
    'partials/modals.blade.php' => [
        'images' => [
            ['line' => 13, 'alt' => '', 'title' => '', 'translations' => ['gl.suc'], 'path' => static function () { return (asset('img/checkmark_circle.1.png')); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'translations' => ['gl.suc', 'gl.inv_filesize_or_ext'], 'path' => static function () { return (asset('img/checkmark_circle.1.png')); }],
            ['line' => 37, 'alt' => '', 'title' => '', 'translations' => ['gl.inv_filesize_or_ext', 'gl.inv_size'], 'path' => static function () { return (asset(env('THEME').'img/cross1.png')); }],
            ['line' => 53, 'alt' => '', 'title' => '', 'translations' => ['gl.inv_filesize_or_ext', 'gl.inv_size'], 'path' => static function () { return (asset('img/cross1.png')); }],
            ['line' => 66, 'alt' => '', 'title' => '', 'translations' => ['gl.inv_size'], 'path' => static function () { return (asset('img/checkmark_circle.1.png')); }],
            ['line' => 79, 'alt' => '', 'title' => '', 'translations' => ['gl.media_missing'], 'path' => static function () { return (asset('img/checkmark_circle.1.png')); }],
            ['line' => 92, 'alt' => '', 'title' => '', 'translations' => ['gl.media_missing'], 'path' => static function () { return (asset('img/cross1.png')); }],
            ['line' => 105, 'alt' => '', 'title' => '', 'translations' => ['gl.media_missing', 'header.reg'], 'path' => static function () { return (asset('img/checkmark_circle.1.png')); }],
            ['line' => 118, 'alt' => '', 'title' => '', 'translations' => ['header.reg', 'header.email_reg'], 'path' => static function () { return (asset('img/cross1.png')); }],
        ],
    ],
    'partials/module_pics/components.blade.php' => [
        'images' => [
            ['line' => 3, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-img.png')); }],
            ['line' => 7, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-content-img-3.png')); }],
            ['line' => 10, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon1.png')); }],
            ['line' => 14, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon2.png')); }],
            ['line' => 18, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon3.png')); }],
            ['line' => 22, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon4.png')); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon5.png')); }],
            ['line' => 30, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon6.png')); }],
            ['line' => 34, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon7.png')); }],
            ['line' => 38, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/components-item-icon8.png')); }],
        ],
    ],
    'partials/module_pics/del_time.blade.php' => [
        'images' => [
            ['line' => 6, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shipping-title-icon1.png')); }],
            ['line' => 10, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shipping-item-img1.png')); }],
            ['line' => 18, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shipping-title-icon2.png')); }],
            ['line' => 22, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shipping-item-img2.png')); }],
            ['line' => 33, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shipping-title-icon3.png')); }],
            ['line' => 39, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/sum-item-img1.png')); }],
            ['line' => 45, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/sum-item-img2.png')); }],
            ['line' => 53, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/partner-img1.png')); }],
            ['line' => 54, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/partner-img2.png')); }],
            ['line' => 60, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/shipping-title-icon4.png')); }],
            ['line' => 64, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'path' => static function () { return (asset('img/shipping-item-img3.png')); }],
        ],
    ],
    'partials/module_pics/header.blade.php' => [
        'images' => [
            ['line' => 45, 'alt' => '', 'title' => '', 'translations' => ['header.gallery'], 'path' => static function () { return (asset('img/modular-banner-img.png')); }],
            ['line' => 46, 'alt' => '', 'title' => '', 'translations' => ['header.gallery'], 'path' => static function () { return (asset('img/modular-banner-img2.png')); }],
            ['line' => 66, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon.png')); }],
            ['line' => 68, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon2.png')); }],
            ['line' => 70, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon3.png')); }],
            ['line' => 72, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon4.png')); }],
        ],
    ],
    'partials/module_pics/module_price_size.blade.php' => [
        'images' => [
            ['line' => 9, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn'], 'path' => static function () { return (asset('img/prices-img4.png')); }],
            ['line' => 11, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'path' => static function () { return (asset('img/prices-img5.png')); }],
            ['line' => 15, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'path' => static function () { return (asset('img/prices-img6.png')); }],
        ],
    ],
    'partials/module_pics/module_req.blade.php' => [
        'images' => [
            ['line' => 9, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/file-item-img1.png')); }],
            ['line' => 13, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/file-item-img2.png')); }],
            ['line' => 17, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/file-item-img3.png')); }],
            ['line' => 24, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/social-title-img.png')); }],
            ['line' => 56, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/take-item-img1.png')); }],
            ['line' => 58, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/take-item-after1.png')); }],
            ['line' => 63, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/take-item-img2.png')); }],
            ['line' => 65, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/take-item-after2.png')); }],
            ['line' => 85, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/paid-img.png')); }],
            ['line' => 88, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/paid-img2.png')); }],
            ['line' => 93, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/screen-img.png')); }],
            ['line' => 106, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasReq', 'fields' => ['req_link1_img'], 'media' => []]],
            ['line' => 111, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasReq', 'fields' => ['req_link2_img'], 'media' => []]],
            ['line' => 116, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn'], 'binding' => ['model' => 'App\\Models\\CanvasReq', 'fields' => ['req_link3_img'], 'media' => []]],
            ['line' => 121, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'binding' => ['model' => 'App\\Models\\CanvasReq', 'fields' => ['req_link4_img'], 'media' => []]],
        ],
    ],
    'partials/module_pics/module_works.blade.php' => [
        'images' => [
            ['line' => 25, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'binding' => ['model' => 'App\\Models\\ModularPicsHead', 'fields' => ['our_works'], 'media' => []]],
        ],
    ],
    'partials/module_pics/popular.blade.php' => [
        'images' => [
            ['line' => 20, 'alt' => '', 'title' => '', 'translations' => ['gl.pic_size', 'gl.price_text', 'gl.price_from_text'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]],
        ],
    ],
    'partials/module_pics/serv_qual.blade.php' => [
        'images' => [
            ['line' => 11, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img.png' )); }],
            ['line' => 15, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img2.png' )); }],
            ['line' => 19, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img3.png' )); }],
            ['line' => 23, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img4.png' )); }],
            ['line' => 27, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img5.png' )); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img6.png' )); }],
            ['line' => 37, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img7.png' )); }],
            ['line' => 43, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/service-item-img8.png' )); }],
        ],
    ],
    'partials/module_pics/tab1_form.blade.php' => [
        'images' => [
            ['line' => 12, 'alt' => '', 'title' => '', 'translations' => ['gl.choose_product_before'], 'path' => static function () { return (asset('img/download-icon.png')); }],
        ],
    ],
    'partials/module_pics/what_module.blade.php' => [
        'images' => [
            ['line' => 9, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'path' => static function () { return (asset('img/modular-item-img1.png')); }],
            ['line' => 13, 'alt' => '', 'title' => '', 'translations' => ['gl.show_btn', 'gl.hide_btn'], 'path' => static function () { return (asset('img/modular-item-img2.png')); }],
        ],
    ],
    'partials/oil-portrait.blade.php' => [
        'images' => [
            ['line' => 53, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon.png')); }],
            ['line' => 55, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon2.png')); }],
            ['line' => 57, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon3.png')); }],
            ['line' => 59, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/solial-icon4.png')); }],
            ['line' => 91, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitsHard', 'fields' => ['img1'], 'media' => []]],
            ['line' => 94, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitsHard', 'fields' => ['img2'], 'media' => []]],
            ['line' => 113, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortPrevItem', 'fields' => ['image'], 'media' => []]],
            ['line' => 13, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/oil-portrait-bg.png')); }],
        ],
    ],
    'partials/portrait_new/etc_styles.blade.php' => [
        'images' => [
            ['line' => 30, 'alt' => '', 'title' => '', 'path' => static function () { return 'images/vertical.webp'; }],
        ],
    ],
    'partials/portrait_new/form/art_decor.blade.php' => [
        'images' => [
            ['line' => 19, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step6_title'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 57, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step6_bot_desc'], 'path' => static function () { return (asset('images/prompt6.png')); }],
        ],
    ],
    'partials/portrait_new/form/canvas.blade.php' => [
        'images' => [
            ['line' => 22, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 44, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step5_bot_desc'], 'path' => static function () { return (asset('images/prompt5.png')); }],
        ],
    ],
    'partials/portrait_new/form/comments.blade.php' => [
        'images' => [
            ['line' => 51, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step_comment_bot_desc'], 'path' => static function () { return (asset('images/prompt9.png')); }],
        ],
    ],
    'partials/portrait_new/form/final.blade.php' => [
        'images' => [
            ['line' => 24, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.final_pack'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
        ],
    ],
    'partials/portrait_new/form/form.blade.php' => [
        'images' => [
            ['line' => 11, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step2_title'], 'path' => static function () { return (asset('images/form1.svg ')); }],
            ['line' => 22, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/form2.svg ')); }],
            ['line' => 33, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/form3.svg ')); }],
            ['line' => 50, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step2_bot_desc'], 'path' => static function () { return (asset('images/prompt2.png')); }],
        ],
    ],
    'partials/portrait_new/form/frames.blade.php' => [
        'images' => [
            ['line' => 53, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step7_bot_desc'], 'path' => static function () { return (asset('images/prompt7.png')); }],
        ],
    ],
    'partials/portrait_new/form/newcanvas.blade.php' => [
        'images' => [
            ['line' => 18, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step6_title'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 56, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step6_bot_desc'], 'path' => static function () { return (asset('images/prompt6.png')); }],
        ],
    ],
    'partials/portrait_new/form/persons.blade.php' => [
        'images' => [
            ['line' => 69, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step4_bot_desc'], 'path' => static function () { return (asset('images/prompt4.png')); }],
        ],
    ],
    'partials/portrait_new/form/photo.blade.php' => [
        'images' => [
            ['line' => 55, 'alt' => '', 'title' => '', 'translations' => ['portrait.form_files_loaded', 'portrait_buy_form.step1_bot_desc'], 'path' => static function () { return (asset('images/prompt1.png')); }],
        ],
    ],
    'partials/portrait_new/form/sets.blade.php' => [
        'images' => [
            ['line' => 17, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step8_bot_desc'], 'path' => static function () { return (asset('images/prompt8.png')); }],
        ],
    ],
    'partials/portrait_new/form/sizes.blade.php' => [
        'images' => [
            ['line' => 161, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step3_bot_desc'], 'path' => static function () { return (asset('images/prompt3.png')); }],
        ],
    ],
    'partials/portrait_new/h_zero.blade.php' => [
        'images' => [
            ['line' => 43, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/portrait-bg.png')); }],
        ],
    ],
    'partials/portrait_new/modals.blade.php' => [
        'images' => [
            ['line' => 337, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/portrait-form.png')); }],
        ],
    ],
    'partials/portrait_new/order_steps.blade.php' => [
        'images' => [
            ['line' => 14, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_steps_title', 'portrait.order_steps_desc', 'portrait.order_step1_title', 'portrait.order_step1_desc'], 'path' => static function () { return (asset('images/photo.png')); }],
            ['line' => 24, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_step1_title', 'portrait.order_step1_desc', 'portrait.order_step2_title', 'portrait.order_step2_desc'], 'path' => static function () { return (asset('images/conversation.png')); }],
            ['line' => 34, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_step2_title', 'portrait.order_step2_desc', 'portrait.order_step3_title', 'portrait.order_step3_desc'], 'path' => static function () { return (asset('images/portrait.png')); }],
            ['line' => 44, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_step3_title', 'portrait.order_step3_desc', 'portrait.order_step4_title', 'portrait.order_step4_desc'], 'path' => static function () { return (asset('images/canvas.png')); }],
            ['line' => 54, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_step4_title', 'portrait.order_step4_desc', 'portrait.order_step5_title', 'portrait.order_step5_desc'], 'path' => static function () { return (asset('images/delivery1.png')); }],
            ['line' => 74, 'alt' => '', 'title' => '', 'translations' => ['homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/work/work-4.svg')); }],
            ['line' => 78, 'alt' => '', 'title' => '', 'translations' => ['homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart', 'portrait.order_steps_bot_text'], 'path' => static function () { return (asset('images/work/work-5.svg')); }],
        ],
    ],
    'partials/portrait_new/sizes.blade.php' => [
        'images' => [
            ['line' => 8, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/sizes/size-bg.webp')); }],
            ['line' => 15, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/10.webp')); }],
            ['line' => 20, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/9.webp')); }],
        ],
    ],
    'partials/portrait_new/slider.blade.php' => [
        'images' => [
            ['line' => 34, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['png'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 39, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/wing.svg')); }],
            ['line' => 54, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['fotopng'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 72, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['fotopng'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 92, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/wing.svg')); }],
            ['line' => 175, 'alt' => 'Viar Image', 'title' => '', 'path' => static function () { return (asset('images/collage/gift.svg')); }],
            ['line' => 189, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'partials/portrait_new/tabs/fifth.blade.php' => [
        'images' => [
            ['line' => 5, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__inner__title'], 'path' => static function () { return (ver_asset('images/clock.png')); }],
            ['line' => 14, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__inner__title', 'portrait.tab5__inner__block1_title', 'portrait.tab5__inner__block1_text'], 'path' => static function () { return (ver_asset('images/three-days.jpg')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__inner__block1_title', 'portrait.tab5__inner__block1_text', 'portrait.tab5__inner__block2_title', 'portrait.tab5__inner__block2_text'], 'path' => static function () { return (ver_asset('images/one-day.jpg')); }],
            ['line' => 48, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__inner__block2_text', 'portrait.tab5__inner__block3_title', 'portrait.tab5__inner__block3_title_after'], 'path' => static function () { return (ver_asset('images/on-date.jpg')); }],
            ['line' => 64, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__inner__block3_title', 'portrait.tab5__inner__block3_title_after', 'portrait.tab5__delivery__title'], 'path' => static function () { return (ver_asset('images/delivery.png')); }],
            ['line' => 73, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__delivery__title', 'portrait.tab5__delivery__block1_title', 'portrait.tab5__delivery__block1_title_after'], 'path' => static function () { return (ver_asset('images/van.jpg')); }],
            ['line' => 88, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__delivery__block1_title', 'portrait.tab5__delivery__block1_title_after', 'portrait.tab5__delivery__block2_title', 'portrait.tab5__delivery__block2_title_after'], 'path' => static function () { return (ver_asset('images/on-adress.jpg')); }],
            ['line' => 103, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__delivery__block2_title', 'portrait.tab5__delivery__block2_title_after', 'portrait.tab5__delivery__block3_title', 'portrait.tab5__delivery__block3_title_after', 'portrait.tab5__delivery__after_text'], 'path' => static function () { return (ver_asset('images/abroad.jpg')); }],
            ['line' => 122, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__delivery__block3_title_after', 'portrait.tab5__delivery__after_text', 'portrait.tab5__delivery__after_block1_text'], 'path' => static function () { return (ver_asset('images/venipak.png')); }],
            ['line' => 132, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__delivery__after_block1_text', 'portrait.tab5__delivery__after_block2_text'], 'path' => static function () { return (ver_asset('images/dpd.png')); }],
            ['line' => 133, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__delivery__after_block1_text', 'portrait.tab5__delivery__after_block2_text', 'portrait.tabs_show_btn_text'], 'path' => static function () { return (ver_asset('images/omniva-logo-41B019A1E9-seeklogo.com.png')); }],
        ],
    ],
    'partials/portrait_new/tabs/first.blade.php' => [
        'images' => [
            ['line' => 6, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/about-borderB.png')); }],
        ],
    ],
    'partials/portrait_new/tabs/second.blade.php' => [
        'images' => [
            ['line' => 27, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab2__inner_title', 'portrait.tab2__inner_text', 'portrait.tab2__inner_block1_text'], 'path' => static function () { return (ver_asset('images/about-m1.png')); }],
            ['line' => 40, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab2__inner_block1_text', 'portrait.tab2__inner_block2_text'], 'path' => static function () { return (ver_asset('images/fit-1.jpg')); }],
            ['line' => 52, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab2__inner_block2_text', 'portrait.tab2__inner_block3_text'], 'path' => static function () { return (ver_asset('images/fit-2.jpg')); }],
            ['line' => 64, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab2__inner_block3_text', 'portrait.tab2__inner_mob1_hint', 'portrait.tab2__inner_mob2_hint', 'portrait.tabs_show_btn_text'], 'path' => static function () { return (ver_asset('images/fit-3.jpg')); }],
        ],
    ],
    'partials/reviews.blade.php' => [
        'images' => [
            ['line' => 7, 'alt' => '', 'title' => '', 'binding' => ['sources' => [['model' => 'App\\Models\\OurWork', 'fields' => ['avatar'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']], ['model' => 'App\\Models\\Review', 'fields' => ['avatar'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']]]]],
            ['line' => 11, 'alt' => '', 'title' => '', 'binding' => ['sources' => [['model' => 'App\\Models\\OurWork', 'fields' => ['img'], 'where' => ['active' => 1, 'orig_locale' => '{locale}']], ['model' => 'App\\Models\\Review', 'fields' => ['img'], 'where' => ['active' => 1, 'orig_locale' => '{locale}']]]]],
        ],
    ],
    'partials/simpsons/etc_styles.blade.php' => [
        'images' => [
            ['line' => 30, 'alt' => '', 'title' => '', 'path' => static function () { return 'images/vertical.webp'; }],
        ],
    ],
    'partials/simpsons/form/art_decor.blade.php' => [
        'images' => [
            ['line' => 19, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step6_title'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 56, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step6_bot_desc'], 'path' => static function () { return (asset('images/prompt6.png')); }],
        ],
    ],
    'partials/simpsons/form/canvas.blade.php' => [
        'images' => [
            ['line' => 23, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 45, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step5_bot_desc'], 'path' => static function () { return (asset('images/prompt5.png')); }],
        ],
    ],
    'partials/simpsons/form/comments.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item6.formalization-tab', 'portrait_buy_form.step_comment_ex'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format6.svg')); }],
            ['line' => 54, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step_comment_bot_desc'], 'path' => static function () { return (asset('images/prompt9.png')); }],
        ],
    ],
    'partials/simpsons/form/final.blade.php' => [
        'images' => [
            ['line' => 24, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.final_pack'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
        ],
    ],
    'partials/simpsons/form/form.blade.php' => [
        'images' => [
            ['line' => 11, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step2_title'], 'path' => static function () { return (asset('images/form1.svg ')); }],
            ['line' => 22, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/form2.svg ')); }],
            ['line' => 33, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/form3.svg ')); }],
            ['line' => 50, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step2_bot_desc'], 'path' => static function () { return (asset('images/prompt2.png')); }],
        ],
    ],
    'partials/simpsons/form/frames.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => '', 'title' => '', 'translations' => ['simpson.popup-wrapper.formalization-item5.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format5.svg')); }],
            ['line' => 56, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step7_bot_desc'], 'path' => static function () { return (asset('images/prompt7.png')); }],
        ],
    ],
    'partials/simpsons/form/persons.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item2.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format4.svg')); }],
            ['line' => 74, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step4_bot_desc'], 'path' => static function () { return (asset('images/prompt4.png')); }],
        ],
    ],
    'partials/simpsons/form/photo.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item4.formalization-tab', 'portrait_buy_form.step1_desc'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format2.svg')); }],
            ['line' => 56, 'alt' => '', 'title' => '', 'translations' => ['portrait.form_files_loaded', 'portrait_buy_form.step1_bot_desc'], 'path' => static function () { return (asset('images/prompt1.png')); }],
        ],
    ],
    'partials/simpsons/form/sets.blade.php' => [
        'images' => [
            ['line' => 17, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step8_bot_desc'], 'path' => static function () { return (asset('images/prompt8.png')); }],
        ],
    ],
    'partials/simpsons/form/sizes.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item5.formalization-tab', 'simpson.formalization-items.formalization-item5.sizesPopup-js'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format3.svg')); }],
            ['line' => 180, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step3_bot_desc'], 'path' => static function () { return (asset('images/prompt3.png')); }],
        ],
    ],
    'partials/simpsons/h_zero.blade.php' => [
        'images' => [
            ['line' => 43, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/portrait-bg.png')); }],
        ],
    ],
    'partials/simpsons/modals.blade.php' => [
        'images' => [
            ['line' => 339, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/portrait-form.png')); }],
        ],
    ],
    'partials/simpsons/order_steps.blade.php' => [
        'images' => [
            ['line' => 14, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_steps_title', 'portrait.order_steps_desc', 'portrait.order_step1_title', 'portrait.order_step1_desc'], 'path' => static function () { return (asset('images/photo.png')); }],
            ['line' => 24, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_step1_title', 'portrait.order_step1_desc', 'portrait.order_step2_title', 'portrait.order_step2_desc'], 'path' => static function () { return (asset('images/conversation.png')); }],
            ['line' => 34, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_step2_title', 'portrait.order_step2_desc', 'portrait.order_step3_title', 'portrait.order_step3_desc'], 'path' => static function () { return (asset('images/portrait.png')); }],
            ['line' => 44, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_step3_title', 'portrait.order_step3_desc', 'portrait.order_step4_title', 'portrait.order_step4_desc'], 'path' => static function () { return (asset('images/canvas.png')); }],
            ['line' => 54, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_step4_title', 'portrait.order_step4_desc', 'portrait.order_step5_title', 'portrait.order_step5_desc'], 'path' => static function () { return (asset('images/delivery1.png')); }],
            ['line' => 74, 'alt' => '', 'title' => '', 'translations' => ['homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/work/work-4.svg')); }],
            ['line' => 78, 'alt' => '', 'title' => '', 'translations' => ['homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart', 'portrait.order_steps_bot_text'], 'path' => static function () { return (asset('images/work/work-5.svg')); }],
        ],
    ],
    'partials/simpsons/sizes.blade.php' => [
        'images' => [
            ['line' => 8, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/sizes/size-bg.webp')); }],
            ['line' => 15, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/10.webp')); }],
            ['line' => 20, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/9.webp')); }],
        ],
    ],
    'partials/simpsons/slider.blade.php' => [
        'images' => [
            ['line' => 34, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['png'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 39, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/wing.svg')); }],
            ['line' => 54, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['fotopng'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 72, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['fotopng'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 92, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/wing.svg')); }],
            ['line' => 174, 'alt' => 'Viar Image', 'title' => '', 'path' => static function () { return (asset('images/collage/gift.svg')); }],
            ['line' => 188, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'partials/simpsons/tabs/fifth.blade.php' => [
        'images' => [
            ['line' => 5, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__inner__title'], 'path' => static function () { return (ver_asset('images/clock.png')); }],
            ['line' => 14, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__inner__title', 'portrait.tab5__inner__block1_title', 'portrait.tab5__inner__block1_text'], 'path' => static function () { return (ver_asset('images/three-days.jpg')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__inner__block1_title', 'portrait.tab5__inner__block1_text', 'portrait.tab5__inner__block2_title', 'portrait.tab5__inner__block2_text'], 'path' => static function () { return (ver_asset('images/one-day.jpg')); }],
            ['line' => 48, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__inner__block2_text', 'portrait.tab5__inner__block3_title', 'portrait.tab5__inner__block3_title_after'], 'path' => static function () { return (ver_asset('images/on-date.jpg')); }],
            ['line' => 64, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__inner__block3_title', 'portrait.tab5__inner__block3_title_after', 'portrait.tab5__delivery__title'], 'path' => static function () { return (ver_asset('images/delivery.png')); }],
            ['line' => 73, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__delivery__title', 'portrait.tab5__delivery__block1_title', 'portrait.tab5__delivery__block1_title_after'], 'path' => static function () { return (ver_asset('images/van.jpg')); }],
            ['line' => 88, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__delivery__block1_title', 'portrait.tab5__delivery__block1_title_after', 'portrait.tab5__delivery__block2_title', 'portrait.tab5__delivery__block2_title_after'], 'path' => static function () { return (ver_asset('images/on-adress.jpg')); }],
            ['line' => 103, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__delivery__block2_title', 'portrait.tab5__delivery__block2_title_after', 'portrait.tab5__delivery__block3_title', 'portrait.tab5__delivery__block3_title_after', 'portrait.tab5__delivery__after_text'], 'path' => static function () { return (ver_asset('images/abroad.jpg')); }],
            ['line' => 122, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__delivery__block3_title_after', 'portrait.tab5__delivery__after_text', 'portrait.tab5__delivery__after_block1_text'], 'path' => static function () { return (ver_asset('images/venipak.png')); }],
            ['line' => 132, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__delivery__after_block1_text', 'portrait.tab5__delivery__after_block2_text'], 'path' => static function () { return (ver_asset('images/dpd.png')); }],
            ['line' => 133, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__delivery__after_block1_text', 'portrait.tab5__delivery__after_block2_text', 'portrait.tabs_show_btn_text'], 'path' => static function () { return (ver_asset('images/omniva-logo-41B019A1E9-seeklogo.com.png')); }],
        ],
    ],
    'partials/simpsons/tabs/first.blade.php' => [
        'images' => [
            ['line' => 6, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/about-borderB.png')); }],
        ],
    ],
    'partials/simpsons/tabs/second.blade.php' => [
        'images' => [
            ['line' => 27, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab2__inner_title', 'portrait.tab2__inner_text', 'portrait.tab2__inner_block1_text'], 'path' => static function () { return (ver_asset('images/about-m1.png')); }],
            ['line' => 40, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab2__inner_block1_text', 'portrait.tab2__inner_block2_text'], 'path' => static function () { return (ver_asset('images/fit-1.jpg')); }],
            ['line' => 52, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab2__inner_block2_text', 'portrait.tab2__inner_block3_text'], 'path' => static function () { return (ver_asset('images/fit-2.jpg')); }],
            ['line' => 64, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab2__inner_block3_text', 'portrait.tab2__inner_mob1_hint', 'portrait.tab2__inner_mob2_hint', 'portrait.tabs_show_btn_text'], 'path' => static function () { return (ver_asset('images/fit-3.jpg')); }],
        ],
    ],
    'partials/stages_everythin.blade.php' => [
        'images' => [
            ['line' => 7, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img1.png')); }],
            ['line' => 8, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after1.png')); }],
            ['line' => 11, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer1.png')); }],
            ['line' => 17, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img2.png')); }],
            ['line' => 18, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after2.png')); }],
            ['line' => 21, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer2.png')); }],
            ['line' => 27, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img3.png')); }],
            ['line' => 28, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after3.png')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer3.png')); }],
            ['line' => 37, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img4.png')); }],
            ['line' => 38, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after4.png')); }],
            ['line' => 41, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer4.png')); }],
            ['line' => 47, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-img5.png')); }],
            ['line' => 48, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/img-after5.png')); }],
            ['line' => 51, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stages-item-nubmer5.png')); }],
            ['line' => 60, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/everything-img.png')); }],
        ],
    ],
    'partials/stocks.blade.php' => [
        'images' => [
            ['line' => 59, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/akcii_').app()->getLocale().'.png'); }],
            ['line' => 72, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]],
            ['line' => 108, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/akcii_').app()->getLocale().'.png'); }],
            ['line' => 124, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]],
            ['line' => 161, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/akcii_').app()->getLocale().'.png'); }],
            ['line' => 176, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]],
        ],
    ],
    'partials/stocks/all_big.blade.php' => [
        'images' => [
            ['line' => 10, 'alt' => '', 'title' => '', 'translations' => ['gl.dd_text', 'gl.hh_text', 'gl.mm_text', 'gl.ss_text'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]],
        ],
    ],
    'partials/stocks/header.blade.php' => [
        'images' => [
            ['line' => 5, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stocks-item-img1.png')); }],
            ['line' => 12, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stocks-item-img2.png')); }],
            ['line' => 20, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stocks-item-img3.png')); }],
            ['line' => 33, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/send-data-img.png')); }],
        ],
    ],
    'partials/stocks/module_big.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => '', 'title' => '', 'path' => static function () { return '../img/other-items-img1.png'; }],
            ['line' => 24, 'alt' => '', 'title' => '', 'path' => static function () { return '../img/other-items-img1.png'; }],
            ['line' => 44, 'alt' => '', 'title' => '', 'path' => static function () { return '../img/other-items-img1.png'; }],
        ],
    ],
    'partials/stocks/photo_big.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => '', 'title' => '', 'path' => static function () { return '../img/other-items-img2.png'; }],
            ['line' => 24, 'alt' => '', 'title' => '', 'path' => static function () { return '../img/other-items-img2.png'; }],
            ['line' => 44, 'alt' => '', 'title' => '', 'path' => static function () { return '../img/other-items-img2.png'; }],
        ],
    ],
    'partials/stocks/repr_big.blade.php' => [
        'images' => [
            ['line' => 4, 'alt' => '', 'title' => '', 'path' => static function () { return '../img/other-items-img3.png'; }],
            ['line' => 24, 'alt' => '', 'title' => '', 'path' => static function () { return '../img/other-items-img3.png'; }],
            ['line' => 44, 'alt' => '', 'title' => '', 'path' => static function () { return '../img/other-items-img3.png'; }],
        ],
    ],
    'partials/styl_paint/port_lists.blade.php' => [
        'images' => [
            ['line' => 14, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\StylPagePortObr', 'fields' => ['image']]],
        ],
    ],
    'partnership.blade.php' => [
        'images' => [
            ['line' => 49, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/sale-img.png')); }],
        ],
    ],
    'portrait_new.blade.php' => [
        'images' => [
            ['line' => 63, 'alt' => 'img', 'title' => '', 'translations' => ['portrait.tabs_title'], 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-whete.svg')); }],
            ['line' => 105, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab4_title', 'portrait.sizes__title'], 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
            ['line' => 108, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset(env('THEME').'images/sizes/union.png')); }],
            ['line' => 116, 'alt' => 'img', 'title' => '', 'translations' => ['portrait.sizes__title', 'portrait.ex_cl_works_title', 'portrait.ex_cl_works_desc'], 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
            ['line' => 188, 'alt' => '', 'title' => '', 'translations' => ['portrait.ex_video_desc', 'portrait.des_help_title', 'portrait.des_help_desc'], 'path' => static function () { return (asset(env('THEME').'images/play.svg')); }],
            ['line' => 258, 'alt' => '', 'title' => '', 'translations' => ['portrait.form_add_comment'], 'path' => static function () { return (asset(env('THEME').'img/icons/phone.svg')); }],
            ['line' => 347, 'alt' => '', 'title' => '', 'translations' => ['gl.suc'], 'path' => static function () { return (asset(env('THEME').'img/checkmark_circle.1.png')); }],
        ],
    ],
    'stocks.blade.php' => [
        'images' => [
            ['line' => 65, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/akcii_') . app()->getLocale() . '.png'); }],
            ['line' => 78, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]],
            ['line' => 117, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/akcii_') . app()->getLocale() . '.png'); }],
            ['line' => 133, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]],
            ['line' => 165, 'alt' => '', 'title' => '', 'translations' => ['gl.order_btn'], 'path' => static function () { return (asset('img/akcii_') . app()->getLocale() . '.png'); }],
            ['line' => 184, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/akcii_') . app()->getLocale() . '.png'); }],
            ['line' => 200, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]],
        ],
    ],
    'stylization-paintings.blade.php' => [
        'images' => [
            ['line' => 79, 'alt' => '', 'title' => '', 'translations' => ['gl.read_more'], 'path' => static function () { return (asset('img/front_') . app()->getLocale() . '.png'); }],
            ['line' => 82, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/back_') . app()->getLocale() . '.png'); }],
            ['line' => 22, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/stylization-paintings-bg.png')); }],
        ],
    ],
    'theme/viar/account/account.blade.php' => [
        'images' => [
            ['line' => 45, 'alt' => 'Viar Cabinet Resume', 'title' => '', 'translations' => ['account_new.btn.change', 'account_new.main.address', 'account_new.main.phone'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/resume.svg'; }],
        ],
    ],
    'theme/viar/account/basket_img.blade.php' => [
        'images' => [
            ['line' => 5, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage']]],
            ['line' => 26, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage']]],
            ['line' => 40, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage']]],
            ['line' => 45, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage']]],
            ['line' => 49, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage']]],
            ['line' => 59, 'alt' => 'Gift', 'title' => '', 'path' => static function () { return (asset('img/benefits-img1.png')); }],
            ['line' => 66, 'alt' => '', 'title' => '', 'path' => static function () { return (order_image_placeholder()); }],
        ],
    ],
    'theme/viar/account/o_chat_img.blade.php' => [
        'images' => [
            ['line' => 71, 'alt' => '', 'title' => '', 'path' => static function () { return (order_image_placeholder()); }],
            ['line' => 73, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/psd.svg'; }],
            ['line' => 75, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/pdf.svg'; }],
            ['line' => 77, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\OrderPainterImages', 'preferred_fields' => ['small_image', 'image'], 'order_image' => true, 'exclude_extensions' => ['pdf', 'psd']]],
        ],
    ],
    'theme/viar/account/o_chat_img_copy.blade.php' => [
        'images' => [
            ['line' => 23, 'alt' => '', 'title' => '', 'path' => static function () { return (order_image_placeholder()); }],
            ['line' => 25, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/psd.svg'; }],
            ['line' => 27, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/pdf.svg'; }],
            ['line' => 29, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['painter_images', 'painter_sketch_images'], 'csv' => true, 'order_image' => true, 'exclude_extensions' => ['pdf', 'psd']]],
        ],
    ],
    'theme/viar/account/o_client_chat.blade.php' => [
        'images' => [
            ['line' => 155, 'alt' => '', 'title' => '', 'translations' => ['account_new.orders.you_images', 'account_new.btn.send'], 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['client_images'], 'csv' => true, 'order_image' => true]],
        ],
    ],
    'theme/viar/account/o_painter_chat.blade.php' => [
        'images' => [
            ['line' => 100, 'alt' => '', 'title' => '', 'translations' => ['account_new.orders.add_client_files'], 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['client_images'], 'csv' => true, 'order_image' => true]],
        ],
    ],
    'theme/viar/account/o_painter_sketch.blade.php' => [
        'images' => [
            ['line' => 30, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/psd.svg'; }],
            ['line' => 32, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/pdf.svg'; }],
            ['line' => 34, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'fields' => ['painter_sketch_images'], 'csv' => true, 'order_image' => true, 'exclude_extensions' => ['pdf', 'psd']]],
            ['line' => 158, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/image-preview.png'; }],
            ['line' => 162, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/image-preview.png'; }],
        ],
    ],
    'theme/viar/account/order.blade.php' => [
        'images' => [
            ['line' => 179, 'alt' => '', 'title' => '', 'translations' => ['account.index19'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/work-process.svg'; }],
            ['line' => 491, 'alt' => '', 'title' => '', 'translations' => ['account_new.orders.user_status.watching', 'account_new.orders.user_status.print_text'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/icon-danger.svg'; }],
            ['line' => 509, 'alt' => '', 'title' => '', 'translations' => ['account_new.orders.user_status.watching', 'account_new.orders.user_status.print_text', 'account_new.orders.user_status.pegging'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/icon-danger.svg'; }],
            ['line' => 526, 'alt' => '', 'title' => '', 'translations' => ['account_new.orders.user_status.print_text', 'account_new.orders.user_status.pegging', 'account_new.orders.user_status.picture', 'account_new.orders.user_status.sketch'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/icon-danger.svg'; }],
            ['line' => 531, 'alt' => '', 'title' => '', 'translations' => ['account_new.orders.user_status.pegging', 'account_new.orders.user_status.picture', 'account_new.orders.user_status.sketch'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/icon-danger.svg'; }],
            ['line' => 536, 'alt' => '', 'title' => '', 'translations' => ['account_new.orders.user_status.picture', 'account_new.orders.user_status.sketch'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/icon-danger.svg'; }],
        ],
    ],
    'theme/viar/account/order_payment.blade.php' => [
        'images' => [
            ['line' => 231, 'alt' => '', 'title' => '', 'translations' => ['account_new.pay_order_hint', 'account.index15', 'account_new.payment_status'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/icon-danger.svg'; }],
            ['line' => 272, 'alt' => '', 'title' => '', 'translations' => ['account_new.back_to_orders', 'cart_new.general_total_amount'], 'path' => static function () { return array_map(static function ($method) { return asset(env('THEME') . $method['img']); }, array_filter(\App\Http\Controllers\Libwebtopay\WebToPay::PAYSERA_METHODS_MAP, static function ($method) { return !empty($method['img']); })); }],
            ['line' => 319, 'alt' => 'Viar Cabinet Order Payment', 'title' => '', 'translations' => ['cart_new.general_production', 'account_new.back_to_orders'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/credit-card.svg'; }],
        ],
    ],
    'theme/viar/account/orders.blade.php' => [
        'images' => [
            ['line' => 99, 'alt' => 'Viar Cabinet Peding Orders', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/free-icon-order-delivery.svg'; }],
        ],
    ],
    'theme/viar/account/orders_success.blade.php' => [
        'images' => [
            ['line' => 24, 'alt' => 'Viar Cabinet Peding Orders', 'title' => '', 'translations' => ['account_new.orders_success.title', 'account_new.orders_success.count', 'account_new.btn.logout'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/free-icon-order-delivery.svg'; }],
        ],
    ],
    'theme/viar/account/paid.blade.php' => [
        'images' => [
            ['line' => 299, 'alt' => 'Viar Cabinet Peding Orders', 'title' => '', 'translations' => ['account_new.orders.chat.add_comment'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/credit-card.svg'; }],
        ],
    ],
    'theme/viar/account/settings.blade.php' => [
        'images' => [
            ['line' => 215, 'alt' => 'Viar Cabinet Peding Orders', 'title' => '', 'translations' => ['account.index48', 'account_new.btn.save_settings'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/settings.svg'; }],
        ],
    ],
    'theme/viar/account/special_offers.blade.php' => [
        'images' => [
            ['line' => 17, 'alt' => 'Viar', 'title' => '', 'translations' => ['account_new.special_offers.title'], 'binding' => ['model' => 'App\\Models\\AMailTopSale', 'fields' => ['image'], 'media' => []]],
        ],
    ],
    'theme/viar/account/stocks.blade.php' => [
        'images' => [
            ['line' => 65, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_3_1', 'stock.text_3_2', 'stock.text_3_3', 'stock.text_3_4', 'stock.text_3_5'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/stock/sm1.jpg'; }],
            ['line' => 101, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_4_1', 'stock.text_4_2', 'stock.text_4_3', 'stock.text_4_4', 'stock.text_4_5'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/stock/sm2.jpg'; }],
            ['line' => 145, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_5_1', 'stock.text_5_2', 'stock.text_5_3', 'stock.text_5_4', 'stock.text_5_5'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/stock/sm3.jpg'; }],
            ['line' => 190, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_6_3', 'stock.text_6_4', 'stock.text_6_5'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/b1.jpg'; }],
            ['line' => 193, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_6_4', 'stock.text_6_5'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/v1.svg'; }],
            ['line' => 206, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_6_5', 'stock.text_6_6', 'stock.text_6_7', 'stock.text_6_8'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/b2.png'; }],
            ['line' => 259, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_7_1', 'stock.text_7_2', 'stock.text_7_3'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/b3.png'; }],
            ['line' => 262, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_7_2', 'stock.text_7_3'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/v2.svg'; }],
            ['line' => 278, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_7_2', 'stock.text_7_3', 'stock.text_7_4', 'stock.modal_40_60_button'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/b4.png'; }],
            ['line' => 315, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_8_1', 'stock.text_8_2', 'stock.modal_3_1_podarok_button'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/b5.png'; }],
            ['line' => 319, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_8_2', 'stock.modal_3_1_podarok_button'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/v3.svg'; }],
            ['line' => 354, 'alt' => 'Viar Cabinet Peding Orders', 'title' => '', 'translations' => ['stock.modal_3_1_podarok_button'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/gift-card.svg'; }],
        ],
    ],
    'theme/viar/account/unpaid.blade.php' => [
        'images' => [
            ['line' => 293, 'alt' => 'Viar Cabinet Peding Orders', 'title' => '', 'translations' => ['account_new.orders.chat.add_comment'], 'path' => static function () { return (asset(config('theme.current') . '/images')) . '/cabinet/credit-card.svg'; }],
        ],
    ],
    'theme/viar/blog/blog.blade.php' => [
        'images' => [
            ['line' => 160, 'alt' => 'img', 'title' => '', 'translations' => ['blog.new'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg'; }],
            ['line' => 403, 'alt' => '', 'title' => '', 'translations' => ['blog.stories'], 'binding' => ['model' => 'App\\Models\\BlogPost', 'fields' => ['image_user'], 'where' => ['is_stories' => 1, 'status' => 'published']]],
            ['line' => 558, 'alt' => 'img', 'title' => '', 'translations' => ['blog.our_social_networks'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg'; }],
        ],
    ],
    'theme/viar/cart/cart_item_image.blade.php' => [
        'images' => [
            ['line' => 14, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage', 'orig_images'], 'orig_slice' => [0, 1]]],
            ['line' => 21, 'alt' => '', 'title' => '', 'translations' => ['cart_new.general_change'], 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage', 'orig_images'], 'orig_slice' => [0, 1]]],
            ['line' => 40, 'alt' => '', 'title' => '', 'translations' => ['cart_new.general_change'], 'path' => static function () { return (asset(env('THEME') . 'images/gift/cart')) . '/' . (Config::get('app.locale')) . '.png'; }],
            ['line' => 59, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage', 'orig_images'], 'orig_slice' => [0, 1]]],
            ['line' => 65, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage', 'orig_images'], 'orig_slice' => [0, 1]]],
            ['line' => 73, 'alt' => '', 'title' => '', 'translations' => ['cart_new.general_change'], 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage', 'orig_images'], 'orig_slice' => [0, 1]]],
            ['line' => 89, 'alt' => '', 'title' => '', 'translations' => ['cart_new.general_change'], 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage', 'orig_images'], 'orig_slice' => [0, 1]]],
            ['line' => 96, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage', 'orig_images'], 'orig_slice' => [0, 1]]],
            ['line' => 119, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage', 'orig_images'], 'orig_slice' => [0, 1]]],
            ['line' => 127, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage', 'orig_images'], 'orig_slice' => [0, 1]]],
            ['line' => 152, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage', 'orig_images'], 'orig_slice' => [0, 1]]],
            ['line' => 159, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['activeImage', 'savedImage', 'orig_images'], 'orig_slice' => [0, 1]]],
        ],
    ],
    'theme/viar/cart/cart_popup.blade.php' => [
        'images' => [
            ['line' => 16, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'img/cart/arrow-img.svg')); }],
            ['line' => 24, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'img/cart/painting.svg')); }],
            ['line' => 36, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'img/cart/painting.svg')); }],
        ],
    ],
    'theme/viar/cart/delivery.blade.php' => [
        'images' => [
            ['line' => 75, 'alt' => '', 'title' => '', 'translations' => ['cart_new.step_3_delivery_in'], 'path' => static function () { return '/images/icon/check-circle.svg'; }],
            ['line' => 94, 'alt' => '', 'title' => '', 'translations' => ['cart_new.step_3_delivery_to_your_address_by_courier'], 'path' => static function () { return '/images/icon/check-circle.svg'; }],
            ['line' => 122, 'alt' => '', 'title' => '', 'translations' => ['cart_new.step_3_delivery_to_pick-up_point'], 'path' => static function () { return '/images/icon/check-circle.svg'; }],
            ['line' => 198, 'alt' => '', 'title' => '', 'translations' => ['cart_new.step_3_pick_up_at_viar_workshop', 'cart_new.step_3_city'], 'path' => static function () { return '/images/icon/check-circle.svg'; }],
        ],
    ],
    'theme/viar/cart/modals/recommendation_modal.blade.php' => [
        'images' => [
            ['line' => 24, 'alt' => '', 'title' => '', 'translations' => ['cart_new.no_image'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'where' => ['active' => 1]]],
        ],
    ],
    'theme/viar/cart/recommendations.blade.php' => [
        'images' => [
            ['line' => 34, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'where' => ['active' => 1]]],
            ['line' => 73, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_foto_text3', 'cart_new.no_image'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'where' => ['active' => 1]]],
            ['line' => 160, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env("THEME").'images/bg/fon.png')); }],
        ],
    ],
    'theme/viar/cart/sidebar.blade.php' => [
        'images' => [
            ['line' => 333, 'alt' => '', 'title' => '', 'translations' => ['cart_new.step_4_agreement', 'cart_new.step_4_personal-data-text1', 'cart_new.step_4_personal-data-text2', 'cart_new.general_production', 'gl.standart', 'gl.express'], 'path' => static function () { return (asset(env('THEME') . 'img/cart/clock-orange.svg')); }],
        ],
    ],
    'theme/viar/cart/step1.blade.php' => [
        'images' => [
            ['line' => 99, 'alt' => '', 'title' => '', 'translations' => ['cart_new.general_together', 'cart_new.general_change'], 'binding' => ['model' => 'App\\Models\\Orders', 'json_images' => ['items'], 'image_keys' => ['orig_images'], 'orig_slice' => [1, null], 'raw_asset' => true]],
        ],
    ],
    'theme/viar/cart/step2_data.blade.php' => [
        'images' => [
            ['line' => 47, 'alt' => 'img', 'title' => '', 'translations' => ['cart_new.step_2_phone_number'], 'binding' => ['glob' => 'images/flag/*.svg']],
            ['line' => 52, 'alt' => 'img', 'title' => '', 'translations' => ['cart_new.step_2_phone_number'], 'binding' => ['glob' => 'images/flag/*.svg']],
            ['line' => 64, 'alt' => 'img', 'title' => '', 'binding' => ['glob' => 'images/flag/*.svg']],
            ['line' => 94, 'alt' => 'img', 'title' => '', 'binding' => ['glob' => 'images/flag/*.svg']],
            ['line' => 107, 'alt' => 'img', 'title' => '', 'binding' => ['glob' => 'images/flag/*.svg']],
        ],
    ],
    'theme/viar/cart/step3_delivery.blade.php' => [
        'images' => [
            ['line' => 491, 'alt' => '', 'title' => '', 'translations' => ['cart_new.step_3_your_comment_on_the_order', 'cart_new.step_3_details', 'cart_new.step_3_your_comment'], 'path' => static function () { return (asset(env('THEME') . 'img/cart/comment.svg')); }],
        ],
    ],
    'theme/viar/cart/step4_payment.blade.php' => [
        'images' => [
            ['line' => 58, 'alt' => '', 'title' => '', 'path' => static function () { return array_map(static function ($method) { return asset(env('THEME') . $method['img']); }, array_filter(\App\Http\Controllers\Libwebtopay\WebToPay::PAYSERA_METHODS_MAP, static function ($method) { return !empty($method['img']); })); }],
            ['line' => 75, 'alt' => '', 'title' => '', 'translations' => ['cart_new.step_4_payment_by_bank_transfer'], 'path' => static function () { return (asset(env('THEME') . 'img/icons/PayPal.svg')); }],
            ['line' => 103, 'alt' => '', 'title' => '', 'translations' => ['cart_new.prepayment', 'cart_new.general_return'], 'path' => static function () { return (asset(env('THEME') . 'img/cart/prepaid2.png')); }],
        ],
    ],
    'theme/viar/layouts/app.blade.php' => [
        'images' => [
            ['line' => 419, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.header.top_sale'], 'path' => static function () { return (asset(env('THEME').'images/sale.webp')); }],
            ['line' => 532, 'alt' => 'loading', 'title' => '', 'path' => static function () { return (asset('img/loading.gif')); }],
            ['line' => 665, 'alt' => '', 'title' => '', 'translations' => ['gl.suc', 'gl.inv_filesize_or_ext'], 'path' => static function () { return (asset(env('THEME').'img/checkmark_circle.1.png')); }],
            ['line' => 676, 'alt' => '', 'title' => '', 'translations' => ['gl.suc', 'gl.inv_filesize_or_ext'], 'path' => static function () { return (asset(env('THEME').'img/cross1.png')); }],
        ],
    ],
    'theme/viar/modals/popup_add_to_cart.blade.php' => [
        'images' => [
            ['line' => 16, 'alt' => '', 'title' => '', 'translations' => ['canvas.modal_btn1', 'canvas.modal_btn2'], 'path' => static function () { return (asset('images/canvas/canvas-popup.png')); }],
        ],
    ],
    'theme/viar/modals/target-box.blade.php' => [
        'images' => [
            ['line' => 81, 'alt' => '', 'title' => '', 'translations' => ['popup.target-box_1_btn', 'popup.target-box_2_text1'], 'path' => static function () { return (asset('images/canvas/canvas.png')); }],
            ['line' => 169, 'alt' => '', 'title' => '', 'translations' => ['popup.target-box_2_text6', 'popup.target-box_2_btn', 'portrait_buy_form.setting_whatsapp_phone_href', 'popup.target-box_3_btn', 'popup.popup-why_title', 'popup.popup-why_subtitle'], 'path' => static function () { return 'https://viarcanvas.com/images/premium-icon-whatsapp.svg'; }],
            ['line' => 201, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.setting_whatsapp_phone_href', 'popup.target-box_3_btn'], 'path' => static function () { return (asset(env('THEME').'img/p-why.svg')); }],
            ['line' => 205, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.setting_whatsapp_phone_href', 'popup.target-box_3_btn', 'popup.popup-callback_title'], 'path' => static function () { return 'https://viarcanvas.com/images/premium-icon-whatsapp.svg'; }],
            ['line' => 229, 'alt' => '', 'title' => '', 'translations' => ['popup.popup-callback_title', 'popup.popup-callback_subtitle', 'popup.popup-callback_content_phone', 'homepage_new.send', 'portrait_buy_form.setting_whatsapp_phone_href', 'popup.target-box_3_btn'], 'path' => static function () { return (asset('img/icons/phone.svg')); }],
            ['line' => 236, 'alt' => '', 'title' => '', 'translations' => ['homepage_new.send', 'portrait_buy_form.setting_whatsapp_phone_href', 'popup.target-box_3_btn'], 'path' => static function () { return 'https://viarcanvas.com/images/premium-icon-whatsapp.svg'; }],
        ],
    ],
    'theme/viar/pages/about/index.blade.php' => [
        'images' => [
            ['line' => 138, 'alt' => '', 'title' => '', 'translations' => ['about.text_5_3', 'about.text_6_1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/play.svg'; }],
            ['line' => 211, 'alt' => 'Viar', 'title' => '', 'translations' => ['about.text_6_2', 'about.text_7_1'], 'path' => static function () { return (asset('img')) . '/icons/logo.svg'; }],
            ['line' => 254, 'alt' => '', 'title' => '', 'translations' => ['about.text_8_1'], 'binding' => ['model' => 'App\\Models\\OurWorkingProcess', 'fields' => ['photo']]],
            ['line' => 270, 'alt' => 'img', 'title' => '', 'translations' => ['about.text_8_1', 'about.text_8_2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/ellipse-whete.svg'; }],
            ['line' => 290, 'alt' => '', 'title' => '', 'translations' => ['about.text_8_3', 'about.text_8_4', 'about.text_8_5'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/w1.jpg'; }],
            ['line' => 312, 'alt' => '', 'title' => '', 'translations' => ['about.text_8_6', 'about.text_8_7', 'about.text_8_8'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/w2.jpg'; }],
            ['line' => 334, 'alt' => '', 'title' => '', 'translations' => ['about.text_8_9', 'about.text_8_10', 'about.text_8_11'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/w3.jpg'; }],
            ['line' => 356, 'alt' => '', 'title' => '', 'translations' => ['about.text_8_12', 'about.text_8_13', 'about.text_8_14', 'about.text_8_15'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/w4.jpg'; }],
            ['line' => 43, 'alt' => '', 'title' => '', 'path' => static function () { return array_values(array_filter(\Illuminate\Support\Arr::only(site_image('about_slide', env('THEME').'images/contacts/about-bg.jpg'), ['src', 'src_webp']))); }],
            ['line' => 48, 'alt' => '', 'title' => '', 'path' => static function () { return array_values(array_filter(\Illuminate\Support\Arr::only(site_image('about_slide', env('THEME').'images/contacts/about-bg.jpg'), ['src', 'src_webp']))); }],
            ['line' => 134, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\Page', 'fields' => ['video_img'], 'where' => ['url' => '/about']]],
        ],
    ],
    'theme/viar/pages/canvas.blade.php' => [
        'images' => [
            ['line' => 44, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
            ['line' => 49, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/sizes/union.png')); }],
            ['line' => 59, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
            ['line' => 64, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
        ],
    ],
    'theme/viar/pages/canvas/also_like_examples.blade.php' => [
        'images' => [
            ['line' => 32, 'alt' => '', 'title' => '', 'path' => static function () { return 'images/vertical.webp'; }],
        ],
    ],
    'theme/viar/pages/canvas/composition.blade.php' => [
        'images' => [
            ['line' => 16, 'alt' => '', 'title' => '', 'translations' => ['canvas.comp_title', 'canvas.comp_desc', 'canvas.comp_step1_title'], 'path' => static function () { return (asset('images/canvas/composition.jpg')); }],
            ['line' => 72, 'alt' => 'img', 'title' => '', 'translations' => ['canvas.enter_num'], 'path' => static function () { return (asset('images/flag/lv.svg')); }],
            ['line' => 77, 'alt' => 'img', 'title' => '', 'translations' => ['canvas.enter_num'], 'binding' => ['glob' => 'images/flag/*.svg']],
        ],
    ],
    'theme/viar/pages/canvas/form/canvas.blade.php' => [
        'images' => [
            ['line' => 13, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
        ],
    ],
    'theme/viar/pages/canvas/form/final.blade.php' => [
        'images' => [
            ['line' => 25, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.final_pack'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
        ],
    ],
    'theme/viar/pages/canvas/form/step1.blade.php' => [
        'images' => [
            ['line' => 12, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step1_title', 'portrait_buy_form.step1_desc', 'homepage_new.load_photo', 'homepage_new.pree_to_add_photo'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 48, 'alt' => '', 'title' => '', 'translations' => ['portrait.form_files_loaded', 'canvas.form_step1_need_imp_photo'], 'path' => static function () { return (asset('images/canvas/additional.svg')); }],
            ['line' => 87, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 121, 'alt' => '', 'title' => '', 'translations' => ['canvas.form_step1_tarif_1'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 132, 'alt' => '', 'title' => '', 'translations' => ['canvas.form_step1_tarif_1', 'canvas.form_step1_tarif_2'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 143, 'alt' => '', 'title' => '', 'translations' => ['canvas.form_step1_tarif_2', 'canvas.form_step1_tarif_3'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
        ],
    ],
    'theme/viar/pages/canvas/form/step2.blade.php' => [
        'images' => [
            ['line' => 71, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/form1.svg')); }],
            ['line' => 83, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/form2.svg')); }],
            ['line' => 95, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/form3.svg')); }],
            ['line' => 107, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/form5.svg')); }],
        ],
    ],
    'theme/viar/pages/canvas/form/step4.blade.php' => [
        'images' => [
            ['line' => 24, 'alt' => '', 'title' => '', 'translations' => ['canvas.form_print'], 'path' => static function () { return (asset('images/canvas/execution-item2.svg')); }],
            ['line' => 45, 'alt' => '', 'title' => '', 'translations' => ['canvas.form_print_oil'], 'path' => static function () { return (asset('images/canvas/execution-item1.svg')); }],
        ],
    ],
    'theme/viar/pages/canvas/form/step4_show.blade.php' => [
        'images' => [
            ['line' => 14, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
        ],
    ],
    'theme/viar/pages/canvas/form/step5.blade.php' => [
        'images' => [
            ['line' => 15, 'alt' => '', 'title' => '', 'translations' => ['canvas.form_step5_title'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 38, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 49, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 61, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
        ],
    ],
    'theme/viar/pages/canvas/form/tab2.blade.php' => [
        'images' => [
            ['line' => 35, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio1.jpg')); }],
            ['line' => 67, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio2.jpg')); }],
            ['line' => 99, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio3.jpg')); }],
            ['line' => 131, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio4.jpg')); }],
            ['line' => 163, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_interer_text2'], 'path' => static function () { return (asset('images/canvas/interio5.jpg')); }],
            ['line' => 323, 'alt' => 'Белый', 'title' => 'Белый', 'translations' => ['collage_new.z7_generator_ramma_text4'], 'path' => static function () { return (asset('images/canvas/rama1.jpg')); }],
            ['line' => 355, 'alt' => 'Белый', 'title' => 'Белый', 'translations' => ['collage_new.z7_generator_ramma_text5'], 'path' => static function () { return (asset('images/canvas/rama2.jpg')); }],
        ],
    ],
    'theme/viar/pages/canvas/header.blade.php' => [
        'images' => [
            ['line' => 31, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasNew', 'fields' => ['canv_head_desk_img'], 'media' => [], 'prefix' => '', 'image_original' => true]],
        ],
    ],
    'theme/viar/pages/canvas/modals/canv_form.blade.php' => [
        'images' => [
            ['line' => 167, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/portrait-form.png')); }],
        ],
    ],
    'theme/viar/pages/canvas/photo_work.blade.php' => [
        'images' => [
            ['line' => 185, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_work_before', 'canvas.photo_work_after'], 'binding' => ['model' => 'App\\Models\\CanvasNew', 'fields' => [], 'media' => ['photo_work_optimal_after']]],
            ['line' => 267, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasNew', 'fields' => [], 'media' => ['photo_work_premium']]],
            ['line' => 280, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_work_before', 'canvas.photo_work_after'], 'binding' => ['model' => 'App\\Models\\CanvasNew', 'fields' => [], 'media' => ['photo_work_premium_after']]],
        ],
    ],
    'theme/viar/pages/canvas/sizes.blade.php' => [
        'images' => [
            ['line' => 2, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/union.png')); }],
            ['line' => 12, 'alt' => 'img', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/size-bg.jpg')); }],
            ['line' => 19, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/10.webp')); }],
            ['line' => 24, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/9.webp')); }],
            ['line' => 101, 'alt' => 'img', 'title' => '', 'translations' => ['gl.cm'], 'path' => static function () { return (asset('images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'theme/viar/pages/canvas/slider.blade.php' => [
        'images' => [
            ['line' => 28, 'alt' => 'an avif image', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasSlider', 'fields' => ['png'], 'media' => []]],
            ['line' => 149, 'alt' => 'Viar Image', 'title' => '', 'path' => static function () { return (asset('images/collage/gift.svg')); }],
            ['line' => 207, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'theme/viar/pages/canvas/steps.blade.php' => [
        'images' => [
            ['line' => 32, 'alt' => '', 'title' => '', 'translations' => ['canvas.order_steps_title', 'canvas.order_steps_desc', 'canvas.order_step1_title', 'canvas.order_step1_desc'], 'path' => static function () { return (asset('images/photo.png')); }],
            ['line' => 46, 'alt' => '', 'title' => '', 'translations' => ['canvas.order_step1_title', 'canvas.order_step1_desc', 'canvas.order_step2_title', 'canvas.order_step2_desc'], 'path' => static function () { return (asset('images/conversation.png')); }],
            ['line' => 60, 'alt' => '', 'title' => '', 'translations' => ['canvas.order_step2_title', 'canvas.order_step2_desc', 'canvas.order_step3_title', 'canvas.order_step3_desc'], 'path' => static function () { return (asset('images/portrait.png')); }],
            ['line' => 74, 'alt' => '', 'title' => '', 'translations' => ['canvas.order_step3_title', 'canvas.order_step3_desc', 'canvas.order_step4_title', 'canvas.order_step4_desc'], 'path' => static function () { return (asset('images/canvas.png')); }],
            ['line' => 88, 'alt' => '', 'title' => '', 'translations' => ['canvas.order_step4_title', 'canvas.order_step4_desc', 'canvas.order_step5_title', 'canvas.order_step5_desc'], 'path' => static function () { return (asset('images/delivery1.png')); }],
            ['line' => 109, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/work/work-4.svg')); }],
            ['line' => 113, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/work/work-5.svg')); }],
            ['line' => 145, 'alt' => '', 'title' => '', 'translations' => ['portrait.form_add_comment', 'portrait.form_photo'], 'path' => static function () { return (asset('img/icons/phone.svg')); }],
            ['line' => 211, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_title'], 'path' => static function () { return site_image('canvas_advantages', 'images/canvas/advantages.png')['src']; }],
            ['line' => 230, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_title', 'canvas.photo_sec_step1', 'canvas.photo_sec_step2', 'canvas.photo_sec_step3'], 'path' => static function () { return (asset('images/canvas/tree.svg')); }],
            ['line' => 234, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_title', 'canvas.photo_sec_step1', 'canvas.photo_sec_step2', 'canvas.photo_sec_step3', 'canvas.photo_sec_step4'], 'path' => static function () { return (asset('images/canvas/leaf.svg')); }],
            ['line' => 238, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_title', 'canvas.photo_sec_step1', 'canvas.photo_sec_step2', 'canvas.photo_sec_step3', 'canvas.photo_sec_step4', 'canvas.photo_sec_step5'], 'path' => static function () { return (asset('images/canvas/brush.svg')); }],
            ['line' => 242, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_step1', 'canvas.photo_sec_step2', 'canvas.photo_sec_step3', 'canvas.photo_sec_step4', 'canvas.photo_sec_step5', 'canvas.photo_sec_step6'], 'path' => static function () { return (asset('images/canvas/shield.svg')); }],
            ['line' => 246, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_step2', 'canvas.photo_sec_step3', 'canvas.photo_sec_step4', 'canvas.photo_sec_step5', 'canvas.photo_sec_step6', 'canvas.photo_sec_step7'], 'path' => static function () { return (asset('images/canvas/picture.svg')); }],
            ['line' => 250, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_step3', 'canvas.photo_sec_step4', 'canvas.photo_sec_step5', 'canvas.photo_sec_step6', 'canvas.photo_sec_step7'], 'path' => static function () { return (asset('images/canvas/clock.svg')); }],
            ['line' => 254, 'alt' => '', 'title' => '', 'translations' => ['canvas.photo_sec_step4', 'canvas.photo_sec_step5', 'canvas.photo_sec_step6', 'canvas.photo_sec_step7'], 'path' => static function () { return (asset('images/canvas/package.svg')); }],
        ],
    ],
    'theme/viar/pages/canvas/tabs/fifth.blade.php' => [
        'images' => [
            ['line' => 33, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab5__inner__title'], 'path' => static function () { return (ver_asset('images/clock.png')); }],
            ['line' => 104, 'alt' => '', 'title' => '', 'translations' => ['canvas.tab5__inner__block3_title', 'canvas.tab5__inner__block3_title_after', 'canvas.tab5__delivery__title'], 'path' => static function () { return (ver_asset('images/delivery.png')); }],
        ],
    ],
    'theme/viar/pages/collage/advantage_screen.blade.php' => [
        'images' => [
            ['line' => 22, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['collage_new.z0_advantage_screen_subtitle'], 'binding' => ['model' => 'App\\Models\\ACollageAdvantageScreen', 'fields' => ['image'], 'media' => [], 'prefix' => '']],
        ],
    ],
    'theme/viar/pages/collage/doubt_screen.blade.php' => [
        'images' => [
            ['line' => 14, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['collage_new.z4_doubt_screen_title', 'collage_new.z4_doubt_screen_question', 'collage_new.z4_doubt_screen_question_text', 'collage_new.z4_doubt_screen_question_price'], 'path' => static function () { return (asset('images/collage/tel.svg')); }],
            ['line' => 39, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['collage_new.z4_doubt_screen_question_text2', 'collage_new.z4_doubt_screen_btn', 'collage_new.z4_doubt_screen_express_text1', 'collage_new.z4_doubt_screen_express_text2'], 'path' => static function () { return (asset('images/collage/express.svg')); }],
            ['line' => 43, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['collage_new.z4_doubt_screen_btn', 'collage_new.z4_doubt_screen_express_text1', 'collage_new.z4_doubt_screen_express_text2'], 'path' => static function () { return (asset('images/collage/gift.svg')); }],
            ['line' => 51, 'alt' => 'img', 'title' => '', 'translations' => ['collage_new.z4_doubt_screen_express_text1', 'collage_new.z4_doubt_screen_express_text2'], 'path' => static function () { return (asset('images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'theme/viar/pages/collage/generator.blade.php' => [
        'images' => [
            ['line' => 11, 'alt' => 'img', 'title' => '', 'translations' => ['collage_new.z7_generator_title'], 'path' => static function () { return (asset(env('THEME') . 'images/icon/ellipse-black.svg')); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_title', 'collage_new.z7_generator_subtitle'], 'path' => static function () { return (asset('images/collage/clg.svg')); }],
            ['line' => 56, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc3'], 'path' => static function () { return (asset('images/collage/ctc4.svg')); }],
            ['line' => 63, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc3', 'collage_new.z7_generator_ctc1'], 'path' => static function () { return (asset('images/collage/ctc1.svg')); }],
            ['line' => 70, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc1', 'collage_new.z7_generator_ctc2'], 'path' => static function () { return (asset('images/collage/ctc2.svg')); }],
            ['line' => 77, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc2', 'collage_new.z7_generator_ctc4'], 'path' => static function () { return (asset('images/collage/ctc3.svg')); }],
            ['line' => 84, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc4', 'collage_new.z7_generator_ctc5'], 'path' => static function () { return (asset('images/collage/ctc5.svg')); }],
            ['line' => 91, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc5', 'collage_new.z7_generator_ctc6'], 'path' => static function () { return (asset('images/collage/ctc6.svg')); }],
            ['line' => 98, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_ctc6', 'collage_new.z7_generator_ctc7'], 'path' => static function () { return (asset('images/collage/ctc7.svg')); }],
            ['line' => 111, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls1', 'collage_new.z7_generator_controls2'], 'path' => static function () { return (asset('images/collage/tool1.svg')); }],
            ['line' => 117, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls2', 'collage_new.z7_generator_controls3'], 'path' => static function () { return (asset('images/collage/tool2.svg')); }],
            ['line' => 123, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls3', 'collage_new.z7_generator_controls4'], 'path' => static function () { return (asset('images/collage/tool3.svg')); }],
            ['line' => 129, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls4', 'collage_new.z7_generator_controls5'], 'path' => static function () { return (asset('images/collage/tool4.svg')); }],
            ['line' => 135, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls5', 'collage_new.z7_generator_controls6'], 'path' => static function () { return (asset('images/collage/tool5.svg')); }],
            ['line' => 141, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls6', 'collage_new.z7_generator_controls7'], 'path' => static function () { return (asset('images/collage/tool6.svg')); }],
            ['line' => 147, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls7', 'collage_new.z7_generator_controls8'], 'path' => static function () { return (asset('images/collage/tool7.svg')); }],
            ['line' => 153, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls8', 'collage_new.z7_generator_controls9'], 'path' => static function () { return (asset('images/collage/tool8.svg')); }],
            ['line' => 159, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls9', 'collage_new.z7_generator_controls10'], 'path' => static function () { return (asset('images/collage/tool9.svg')); }],
            ['line' => 165, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls10', 'collage_new.z7_generator_controls11'], 'path' => static function () { return (asset('images/collage/tool10.svg')); }],
            ['line' => 171, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls11'], 'path' => static function () { return (asset('images/collage/tool11.svg')); }],
            ['line' => 420, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\ACollageFon', 'fields' => ['image'], 'media' => []]],
            ['line' => 501, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\ACollageStiker', 'fields' => ['image'], 'media' => []]],
            ['line' => 527, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/collage/angle-close.svg')); }],
            ['line' => 585, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio1.jpg')); }],
            ['line' => 623, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio2.jpg')); }],
            ['line' => 661, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio3.jpg')); }],
            ['line' => 700, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio4.jpg')); }],
            ['line' => 739, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/canvas/interio5.jpg')); }],
            ['line' => 939, 'alt' => '{!! trans(\'collage_new.z7_generator_ramma_text4\') !!}', 'title' => '{!! trans(\'collage_new.z7_generator_ramma_text4\') !!}', 'translations' => ['collage_new.z7_generator_ramma_text4'], 'path' => static function () { return (asset('images/canvas/rama1.jpg')); }],
            ['line' => 976, 'alt' => '{!! trans(\'collage_new.z7_generator_ramma_text5\') !!}', 'title' => '{!! trans(\'collage_new.z7_generator_ramma_text5\') !!}', 'translations' => ['collage_new.z7_generator_ramma_text5'], 'path' => static function () { return (asset('images/canvas/rama2.jpg')); }],
            ['line' => 1030, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls15'], 'path' => static function () { return (asset('images/collage/tool12.svg')); }],
            ['line' => 1038, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_generator_controls15', 'collage_new.z7_generator_controls16'], 'path' => static function () { return (asset('images/collage/tool12.svg')); }],
            ['line' => 1099, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 1217, 'alt' => 'img', 'title' => '', 'translations' => ['portrait_buy_form.loading', 'collage_new.z7_reviews_text', 'collage_new.z7_reviews_subtext'], 'path' => static function () { return (asset(env('THEME') . 'images/icon/ellipse-black.svg')); }],
            ['line' => 1234, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_reviews_text', 'collage_new.z7_reviews_subtext'], 'binding' => ['sources' => [['model' => 'App\\Models\\OurWork', 'fields' => ['img'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']], ['model' => 'App\\Models\\Review', 'fields' => ['img'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']]]]],
            ['line' => 1240, 'alt' => 'img', 'title' => '', 'binding' => ['sources' => [['model' => 'App\\Models\\OurWork', 'fields' => ['avatar'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']], ['model' => 'App\\Models\\Review', 'fields' => ['avatar'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']]]]],
        ],
    ],
    'theme/viar/pages/collage/generator_info.blade.php' => [
        'images' => [
            ['line' => 22, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z6_generator_info_title', 'collage_new.z6_generator_info_subtitle', 'collage_new.z6_generator_info_text1', 'collage_new.z6_generator_info_text2', 'collage_new.z6_generator_info_text3'], 'path' => static function () { return (asset('images/collage/vector14.svg')); }],
            ['line' => 28, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z6_generator_info_text1', 'collage_new.z6_generator_info_text2', 'collage_new.z6_generator_info_text3', 'collage_new.z6_generator_info_text4'], 'path' => static function () { return (asset('images/collage/cub1.svg')); }],
            ['line' => 34, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z6_generator_info_text2', 'collage_new.z6_generator_info_text3', 'collage_new.z6_generator_info_text4', 'collage_new.z6_generator_info_text5'], 'path' => static function () { return (asset('images/collage/cub2.svg')); }],
            ['line' => 42, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z6_generator_info_text3', 'collage_new.z6_generator_info_text4', 'collage_new.z6_generator_info_text5'], 'path' => static function () { return (asset('images/collage/vector15.svg')); }],
            ['line' => 51, 'alt' => 'Портрет по фото', 'title' => '', 'translations' => ['collage_new.z6_generator_info_text5', 'collage_new.z6_generator_info_text6'], 'path' => static function () { return (asset('images/collage/g1.jpg')); }],
            ['line' => 62, 'alt' => 'Портрет по фото', 'title' => '', 'translations' => ['collage_new.z6_generator_info_text6', 'collage_new.z6_generator_info_text7'], 'path' => static function () { return (asset('images/collage/g2.jpg')); }],
            ['line' => 73, 'alt' => 'Портрет по фото', 'title' => '', 'translations' => ['collage_new.z6_generator_info_text7'], 'path' => static function () { return (asset('images/collage/g3.jpg')); }],
        ],
    ],
    'theme/viar/pages/collage/js.blade.php' => [
        'images' => [
            ['line' => 487, 'alt' => '', 'title' => '', 'binding' => ['inline_templates' => 'theme/viar/js/collage-templates.js']],
        ],
    ],
    'theme/viar/pages/collage/love_screen.blade.php' => [
        'images' => [
            ['line' => 5, 'alt' => 'img', 'title' => '', 'translations' => ['collage_new.z5_love_screen_title', 'collage_new.z5_love_screen_text1'], 'path' => static function () { return (asset('images/icon/ellipse-whete.svg')); }],
            ['line' => 30, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z5_love_screen_text2', 'collage_new.z5_love_screen_text3'], 'path' => static function () { return (asset('images/collage/11.jpg')); }],
            ['line' => 50, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/collage/12.jpg')); }],
            ['line' => 61, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/collage/ellipse-orb.svg')); }],
        ],
    ],
    'theme/viar/pages/collage/order_screen.blade.php' => [
        'images' => [
            ['line' => 99, 'alt' => 'img', 'title' => '', 'translations' => ['collage_new.z2_order_screen_btn'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg'; }],
        ],
    ],
    'theme/viar/pages/collage/slider.blade.php' => [
        'images' => [
            ['line' => 15, 'alt' => '', 'title' => '', 'translations' => ['collage_new.c_slider_title'], 'binding' => ['model' => 'App\\Models\\ACollageSlider', 'fields' => ['{locale}'], 'media' => []]],
            ['line' => 107, 'alt' => 'img', 'title' => '', 'translations' => ['collage_new.c_slider_z_text5', 'collage_new.c_slider_z_text6_btn', 'collage_new.c_slider_z_text8'], 'path' => static function () { return (asset('images/gift.png')); }],
            ['line' => 158, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'theme/viar/pages/collage/why_form.blade.php' => [
        'images' => [
            ['line' => 30, 'alt' => '', 'title' => '', 'translations' => ['collage_new.add_comment', 'collage_new.foto'], 'path' => static function () { return (asset('img/icons/phone.svg')); }],
            ['line' => 92, 'alt' => 'img', 'title' => '', 'translations' => ['portrait_buy_form.loading'], 'path' => static function () { return (asset('images/pinned-photo.png')); }],
        ],
    ],
    'theme/viar/pages/collage/why_screen.blade.php' => [
        'images' => [
            ['line' => 56, 'alt' => 'img', 'title' => '', 'translations' => ['collage_new.more_portraits'], 'path' => static function () { return (asset('./images/icon/load-more.png')); }],
        ],
    ],
    'theme/viar/pages/condition/index.blade.php' => [
        'images' => [
            ['line' => 16, 'alt' => '', 'title' => '', 'translations' => ['pages.condition.title', 'pages.condition.terms_viar', 'pages.condition.terms', 'pages.condition.terms_text', 'pages.condition.register'], 'path' => static function () { return (asset(config('theme.current') . '/images/condition/icon1.svg')); }],
            ['line' => 27, 'alt' => '', 'title' => '', 'translations' => ['pages.condition.terms', 'pages.condition.terms_text', 'pages.condition.register', 'pages.condition.register_text', 'pages.condition.prices'], 'path' => static function () { return (asset(config('theme.current') . '/images/condition/icon2.svg')); }],
            ['line' => 38, 'alt' => '', 'title' => '', 'translations' => ['pages.condition.register', 'pages.condition.register_text', 'pages.condition.prices', 'pages.condition.prices_text', 'pages.condition.delivery'], 'path' => static function () { return (asset(config('theme.current') . '/images/condition/icon3.svg')); }],
            ['line' => 49, 'alt' => '', 'title' => '', 'translations' => ['pages.condition.prices', 'pages.condition.prices_text', 'pages.condition.delivery', 'pages.condition.delivery_text', 'pages.condition.confidentiality'], 'path' => static function () { return (asset(config('theme.current') . '/images/condition/icon4.svg')); }],
            ['line' => 60, 'alt' => '', 'title' => '', 'translations' => ['pages.condition.delivery', 'pages.condition.delivery_text', 'pages.condition.confidentiality', 'pages.condition.confidentiality_text', 'pages.condition.gift_card'], 'path' => static function () { return (asset(config('theme.current') . '/images/condition/icon5.svg')); }],
            ['line' => 71, 'alt' => '', 'title' => '', 'translations' => ['pages.condition.confidentiality', 'pages.condition.confidentiality_text', 'pages.condition.gift_card', 'pages.condition.gift_card_text', 'pages.condition.property'], 'path' => static function () { return (asset(config('theme.current') . '/images/condition/icon6.svg')); }],
            ['line' => 82, 'alt' => '', 'title' => '', 'translations' => ['pages.condition.gift_card', 'pages.condition.gift_card_text', 'pages.condition.property', 'pages.condition.property_text', 'pages.condition.return_product'], 'path' => static function () { return (asset(config('theme.current') . '/images/condition/icon7.svg')); }],
            ['line' => 93, 'alt' => '', 'title' => '', 'translations' => ['pages.condition.property', 'pages.condition.property_text', 'pages.condition.return_product', 'pages.condition.return_product_text'], 'path' => static function () { return (asset(config('theme.current') . '/images/condition/icon8.svg')); }],
        ],
    ],
    'theme/viar/pages/contacts/index.blade.php' => [
        'images' => [
            ['line' => 597, 'alt' => 'Viar', 'title' => '', 'translations' => ['contacts.text_1_6_b1_1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/cp2.jpg'; }],
            ['line' => 601, 'alt' => '', 'title' => '', 'translations' => ['contacts.text_1_6_b1_1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/check-circle.svg'; }],
            ['line' => 613, 'alt' => 'Viar', 'title' => '', 'translations' => ['contacts.text_1_6_b1_2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/cp1.jpg'; }],
            ['line' => 617, 'alt' => '', 'title' => '', 'translations' => ['contacts.text_1_6_b1_2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/check-circle.svg'; }],
            ['line' => 629, 'alt' => 'Viar', 'title' => '', 'translations' => ['contacts.text_1_6_b1_3'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/cp3.jpg'; }],
            ['line' => 633, 'alt' => '', 'title' => '', 'translations' => ['contacts.text_1_6_b1_3', 'contacts.text_1_6_b1_4'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/check-circle.svg'; }],
            ['line' => 687, 'alt' => '', 'title' => '', 'translations' => ['contacts.tab_payment_info', 'contacts.text_1_8_b1_1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/check-circle.svg'; }],
            ['line' => 728, 'alt' => '', 'title' => '', 'translations' => ['contacts.text_1_8_b2_1', 'contacts.text_1_8_b2_2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/check-circle.svg'; }],
            ['line' => 741, 'alt' => '', 'title' => '', 'translations' => ['contacts.text_1_8_b2_1', 'contacts.text_1_8_b2_2', 'contacts.text_1_8_b3_1', 'contacts.text_1_8_b3_2', 'contacts.text_1_8_b3_3'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/check-circle.svg'; }],
            ['line' => 781, 'alt' => '', 'title' => '', 'translations' => ['contacts.text_1_8_b4_1', 'contacts.text_1_8_b4_2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/check-circle.svg'; }],
            ['line' => 799, 'alt' => '', 'title' => '', 'translations' => ['contacts.text_1_8_b5_1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/d1.png'; }],
            ['line' => 805, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/d2.png'; }],
            ['line' => 811, 'alt' => '', 'title' => '', 'translations' => ['contacts.text_1_8_b5_2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/d3.png'; }],
            ['line' => 826, 'alt' => '', 'title' => '', 'translations' => ['contacts.text_1_8_b5_2', 'popup.target-box_3_btn'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/whatsapp.png'; }],
            ['line' => 861, 'alt' => 'Viar', 'title' => '', 'translations' => ['contacts.text_1_9_b1_1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/r1.jpg'; }],
            ['line' => 865, 'alt' => '', 'title' => '', 'translations' => ['contacts.text_1_9_b1_1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/check-circle.svg'; }],
            ['line' => 877, 'alt' => 'Viar', 'title' => '', 'translations' => ['contacts.text_1_9_b1_2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/r2.jpg'; }],
            ['line' => 881, 'alt' => '', 'title' => '', 'translations' => ['contacts.text_1_9_b1_2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/check-circle.svg'; }],
            ['line' => 893, 'alt' => 'Viar', 'title' => '', 'translations' => ['contacts.text_1_9_b1_3'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/r3.jpg'; }],
            ['line' => 897, 'alt' => '', 'title' => '', 'translations' => ['contacts.text_1_9_b1_3', 'contacts.text_1_9_b1_4'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/check-circle.svg'; }],
            ['line' => 991, 'alt' => 'Viar', 'title' => '', 'binding' => ['model' => 'App\\Models\\Address', 'fields' => ['images']]],
        ],
    ],
    'theme/viar/pages/faq/item.blade.php' => [
        'images' => [
            ['line' => 31, 'alt' => '', 'title' => '', 'translations' => ['pages.faq_info', 'header_footer_new.footer_phone'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/question-all/whatsapp_icon-icons.com_65942 2.svg'; }],
            ['line' => 32, 'alt' => '', 'title' => '', 'translations' => ['pages.faq_info', 'header_footer_new.footer_phone', 'header_footer_new.footer_phone2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/question-all/Viber_icon-icons.com_66792 2.svg'; }],
            ['line' => 33, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.footer_phone', 'header_footer_new.footer_phone2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/question-all/instagram.svg'; }],
        ],
    ],
    'theme/viar/pages/gallery/gallery.blade.php' => [
        'images' => [
            ['line' => 165, 'alt' => 'ViarCanvas', 'title' => '', 'translations' => ['gallery.go_to_category'], 'binding' => ['sources' => [['model' => 'App\\Models\\SliderMod', 'fields' => ['main_category_image']], ['model' => 'App\\Models\\SliderPhoto', 'fields' => ['main_category_image']], ['model' => 'App\\Models\\SliderRepr', 'fields' => ['main_category_image']]]]],
            ['line' => 185, 'alt' => 'ViarCanvas', 'title' => '', 'translations' => ['gallery.go_to_category'], 'binding' => ['sources' => [['model' => 'App\\Models\\SliderMod', 'fields' => ['main_category_image']], ['model' => 'App\\Models\\SliderPhoto', 'fields' => ['main_category_image']], ['model' => 'App\\Models\\SliderRepr', 'fields' => ['main_category_image']]]]],
            ['line' => 205, 'alt' => 'ViarCanvas', 'title' => '', 'translations' => ['gallery.go_to_category'], 'binding' => ['sources' => [['model' => 'App\\Models\\SliderMod', 'fields' => ['main_category_image']], ['model' => 'App\\Models\\SliderPhoto', 'fields' => ['main_category_image']], ['model' => 'App\\Models\\SliderRepr', 'fields' => ['main_category_image']]]]],
            ['line' => 228, 'alt' => 'img', 'title' => '', 'translations' => ['gallery.go_to_category', 'gallery.footer_h2'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg'; }],
        ],
    ],
    'theme/viar/pages/gallery/gallery_category.blade.php' => [
        'images' => [
            ['line' => 62, 'alt' => 'img', 'title' => '', 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg'; }],
            ['line' => 175, 'alt' => 'img', 'title' => '', 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg'; }],
        ],
    ],
    'theme/viar/pages/gallery/item-card-module.blade.php' => [
        'images' => [
            ['line' => 223, 'alt' => 'ViarCanvas', 'title' => '', 'translations' => ['gl.our_w_title', 'gallery.execution_type', 'gallery.type_1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/mcard/3.jpg'; }],
            ['line' => 245, 'alt' => 'ViarCanvas', 'title' => '', 'translations' => ['gallery.type_1', 'gallery.type_2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/mcard/4.jpg'; }],
        ],
    ],
    'theme/viar/pages/gallery/item-card-reproduction.blade.php' => [
        'images' => [
            ['line' => 293, 'alt' => '', 'title' => '', 'translations' => ['gl.our_w_title', 'gallery.execution_type', 'gallery.reproduction_type_1', 'gallery.reproduction_type_2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/cardreproduction/5.jpg'; }],
            ['line' => 302, 'alt' => '', 'title' => '', 'translations' => ['gallery.reproduction_type_1', 'gallery.reproduction_type_2', 'gallery.option'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/cardreproduction/3.jpg'; }],
            ['line' => 316, 'alt' => 'Viar', 'title' => '', 'translations' => ['gallery.reproduction_type_2', 'gallery.option', 'gallery.reproduction_type_3', 'portrait_buy_form.step1_title'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/cardreproduction/4.jpg'; }],
            ['line' => 404, 'alt' => 'Viar', 'title' => '', 'translations' => ['gl.rama', 'gallery.baguettes_and_frames'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/cardreproduction/6.jpg'; }],
            ['line' => 538, 'alt' => 'Viar', 'title' => '', 'translations' => ['gallery.code'], 'binding' => ['model' => 'App\\Models\\CanvasRam', 'fields' => ['img'], 'media' => []]],
            ['line' => 653, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/mcard/interio1.jpg'; }],
            ['line' => 669, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/mcard/interio2.jpg'; }],
            ['line' => 685, 'alt' => ' Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/mcard/interio3.jpg'; }],
            ['line' => 701, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/mcard/interio1.jpg'; }],
            ['line' => 717, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/mcard/interio2.jpg'; }],
            ['line' => 733, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/mcard/interio3.jpg'; }],
            ['line' => 1039, 'alt' => 'ViarCanvas', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/gallery/7.jpg'; }],
            ['line' => 1053, 'alt' => 'ViarCanvas', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/gallery/8.jpg'; }],
            ['line' => 1067, 'alt' => 'ViarCanvas', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/gallery/7.jpg'; }],
            ['line' => 1081, 'alt' => 'ViarCanvas', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/gallery/8.jpg'; }],
        ],
    ],
    'theme/viar/pages/gallery/item-card_part-about.blade.php' => [
        'images' => [
            ['line' => 187, 'alt' => '', 'title' => '', 'translations' => ['collage_new.z7_reviews_subtext'], 'binding' => ['sources' => [['model' => 'App\\Models\\OurWork', 'fields' => ['img'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']], ['model' => 'App\\Models\\Review', 'fields' => ['img'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']]]]],
            ['line' => 197, 'alt' => '', 'title' => '', 'binding' => ['sources' => [['model' => 'App\\Models\\OurWork', 'fields' => ['avatar'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']], ['model' => 'App\\Models\\Review', 'fields' => ['avatar'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']]]]],
        ],
    ],
    'theme/viar/pages/gallery/item-card_part-order-stage.blade.php' => [
        'images' => [
            ['line' => 2, 'alt' => 'img', 'title' => '', 'translations' => ['gallery.zakaz_title', 'gallery.ordering_steps'], 'path' => static function () { return 'https://viarcanvas.com/images/icon/ellipse-whete.svg'; }],
            ['line' => 33, 'alt' => '', 'title' => '', 'translations' => ['gallery.ordering_steps', 'gallery.ordering_steps_t1_1', 'gallery.ordering_steps_t1_2', 'gallery.ordering_steps_t1_3'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/mcard/11.jpg'; }],
            ['line' => 49, 'alt' => '', 'title' => '', 'translations' => ['gallery.ordering_steps_t1_2', 'gallery.ordering_steps_t1_3', 'gallery.photo_item_block_about_pay', 'gallery.ordering_steps_t1_4'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/mcard/12.jpg'; }],
            ['line' => 65, 'alt' => '', 'title' => '', 'translations' => ['gallery.photo_item_block_about_pay', 'gallery.ordering_steps_t1_4', 'gallery.ordering_steps_t1_5', 'gallery.ordering_steps_t1_6', 'gallery.ordering_steps_t1_7'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/mcard/13.jpg'; }],
            ['line' => 81, 'alt' => '', 'title' => '', 'translations' => ['gallery.ordering_steps_t1_5', 'gallery.ordering_steps_t1_6', 'gallery.ordering_steps_t1_7', 'gallery.ordering_steps_t1_8'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/mcard/14.jpg'; }],
        ],
    ],
    'theme/viar/pages/gallery/modern_handmade_paintings.blade.php' => [
        'images' => [
            ['line' => 64, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/1.jpg'; }],
        ],
    ],
    'theme/viar/pages/gallery/module.blade.php' => [
        'images' => [
            ['line' => 72, 'alt' => 'ViarCanvas', 'title' => '', 'translations' => ['gallery.btn_module'], 'binding' => ['model' => 'App\\Models\\SliderMod', 'fields' => ['sub_cat_image']]],
            ['line' => 163, 'alt' => 'img', 'title' => '', 'translations' => ['gallery.module_gallery_title', 'gallery.module_b2_t1', 'gallery.module_b2_t2', 'gallery.module_b2_t3'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg'; }],
            ['line' => 276, 'alt' => 'img', 'title' => '', 'translations' => ['gallery.module_b2_t7', 'gallery.module_b2_t8', 'gallery.module_footer_title'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg'; }],
        ],
    ],
    'theme/viar/pages/gallery/painters.blade.php' => [
        'images' => [
            ['line' => 58, 'alt' => '', 'title' => '', 'translations' => ['gallery.paintes_title', 'gallery.painters_modern_title', 'gallery.painters_default_title'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/1.jpg'; }],
            ['line' => 107, 'alt' => '', 'title' => '', 'translations' => ['gallery.painter_not_found', 'gallery.paintes_search', 'gallery.painters_modern_search', 'gallery.paintes_default_search'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/gallery/7.jpg'; }],
        ],
    ],
    'theme/viar/pages/gallery/photo.blade.php' => [
        'images' => [
            ['line' => 87, 'alt' => 'ViarCanvas', 'title' => '', 'binding' => ['model' => 'App\\Models\\SliderPhoto', 'fields' => ['sub_cat_image']]],
            ['line' => 238, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/5.jpg'; }],
            ['line' => 248, 'alt' => 'img', 'title' => '', 'translations' => ['gallery.photo_footer_title'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg'; }],
        ],
    ],
    'theme/viar/pages/gallery/reproduction.blade.php' => [
        'images' => [
            ['line' => 71, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\SliderRepr', 'fields' => ['sub_cat_image']]],
            ['line' => 298, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/1.svg'; }],
            ['line' => 301, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/2.svg'; }],
            ['line' => 304, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/3.svg'; }],
            ['line' => 307, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/4.svg'; }],
            ['line' => 310, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/5.svg'; }],
            ['line' => 313, 'alt' => '', 'title' => '', 'translations' => ['gallery.sort_by_price_low'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/6.svg'; }],
            ['line' => 724, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/2.jpg'; }],
        ],
    ],
    'theme/viar/pages/gallery/reproduction_tabs_century.blade.php' => [
        'images' => [
            ['line' => 72, 'alt' => '', 'title' => '', 'translations' => ['gallery.forma'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/1a.svg'; }],
            ['line' => 90, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/3a.svg'; }],
            ['line' => 107, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/5a.svg'; }],
        ],
    ],
    'theme/viar/pages/gallery/reproduction_tabs_genre.blade.php' => [
        'images' => [
            ['line' => 67, 'alt' => '', 'title' => '', 'translations' => ['gallery.forma'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/1a.svg'; }],
            ['line' => 72, 'alt' => '', 'title' => '', 'translations' => ['gallery.forma'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/3a.svg'; }],
            ['line' => 77, 'alt' => '', 'title' => '', 'translations' => ['gallery.forma'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/5a.svg'; }],
        ],
    ],
    'theme/viar/pages/gallery/styles.blade.php' => [
        'images' => [
            ['line' => 63, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/reproduction/1.jpg'; }],
            ['line' => 104, 'alt' => '', 'title' => '', 'translations' => ['gallery.painter_not_found'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/gallery/7.jpg'; }],
        ],
    ],
    'theme/viar/pages/gallery/zpart_modals.blade.php' => [
        'images' => [
            ['line' => 21, 'alt' => '', 'title' => '', 'translations' => ['gallery.photo_item_block_about_delivery_t1_3', 'gallery.photo_item_block_about_delivery_t1_4', 'gallery.photo_item_block_about_delivery_t1_5', 'gallery.photo_item_block_about_delivery_t1_6'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module/1.svg'; }],
            ['line' => 22, 'alt' => '', 'title' => '', 'translations' => ['gallery.photo_item_block_about_delivery_t1_4', 'gallery.photo_item_block_about_delivery_t1_5', 'gallery.photo_item_block_about_delivery_t1_6'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module/2.svg'; }],
            ['line' => 23, 'alt' => '', 'title' => '', 'translations' => ['gallery.photo_item_block_about_delivery_t1_5', 'gallery.photo_item_block_about_delivery_t1_6'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module/3.svg'; }],
            ['line' => 64, 'alt' => 'WhatsApp', 'title' => '', 'translations' => ['gallery.photo_item_block_about_delivery_t1_8', 'popup.target-box_3_btn', 'gallery.photo_item_block_about_pay'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module/1.png'; }],
            ['line' => 120, 'alt' => 'WhatsApp', 'title' => '', 'translations' => ['gallery.photo_item_block_about_pay_t1_3', 'gallery.photo_item_block_about_pay_t1_4', 'popup.target-box_3_btn'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module/1.png'; }],
            ['line' => 138, 'alt' => 'ViarCanvas', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/cardreproduction/frame.jpg'; }],
        ],
    ],
    'theme/viar/pages/gallery/zpart_popular.blade.php' => [
        'images' => [
            ['line' => 46, 'alt' => '', 'title' => '', 'translations' => ['gallery.see'], 'binding' => ['model' => 'App\\Models\\GalleryCategory', 'fields' => ['image']]],
            ['line' => 69, 'alt' => '', 'title' => '', 'translations' => ['gallery.see'], 'binding' => ['model' => 'App\\Models\\GalleryCategory', 'fields' => ['image']]],
            ['line' => 94, 'alt' => '', 'title' => '', 'translations' => ['gallery.see'], 'binding' => ['model' => 'App\\Models\\GalleryCategory', 'fields' => ['image']]],
        ],
    ],
    'theme/viar/pages/gallery/zpart_popular_painters.blade.php' => [
        'images' => [
            ['line' => 25, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images')) . '/gallery/7.jpg'; }],
        ],
    ],
    'theme/viar/pages/gallery/zpart_trybuy.blade.php' => [
        'images' => [
            ['line' => 13, 'alt' => 'img', 'title' => '', 'translations' => ['gallery.trybuy_t1_title', 'gallery.trybuy_t1_desc'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/ellipse-whete.svg'; }],
        ],
    ],
    'theme/viar/pages/gallery/zpart_viarcanvas_is.blade.php' => [
        'images' => [
            ['line' => 33, 'alt' => '', 'title' => '', 'translations' => ['gallery.photo_viarcanvas_is', 'gallery.module_viarcanvas_is', 'contacts.viarcanvas_is_t_0', 'contacts.viarcanvas_is_t_1', 'contacts.viarcanvas_is_t_2', 'contacts.viarcanvas_is_t_3'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/gallery/1.svg'; }],
            ['line' => 39, 'alt' => '', 'title' => '', 'translations' => ['contacts.viarcanvas_is_t_0', 'contacts.viarcanvas_is_t_1', 'contacts.viarcanvas_is_t_2', 'contacts.viarcanvas_is_t_3', 'contacts.viarcanvas_is_t_4', 'contacts.viarcanvas_is_t_5'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/gallery/2.svg'; }],
            ['line' => 47, 'alt' => '', 'title' => '', 'translations' => ['contacts.viarcanvas_is_t_1', 'contacts.viarcanvas_is_t_2', 'contacts.viarcanvas_is_t_3', 'contacts.viarcanvas_is_t_4', 'contacts.viarcanvas_is_t_5', 'contacts.viarcanvas_is_t_6'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/gallery/3.svg'; }],
            ['line' => 53, 'alt' => '', 'title' => '', 'translations' => ['contacts.viarcanvas_is_t_3', 'contacts.viarcanvas_is_t_4', 'contacts.viarcanvas_is_t_5', 'contacts.viarcanvas_is_t_6', 'contacts.viarcanvas_is_t_7', 'contacts.viarcanvas_is_t_8'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/gallery/4.svg'; }],
        ],
    ],
    'theme/viar/pages/index/emoj_4_5.blade.php' => [
        'images' => [
            ['line' => 68, 'alt' => 'img', 'title' => '', 'binding' => ['model' => 'App\\Models\\AllStyleFormStepItem', 'fields' => ['image'], 'media' => [], 'where' => ['name' => '1_default', 'step' => 4]]],
            ['line' => 132, 'alt' => 'img', 'title' => '', 'binding' => ['model' => 'App\\Models\\AllStyleFormStepItem', 'fields' => ['image'], 'media' => [], 'where' => ['name' => '1_default', 'step' => 4]]],
            ['line' => 145, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-1.png')); }],
            ['line' => 154, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-2.png')); }],
            ['line' => 163, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-3.png')); }],
            ['line' => 182, 'alt' => 'img', 'title' => '', 'binding' => ['model' => 'App\\Models\\AllStyleFormStepItem', 'fields' => ['image'], 'media' => [], 'where' => ['name' => '2_default', 'step' => 4]]],
            ['line' => 224, 'alt' => 'img', 'title' => '', 'binding' => ['model' => 'App\\Models\\AllStyleFormStepItem', 'fields' => ['image'], 'media' => [], 'where' => ['name' => '1_default', 'step' => 4]]],
            ['line' => 237, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-1.png')); }],
            ['line' => 246, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-2.png')); }],
            ['line' => 255, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-3.png')); }],
            ['line' => 274, 'alt' => 'img', 'title' => '', 'binding' => ['model' => 'App\\Models\\AllStyleFormStepItem', 'fields' => ['image'], 'media' => [], 'where' => ['name' => '3_default', 'step' => 4]]],
            ['line' => 302, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz-full/kviz-1.jpg')); }],
            ['line' => 329, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz-full/kviz-1.jpg')); }],
            ['line' => 342, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-1.png')); }],
            ['line' => 351, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-2.png')); }],
            ['line' => 360, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/kviz/kviz-3.png')); }],
            ['line' => 371, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/bg/kviz-finish.png')); }],
            ['line' => 393, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/icons/phone.svg')); }],
            ['line' => 543, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/icon/check-done.svg')); }],
        ],
    ],
    'theme/viar/pages/index/examples4.blade.php' => [
        'images' => [
            ['line' => 63, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.our_portraits_btn_title'], 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
        ],
    ],
    'theme/viar/pages/index/faq9.blade.php' => [
        'images' => [
            ['line' => 12, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.faq_title', 'homepage_new.faq_whats_title', 'homepage_new.faq_whats_desc', 'homepage_new.faq_write_whatsup'], 'path' => static function () { return (asset('images/faq.png')); }],
            ['line' => 44, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.faq_show_more_btn_text'], 'path' => static function () { return (asset('images/icon/load-more.png')); }],
        ],
    ],
    'theme/viar/pages/index/footer.blade.php' => [
        'images' => [
            ['line' => 7, 'alt' => 'viarcanvas', 'title' => '', 'path' => static function () { return (asset('images/logo.svg')); }],
            ['line' => 42, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.footer_phone_clean', 'header_footer_new.footer_phone', 'header_footer_new.footer_phone_clean2', 'header_footer_new.footer_phone2'], 'path' => static function () { return (asset('img/icons/phone-footer.svg')); }],
            ['line' => 49, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.footer_phone_clean', 'header_footer_new.footer_phone', 'header_footer_new.footer_phone_clean2', 'header_footer_new.footer_phone2'], 'path' => static function () { return (asset('img/icons/mail-footer.svg')); }],
            ['line' => 56, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.footer_phone', 'header_footer_new.footer_phone_clean2', 'header_footer_new.footer_phone2'], 'path' => static function () { return (asset('img/icons/phone-footer.svg')); }],
            ['line' => 63, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.footer_phone2', 'header_footer_new.footer_addr1_link'], 'path' => static function () { return (asset('img/icons/phone-footer.svg')); }],
            ['line' => 70, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.footer_addr1_link', 'header_footer_new.footer_addr1', 'header_footer_new.footer_addr2_link'], 'path' => static function () { return (asset('img/icons/mail-footer.svg')); }],
            ['line' => 75, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.footer_addr1_link', 'header_footer_new.footer_addr1', 'header_footer_new.footer_addr2_link', 'header_footer_new.footer_addr2'], 'path' => static function () { return (asset('img/icons/location.svg')); }],
            ['line' => 80, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.footer_addr1_link', 'header_footer_new.footer_addr1', 'header_footer_new.footer_addr2_link', 'header_footer_new.footer_addr2', 'header_footer_new.we_in_socs'], 'path' => static function () { return (asset('img/icons/location.svg')); }],
            ['line' => 183, 'alt' => 'Salidzini.lv logotips', 'title' => 'Interneta veikali. Labākā cena', 'translations' => ['header_footer_new.footer_copyright2', 'header_footer_new.terms_and_conditions'], 'path' => static function () { return 'https://static.salidzini.lv/images/logo_button.webp'; }],
            ['line' => 185, 'alt' => 'Meklē preces Latvijas interneta veikalos', 'title' => '', 'translations' => ['header_footer_new.terms_and_conditions'], 'path' => static function () { return '//www.kurpirkt.lv/media/kurpirkt120.gif'; }],
            ['line' => 188, 'alt' => 'visa', 'title' => '', 'translations' => ['header_footer_new.footer_paysera'], 'path' => static function () { return (asset('images/icon/visa_logo_white.svg')); }],
            ['line' => 189, 'alt' => 'mastercard', 'title' => '', 'translations' => ['header_footer_new.footer_paysera', 'header_footer_new.footer_copyright'], 'path' => static function () { return (asset('images/icon/mc_symbol.svg')); }],
        ],
    ],
    'theme/viar/pages/index/happy3.blade.php' => [
        'images' => [
            ['line' => 14, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.you_feel_nice', 'homepage_new.you_feel_nice_1_text', 'homepage_new.you_feel_nice_2_text'], 'path' => static function () { return (asset('images/happy/happy-1.svg')); }],
            ['line' => 20, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.you_feel_nice', 'homepage_new.you_feel_nice_1_text', 'homepage_new.you_feel_nice_2_text', 'homepage_new.you_feel_nice_3_text'], 'path' => static function () { return (asset('images/happy/happy-2.svg')); }],
            ['line' => 26, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.you_feel_nice_1_text', 'homepage_new.you_feel_nice_2_text', 'homepage_new.you_feel_nice_3_text', 'homepage_new.you_feel_nice_4_text'], 'path' => static function () { return (asset('images/happy/happy-3.svg')); }],
            ['line' => 32, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.you_feel_nice_2_text', 'homepage_new.you_feel_nice_3_text', 'homepage_new.you_feel_nice_4_text'], 'path' => static function () { return (asset('images/happy/happy-4.svg')); }],
            ['line' => 46, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.you_feel_nice_4_text'], 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
            ['line' => 9, 'alt' => '', 'title' => '', 'path' => static function () { return array_values(array_filter(\Illuminate\Support\Arr::only(site_image('home_banner_1', 'images/bg/happy-bg.jpg'), ['src', 'src_webp']))); }],
        ],
    ],
    'theme/viar/pages/index/header0.blade.php' => [
        'images' => [
            ['line' => 37, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 84, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 130, 'alt' => '', 'title' => '', 'translations' => ['settings.site_name'], 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 149, 'alt' => '', 'title' => '', 'translations' => ['settings.site_name', 'header_footer_new.header_col1_name'], 'path' => static function () { return (asset('images/logo.svg')); }],
            ['line' => 222, 'alt' => '', 'title' => '', 'translations' => ['header_footer_new.contacts', 'header_footer_new.prices', 'header_footer_new.stocks'], 'path' => static function () { return (asset('images/flag/' . app()->getLocale() . '.svg')); }],
            ['line' => 233, 'alt' => '', 'title' => '', 'binding' => ['glob' => 'images/flag/*.svg']],
        ],
    ],
    'theme/viar/pages/index/header1.blade.php' => [
        'images' => [
            ['line' => 5, 'alt' => '', 'title' => '', 'translations' => ['settings.site_name'], 'path' => static function () { return (asset('img/icons/logo.svg')); }],
            ['line' => 72, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/flag/' . app()->getLocale() . '.svg')); }],
            ['line' => 83, 'alt' => '', 'title' => '', 'binding' => ['glob' => 'images/flag/*.svg']],
        ],
    ],
    'theme/viar/pages/index/header_new_0.blade.php' => [
        'images' => [
            ['line' => 38, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 55, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['png'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 101, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 147, 'alt' => '', 'title' => '', 'translations' => ['settings.site_name'], 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['images'], 'where' => ['is_show' => 1], 'prefix' => '', 'image_original' => true]],
            ['line' => 166, 'alt' => '', 'title' => '', 'translations' => ['settings.site_name', 'header_footer_new.header_col1_name'], 'path' => static function () { return (asset('images/logo.svg')); }],
        ],
    ],
    'theme/viar/pages/index/header_new_1.blade.php' => [
        'images' => [
            ['line' => 5, 'alt' => '', 'title' => '', 'translations' => ['settings.site_name', 'header_footer_new.header_col1_name'], 'path' => static function () { return (asset('img/icons/logo.svg')); }],
            ['line' => 128, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['png'], 'where' => ['is_show' => 1], 'prefix' => 'storage/']],
            ['line' => 160, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['png'], 'where' => ['is_show' => 1], 'prefix' => 'storage/']],
            ['line' => 192, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\HeaderMenu', 'fields' => ['png'], 'where' => ['is_show' => 1], 'prefix' => 'storage/']],
        ],
    ],
    'theme/viar/pages/index/modals.blade.php' => [
        'images' => [
            ['line' => 7, 'alt' => 'img', 'title' => '', 'translations' => ['portrait_buy_form.popup_thanks_text1', 'portrait_buy_form.popup_thanks_text2', 'portrait_buy_form.popup_thanks_text3', 'portrait_buy_form.popup_thanks_text4'], 'path' => static function () { return (asset('images/icon/check-done.svg')); }],
            ['line' => 363, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.set_need_style'], 'path' => static function () { return (asset('images/portrait-form.png')); }],
            ['line' => 383, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z1.webp')); }],
            ['line' => 405, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z2.webp')); }],
            ['line' => 427, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z3.webp')); }],
            ['line' => 449, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z4.webp')); }],
            ['line' => 473, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z5.webp')); }],
            ['line' => 495, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z6.webp')); }],
            ['line' => 517, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z7.webp')); }],
            ['line' => 539, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z8.webp')); }],
        ],
    ],
    'theme/viar/pages/index/revs8.blade.php' => [
        'images' => [
            ['line' => 11, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
            ['line' => 22, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.gift_cart_banner_title', 'homepage_new.gift_cart_title', 'homepage_new.gift_cart_desc'], 'path' => static function () { return site_image('home_certificate', env('THEME').'images/certificate.png')['src']; }],
            ['line' => 57, 'alt' => '', 'title' => '', 'binding' => ['sources' => [['model' => 'App\\Models\\OurWork', 'fields' => ['img'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']], ['model' => 'App\\Models\\Review', 'fields' => ['img'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']]]]],
            ['line' => 72, 'alt' => 'img', 'title' => '', 'binding' => ['sources' => [['model' => 'App\\Models\\OurWork', 'fields' => ['avatar'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']], ['model' => 'App\\Models\\Review', 'fields' => ['avatar'], 'media' => [], 'where' => ['active' => 1, 'orig_locale' => '{locale}']]]]],
            ['line' => 125, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
        ],
    ],
    'theme/viar/pages/index/slider1.blade.php' => [
        'images' => [
            ['line' => 15, 'alt' => '', 'title' => '', 'translations' => ['homepage_new.slider_title'], 'binding' => ['model' => 'App\\Models\\NewhomeTopSlider', 'fields' => ['{locale}'], 'media' => []]],
            ['line' => 41, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.slider_btn1_title', 'homepage_new.slider_image_text', 'homepage_new.slider_image_text_mob'], 'path' => static function () { return (asset('images/gift.png')); }],
            ['line' => 95, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'theme/viar/pages/index/tops6.blade.php' => [
        'images' => [
            ['line' => 3, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.top_sales_title'], 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-cream.svg')); }],
            ['line' => 54, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.top_sales_btn_title', 'homepage_new.top_sales_more_btn_title', 'homepage_new.hide'], 'path' => static function () { return (asset('images/icon/load-more.png')); }],
        ],
    ],
    'theme/viar/pages/index/why7.blade.php' => [
        'images' => [
            ['line' => 67, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.why_pic_text8', 'homepage_new.why_pic_text9', 'homepage_new.why_pic_text10', 'homepage_new.why_pic_gift_text'], 'path' => static function () { return site_image('home_why_block', 'images/why-picture-girl.png')['src']; }],
            ['line' => 71, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.why_pic_text10', 'homepage_new.why_pic_gift_text'], 'path' => static function () { return (asset('images/icon/gift.svg')); }],
        ],
    ],
    'theme/viar/pages/index/why7_form.blade.php' => [
        'images' => [
            ['line' => 73, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.loading'], 'path' => static function () { return (asset('img/icons/phone.svg')); }],
            ['line' => 94, 'alt' => 'img', 'title' => '', 'translations' => ['portrait_buy_form.loading'], 'path' => static function () { return site_image('home_15_min', 'images/pinned-photo.png')['src']; }],
        ],
    ],
    'theme/viar/pages/index/works5.blade.php' => [
        'images' => [
            ['line' => 45, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_title', 'homepage_new.how_we_work_sub_title', 'homepage_new.how_we_work_step1_title', 'homepage_new.how_we_work_step1_desc'], 'path' => static function () { return (asset('images/work/work-1.svg')); }],
            ['line' => 55, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_step1_title', 'homepage_new.how_we_work_step1_desc', 'homepage_new.how_we_work_step2_title', 'homepage_new.how_we_work_step2_desc'], 'path' => static function () { return (asset('images/work/work-2.svg')); }],
            ['line' => 65, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_step2_title', 'homepage_new.how_we_work_step2_desc', 'homepage_new.how_we_work_step3_title', 'homepage_new.how_we_work_step3_desc', 'homepage_new.how_we_work_express'], 'path' => static function () { return (asset('images/work/work-3.svg')); }],
            ['line' => 74, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_step3_title', 'homepage_new.how_we_work_step3_desc', 'homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/work/work-4.svg')); }],
            ['line' => 78, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.how_we_work_step3_title', 'homepage_new.how_we_work_step3_desc', 'homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/work/work-5.svg')); }],
            ['line' => 11, 'alt' => '', 'title' => '', 'path' => static function () { return array_values(array_filter(\Illuminate\Support\Arr::only(site_image('home_banner_2', env('THEME').'images/bg/work-bg.jpg'), ['src', 'src_webp']))); }],
        ],
    ],
    'theme/viar/pages/modular-generator/index.blade.php' => [
        'images' => [
            ['line' => 35, 'alt' => 'Viar', 'title' => '', 'translations' => ['pages.modular-generator.slider.page-title'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/main.png'; }],
            ['line' => 40, 'alt' => 'img', 'title' => '', 'translations' => ['pages.modular-generator.slider.page-title'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/el.svg'; }],
            ['line' => 128, 'alt' => 'img', 'title' => '', 'translations' => ['pages.modular-generator.slider.text3', 'pages.modular-generator.slider.btn', 'pages.modular-generator.mg-design.mg-design__title_t1'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg'; }],
            ['line' => 146, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.mg-design.mg-design__title_t1', 'pages.modular-generator.mg-design.p1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/i1.svg'; }],
            ['line' => 161, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.mg-design.p1', 'pages.modular-generator.mg-design.p2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/d1.jpg'; }],
            ['line' => 167, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.mg-design.p2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/i2.svg'; }],
            ['line' => 182, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.mg-design.p2', 'pages.modular-generator.mg-design.p3'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/d2.jpg'; }],
            ['line' => 188, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.mg-design.p3'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/i3.svg'; }],
            ['line' => 203, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.mg-design.p3', 'pages.modular-generator.mg-design.p4'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/d3.jpg'; }],
            ['line' => 209, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.mg-design.p4'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/i4.svg'; }],
            ['line' => 224, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.mg-design.p4', 'pages.modular-generator.mg-design.btn', 'pages.modular-generator.mg-program.title', 'pages.modular-generator.mg-program.text1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/d4.jpg'; }],
            ['line' => 321, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.mg-why.title', 'pages.modular-generator.mg-why.p1', 'pages.modular-generator.mg-why.p2', 'pages.modular-generator.mg-why.p3'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/w1.svg'; }],
            ['line' => 328, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.mg-why.p1', 'pages.modular-generator.mg-why.p2', 'pages.modular-generator.mg-why.p3', 'pages.modular-generator.mg-why.p4'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/w2.svg'; }],
            ['line' => 335, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.mg-why.p2', 'pages.modular-generator.mg-why.p3', 'pages.modular-generator.mg-why.p4'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/w3.svg'; }],
            ['line' => 342, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.mg-why.p3', 'pages.modular-generator.mg-why.p4'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/w4.svg'; }],
            ['line' => 350, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.mg-why.p4', 'pages.modular-generator.mg-inspire.title', 'pages.modular-generator.mg-inspire.p1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/why1.png'; }],
            ['line' => 360, 'alt' => 'img', 'title' => '', 'translations' => ['pages.modular-generator.mg-inspire.title', 'pages.modular-generator.mg-inspire.p1'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg'; }],
            ['line' => 383, 'alt' => 'Viar', 'title' => '', 'translations' => ['pages.modular-generator.mg-inspire.p1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/ins1.jpg'; }],
            ['line' => 393, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/ins2.jpg'; }],
            ['line' => 403, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/ins3.jpg'; }],
            ['line' => 413, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/ins1.jpg'; }],
            ['line' => 423, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/ins1.jpg'; }],
            ['line' => 433, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/ins2.jpg'; }],
            ['line' => 478, 'alt' => 'Viar', 'title' => '', 'translations' => ['pages.modular-generator.generate.p1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/pngwing.png'; }],
            ['line' => 514, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.generate.step1.select_photo'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/mt1.svg'; }],
            ['line' => 600, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.generate.step2.select_form'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/mt2.svg'; }],
            ['line' => 615, 'alt' => '', 'title' => '', 'translations' => ['pages.modular-generator.generate.step3.select_size'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/mt3.svg'; }],
            ['line' => 699, 'alt' => '', 'title' => '', 'translations' => ['gl.hud_of_text'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/mt4.svg'; }],
            ['line' => 718, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 760, 'alt' => '', 'title' => '', 'translations' => ['cart.comment', 'pages.modular-generator.generate.step5.add_comment'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/mt5.svg'; }],
            ['line' => 844, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/canvas/interio1.jpg'; }],
            ['line' => 878, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/canvas/interio2.jpg'; }],
            ['line' => 912, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/canvas/interio3.jpg'; }],
            ['line' => 947, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/canvas/interio4.jpg'; }],
            ['line' => 982, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/canvas/interio5.jpg'; }],
            ['line' => 1209, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 1267, 'alt' => 'img', 'title' => '', 'translations' => ['pages.modular-generator.how_we_work_title', 'pages.modular-generator.about-work.text'], 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-black.svg'; }],
            ['line' => 1290, 'alt' => '', 'title' => '', 'translations' => ['about.text_8_3', 'about.text_8_4', 'about.text_8_5'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/w1.jpg'; }],
            ['line' => 1315, 'alt' => '', 'title' => '', 'translations' => ['about.text_8_6', 'about.text_8_7', 'about.text_8_8'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/w2.jpg'; }],
            ['line' => 1340, 'alt' => '', 'title' => '', 'translations' => ['about.text_8_9', 'about.text_8_10', 'about.text_8_11'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/w3.jpg'; }],
            ['line' => 1365, 'alt' => '', 'title' => '', 'translations' => ['about.text_8_12', 'about.text_8_13', 'about.text_8_14', 'about.text_8_15'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/contacts/w4.jpg'; }],
            ['line' => 1156, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/split_v.svg'; }],
            ['line' => 1163, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/collage7.png'; }],
            ['line' => 264, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/video.webp'; }],
        ],
    ],
    'theme/viar/pages/modular-generator/mg-types.blade.php' => [
        'images' => [
            ['line' => 116, 'alt' => 'ViarCanvas', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/s1.jpg'; }],
            ['line' => 139, 'alt' => 'ViarCanvas', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/2.jpg'; }],
            ['line' => 161, 'alt' => 'ViarCanvas', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/3.jpg'; }],
            ['line' => 183, 'alt' => 'ViarCanvas', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/4.jpg'; }],
            ['line' => 205, 'alt' => 'ViarCanvas', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/module-generator/5.jpg'; }],
        ],
    ],
    'theme/viar/pages/modular-generator/one_item.blade.php' => [
        'images' => [
            ['line' => 25, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'where' => ['active' => 1]]],
        ],
    ],
    'theme/viar/pages/portrait--royal.blade.php' => [
        'images' => [
            ['line' => 39, 'alt' => 'img', 'title' => '', 'translations' => ['portrait.tabs_title'], 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait-oil.blade.php' => [
        'images' => [
            ['line' => 48, 'alt' => 'img', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab_text'], 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait.blade.php' => [
        'images' => [
            ['line' => 32, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-whete.svg')); }],
            ['line' => 80, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
            ['line' => 83, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset(env('THEME').'images/sizes/union.png')); }],
            ['line' => 91, 'alt' => 'img', 'title' => '', 'translations' => ['portrait.sizes__title', 'portrait.ex_cl_works_title', 'portrait.ex_cl_works_desc'], 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-black.svg')); }],
            ['line' => 172, 'alt' => '', 'title' => '', 'translations' => ['portrait.ex_video_desc', 'portrait.des_help_title', 'portrait.des_help_desc'], 'path' => static function () { return (asset(env('THEME').'images/play.svg')); }],
            ['line' => 242, 'alt' => '', 'title' => '', 'translations' => ['portrait.form_add_comment'], 'path' => static function () { return (asset(env('THEME').'img/icons/phone.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait/base_and_extra_services.blade.php' => [
        'images' => [
            ['line' => 15, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.s_service_tab_extra'], 'path' => static function () { return (ver_asset('images/sharj/service1.webp')); }],
            ['line' => 20, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('images/sharj/service2.webp')); }],
            ['line' => 67, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.s_service_basic_list_2_2'], 'path' => static function () { return (ver_asset('images/sharj/service3.webp')); }],
            ['line' => 72, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('images/sharj/service4.webp')); }],
            ['line' => 119, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.s_service_basic_list_4_2'], 'path' => static function () { return (ver_asset('images/sharj/service5.webp')); }],
            ['line' => 124, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('images/sharj/service6.webp')); }],
            ['line' => 173, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('images/sharj/service7.webp')); }],
            ['line' => 178, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.s_service_extra_list_1', 'portrait_royal.s_service_extra_list_1_2'], 'path' => static function () { return (ver_asset('images/sharj/service8.webp')); }],
            ['line' => 196, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.s_service_extra_list_1_2'], 'path' => static function () { return (ver_asset('images/sharj/service9.webp')); }],
            ['line' => 201, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.s_service_extra_list_2', 'portrait_royal.s_service_extra_list_2_2'], 'path' => static function () { return (ver_asset('images/sharj/service10.webp')); }],
            ['line' => 219, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.s_service_extra_list_2_2'], 'path' => static function () { return (ver_asset('images/sharj/service11.webp')); }],
            ['line' => 224, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.s_service_extra_list_3', 'portrait_royal.s_service_extra_list_3_2'], 'path' => static function () { return (ver_asset('images/sharj/service12.webp')); }],
        ],
    ],
    'theme/viar/pages/portrait/etc_styles.blade.php' => [
        'images' => [
            ['line' => 30, 'alt' => '', 'title' => '', 'path' => static function () { return 'images/vertical.webp'; }],
        ],
    ],
    'theme/viar/pages/portrait/form/art_decor.blade.php' => [
        'images' => [
            ['line' => 19, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step6_title'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 56, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step6_bot_desc'], 'path' => static function () { return (asset('images/prompt6.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/form/canvas.blade.php' => [
        'images' => [
            ['line' => 23, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 44, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step5_bot_desc'], 'path' => static function () { return (asset('images/prompt5.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/form/comments.blade.php' => [
        'images' => [
            ['line' => 67, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step_comment_bot_desc'], 'path' => static function () { return (asset('images/prompt9.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/form/final.blade.php' => [
        'images' => [
            ['line' => 23, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.final_pack'], 'path' => static function () { return (asset('images/icon/info.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait/form/form.blade.php' => [
        'images' => [
            ['line' => 11, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step2_title'], 'path' => static function () { return (asset('images/form1.svg ')); }],
            ['line' => 22, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/form2.svg ')); }],
            ['line' => 33, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/form3.svg ')); }],
            ['line' => 50, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step2_bot_desc'], 'path' => static function () { return (asset('images/prompt2.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/form/frames.blade.php' => [
        'images' => [
            ['line' => 53, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step7_bot_desc'], 'path' => static function () { return (asset('images/prompt7.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/form/newcanvas.blade.php' => [
        'images' => [
            ['line' => 18, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 55, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step6_bot_desc'], 'path' => static function () { return (asset('images/prompt6.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/form/persons.blade.php' => [
        'images' => [
            ['line' => 73, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step4_bot_desc'], 'path' => static function () { return (asset('images/prompt4.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/form/photo.blade.php' => [
        'images' => [
            ['line' => 55, 'alt' => '', 'title' => '', 'translations' => ['portrait.form_files_loaded', 'portrait_buy_form.step1_bot_desc'], 'path' => static function () { return (asset('images/prompt1.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/form/sets.blade.php' => [
        'images' => [
            ['line' => 17, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step8_bot_desc'], 'path' => static function () { return (asset('images/prompt8.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/form/sizes.blade.php' => [
        'images' => [
            ['line' => 204, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step3_bot_desc'], 'path' => static function () { return (asset('images/prompt3.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/gift-oil.blade.php' => [
        'images' => [
            ['line' => 21, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['pages.portrait_oil.oil-gift.t11', 'pages.portrait_oil.oil-gift.t12', 'pages.portrait_oil.oil-gift.t13', 'portrait_royal.btn_order_portrait', 'portrait_royal.s_gift_note'], 'path' => static function () { return (ver_asset('images/collage/gift.svg')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.oil-gift.t13', 'portrait_royal.btn_order_portrait', 'portrait_royal.s_gift_note'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/gift_1.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/gift.blade.php' => [
        'images' => [
            ['line' => 12, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['portrait_royal.s_gift_title', 'portrait_royal.s_gift_text', 'portrait_royal.btn_order_portrait', 'portrait_royal.s_gift_note'], 'path' => static function () { return (ver_asset('images/collage/gift.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait/h_zero.blade.php' => [
        'images' => [
            ['line' => 43, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/portrait-bg.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/modals.blade.php' => [
        'images' => [
            ['line' => 337, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/portrait-form.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/oil-info.blade.php' => [
        'images' => [
            ['line' => 27, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.oilinfo.t11', 'pages.portrait_oil.oilinfo.t12', 'pages.portrait_oil.oilinfo.t13', 'pages.portrait_oil.oilinfo.t14'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/oil_b2.png')); }],
            ['line' => 36, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.oilinfo.t14'], 'path' => static function () { return 'https://viarcanvas.com/images/sharj/v1.svg?1691276006'; }],
            ['line' => 48, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/oil_b1.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/oil-video.blade.php' => [
        'images' => [
            ['line' => 11, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.oil-video.t11'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/video_1.png')); }],
            ['line' => 23, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.oil-video.t11', 'pages.portrait_oil.oil-video.t12', 'pages.portrait_oil.oil-video.t13', 'pages.portrait_oil.oil-video.t14'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/video_btn_play.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait/order_steps--small.blade.php' => [
        'images' => [
            ['line' => 18, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_steps_title', 'portrait.order_steps_desc', 'portrait_royal.s_order_note'], 'path' => static function () { return (ver_asset('images/sharj/pngwing.webp')); }],
            ['line' => 27, 'alt' => '', 'title' => '', 'translations' => ['homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/sharj/express.svg')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'translations' => ['homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/sharj/standart.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait/order_steps.blade.php' => [
        'images' => [
            ['line' => 21, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_steps_title', 'portrait.order_steps_desc', 'portrait.order_step1_title', 'portrait.order_step1_desc'], 'path' => static function () { return (asset('images/photo.png')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_step1_title', 'portrait.order_step1_desc', 'portrait.order_step2_title', 'portrait.order_step2_desc'], 'path' => static function () { return (asset('images/conversation.png')); }],
            ['line' => 41, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_step2_title', 'portrait.order_step2_desc', 'portrait.order_step3_title', 'portrait.order_step3_desc'], 'path' => static function () { return (asset('images/portrait.png')); }],
            ['line' => 51, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_step3_title', 'portrait.order_step3_desc', 'portrait.order_step4_title', 'portrait.order_step4_desc'], 'path' => static function () { return (asset('images/canvas.png')); }],
            ['line' => 61, 'alt' => '', 'title' => '', 'translations' => ['portrait.order_step4_title', 'portrait.order_step4_desc', 'portrait.order_step5_title', 'portrait.order_step5_desc'], 'path' => static function () { return (asset('images/delivery1.png')); }],
            ['line' => 81, 'alt' => '', 'title' => '', 'translations' => ['homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/work/work-4.svg')); }],
            ['line' => 85, 'alt' => '', 'title' => '', 'translations' => ['homepage_new.how_we_work_express', 'homepage_new.how_we_work_standart'], 'path' => static function () { return (asset('images/work/work-5.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait/popup-order.blade.php' => [
        'images' => [
            ['line' => 58, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('images/sharj/subRow1.webp')); }],
            ['line' => 66, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_type_title'], 'path' => static function () { return (ver_asset('images/sharj/format1.svg')); }],
            ['line' => 85, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_type_paper'], 'path' => static function () { return (ver_asset('images/sharj/holstPhoto.webp')); }],
            ['line' => 100, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_type_paper'], 'path' => static function () { return (ver_asset('images/sharj/paperPhoto.webp')); }],
            ['line' => 114, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_type_hint'], 'path' => static function () { return (ver_asset('images/sharj/ficon1.webp')); }],
            ['line' => 124, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_type_hint', 'portrait_royal.form_group_upload_title'], 'path' => static function () { return (ver_asset('images/sharj/format2.svg')); }],
            ['line' => 186, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step1_bot_desc'], 'path' => static function () { return (ver_asset('images/sharj/ficon2.webp')); }],
            ['line' => 196, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step1_bot_desc', 'portrait_royal.form_group_size_title', 'portrait_royal.form_group_size_preview'], 'path' => static function () { return (ver_asset('images/sharj/format3.svg')); }],
            ['line' => 248, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step3_bot_desc'], 'path' => static function () { return (ver_asset('images/sharj/ficon3.webp')); }],
            ['line' => 258, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step3_bot_desc', 'portrait_royal.form_group_people_title'], 'path' => static function () { return (ver_asset('images/sharj/format4.svg')); }],
            ['line' => 322, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step4_bot_desc'], 'path' => static function () { return (asset('images/prompt4.png')); }],
            ['line' => 332, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step4_bot_desc', 'portrait_royal.form_group_frame_title'], 'path' => static function () { return (ver_asset('images/sharj/format5.svg')); }],
            ['line' => 456, 'alt' => 'Viar', 'title' => '', 'translations' => ['gallery.code'], 'binding' => ['model' => 'App\\Models\\CanvasRam', 'fields' => ['img'], 'media' => []]],
            ['line' => 557, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_frame_note'], 'path' => static function () { return (asset('images/prompt7.png')); }],
            ['line' => 566, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_frame_note', 'portrait_royal.form_group_frame2_title'], 'path' => static function () { return (ver_asset('images/sharj/format5.svg')); }],
            ['line' => 601, 'alt' => 'Viar', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasRam', 'fields' => ['img'], 'media' => []]],
            ['line' => 821, 'alt' => 'Viar', 'title' => '', 'translations' => ['gallery.code'], 'binding' => ['model' => 'App\\Models\\CanvasRam', 'fields' => ['img'], 'media' => []]],
            ['line' => 924, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_frame_note'], 'path' => static function () { return (asset('images/prompt7.png')); }],
            ['line' => 933, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_frame_note', 'portrait_buy_form.step_comment_title'], 'path' => static function () { return (ver_asset('images/sharj/format6.svg')); }],
            ['line' => 999, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step_comment_bot_desc'], 'path' => static function () { return (asset('images/prompt9.png')); }],
            ['line' => 1033, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 1086, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('images/sharj/cancel.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait/sizes.blade.php' => [
        'images' => [
            ['line' => 8, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset('images/sizes/size-bg.webp')); }],
            ['line' => 15, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/10.webp')); }],
            ['line' => 20, 'alt' => '', 'title' => '', 'translations' => ['portrait.sizes__title'], 'path' => static function () { return (asset('images/sizes/9.webp')); }],
        ],
    ],
    'theme/viar/pages/portrait/slider--royal.blade.php' => [
        'images' => [
            ['line' => 22, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['png'], 'media' => []]],
            ['line' => 28, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('/images/sharj/pngwing.webp')); }],
            ['line' => 43, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['fotopng'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 61, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['fotopng'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 84, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images/wing.svg')); }],
            ['line' => 185, 'alt' => 'Viar Image', 'title' => '', 'path' => static function () { return (asset('images/collage/gift.svg')); }],
            ['line' => 226, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait/slider.blade.php' => [
        'images' => [
            ['line' => 22, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['png'], 'media' => []]],
            ['line' => 28, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images/wing.svg')); }],
            ['line' => 43, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['fotopng'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 61, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['fotopng'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 75, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images/wing.svg')); }],
            ['line' => 170, 'alt' => 'Viar Image', 'title' => '', 'path' => static function () { return (asset('images/collage/gift.svg')); }],
            ['line' => 210, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images/icon/ellipse-whete.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait/style_example.blade.php' => [
        'images' => [
            ['line' => 21, 'alt' => '', 'title' => '', 'translations' => ['cart_new.obraz', 'portrait_royal.btn_order_portrait'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => [], 'media' => ['reason_example'], 'where' => ['active' => 1]]],
        ],
    ],
    'theme/viar/pages/portrait/style_example_oil.blade.php' => [
        'images' => [
            ['line' => 38, 'alt' => '', 'title' => '', 'translations' => ['cart_new.obraz', 'portrait_royal.btn_order_portrait'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => [], 'media' => ['reason_example'], 'where' => ['active' => 1]]],
        ],
    ],
    'theme/viar/pages/portrait/tabs/fifth.blade.php' => [
        'images' => [
            ['line' => 32, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__inner__title'], 'path' => static function () { return (ver_asset('images/clock.png')); }],
            ['line' => 103, 'alt' => '', 'title' => '', 'translations' => ['portrait.tab5__inner__block3_title', 'portrait.tab5__inner__block3_title_after', 'portrait.tab5__delivery__title'], 'path' => static function () { return (ver_asset('images/delivery.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/tabs/first.blade.php' => [
        'images' => [
            ['line' => 6, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/about-borderB.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/tabs/picture.blade.php' => [
        'images' => [
            ['line' => 23, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_picture_body_title', 'portrait_royal.tab_picture_body_subtitle'], 'path' => static function () { return (ver_asset('images/sharj/v1.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait/tabs/portrait_oil.blade.php' => [
        'images' => [
            ['line' => 24, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t11', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t12', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t13', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t14'], 'path' => static function () { return (ver_asset('images/sharj/v1.svg')); }],
            ['line' => 30, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t11', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t12', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t13', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t14'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/badge-bg.svg')); }],
            ['line' => 31, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t12', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t13', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t14'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/badge-frame.svg')); }],
            ['line' => 36, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t14'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/shot.png')); }],
            ['line' => 47, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t14'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/1.jpg')); }],
            ['line' => 53, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t21', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t22', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t23'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/2.jpg')); }],
            ['line' => 73, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t21', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t22', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t23', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t24'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/checked.svg')); }],
            ['line' => 77, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t21', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t22', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t23', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t24'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/checked.svg')); }],
            ['line' => 87, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t23', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t24', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t25'], 'path' => static function () { return 'https://viarcanvas.com/images/sharj/v1.svg?1691276006'; }],
            ['line' => 91, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t24', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t25'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/badge-bg.svg')); }],
            ['line' => 92, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t24', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t25'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/badge-frame.svg')); }],
            ['line' => 97, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t25'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/mail.png')); }],
            ['line' => 107, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t25', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t31', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t32', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t33'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/3.jpg')); }],
            ['line' => 126, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t31', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t32', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t33'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/checked.svg')); }],
            ['line' => 136, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t32', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t33', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t34'], 'path' => static function () { return 'https://viarcanvas.com/images/sharj/v1.svg?1691276006'; }],
            ['line' => 140, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t33', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t34'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/badge-bg.svg')); }],
            ['line' => 141, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t33', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t34'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/badge-frame.svg')); }],
            ['line' => 146, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t34'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/colors.png')); }],
            ['line' => 156, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t34', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t41', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t42', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t43'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/4.jpg')); }],
            ['line' => 184, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t41', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t42', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t43', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t44'], 'path' => static function () { return 'https://viarcanvas.com/images/sharj/v1.svg?1691276006'; }],
            ['line' => 188, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t42', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t43', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t44'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/badge-bg.svg')); }],
            ['line' => 189, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t43', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t44'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/badge-frame.svg')); }],
            ['line' => 194, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t44'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/defense.png')); }],
            ['line' => 204, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t44', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t51', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t52', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t53'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/5.jpg')); }],
            ['line' => 231, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t51', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t52', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t53', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t54'], 'path' => static function () { return 'https://viarcanvas.com/images/sharj/v1.svg?1691276006'; }],
            ['line' => 235, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t52', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t53', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t54'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/badge-bg.svg')); }],
            ['line' => 236, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t53', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t54'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/badge-frame.svg')); }],
            ['line' => 241, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t54'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/baget.png')); }],
            ['line' => 251, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t54', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t61', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t62'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/6.jpg')); }],
            ['line' => 276, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t61', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t62', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t63'], 'path' => static function () { return 'https://viarcanvas.com/images/sharj/v1.svg?1691276006'; }],
            ['line' => 280, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t61', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t62', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t63'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/badge-bg.svg')); }],
            ['line' => 281, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t62', 'pages.portrait_oil.tab_portrait_oil_tab.about__block_t63'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/badge-frame.svg')); }],
            ['line' => 286, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t63'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/delivery.png')); }],
            ['line' => 296, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_portrait_oil_tab.about__block_t63'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/7.jpg')); }],
        ],
    ],
    'theme/viar/pages/portrait/tabs/portrait_oil_two_pictures.blade.php' => [
        'images' => [
            ['line' => 11, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_two_pictures_tab.t11', 'pages.portrait_oil.tab_two_pictures_tab.t12', 'pages.portrait_oil.tab_two_pictures_tab.t13', 'pages.portrait_oil.tab_two_pictures_tab.t14'], 'path' => static function () { return (ver_asset('images/sharj/i1.svg')); }],
            ['line' => 24, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_two_pictures_tab.t13', 'pages.portrait_oil.tab_two_pictures_tab.t14', 'pages.portrait_oil.tab_two_pictures_tab.t15'], 'path' => static function () { return 'https://viarcanvas.com/images/sharj/v1.svg?1691276006'; }],
            ['line' => 33, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_two_pictures_tab.t14', 'pages.portrait_oil.tab_two_pictures_tab.t15', 'pages.portrait_oil.tab_two_pictures_tab.t16'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/t2_1.png')); }],
            ['line' => 45, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_two_pictures_tab.t15', 'pages.portrait_oil.tab_two_pictures_tab.t16', 'pages.portrait_oil.tab_two_pictures_tab.t17'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/t2_2.png')); }],
            ['line' => 57, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_two_pictures_tab.t16', 'pages.portrait_oil.tab_two_pictures_tab.t17'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/t2_3.png')); }],
            ['line' => 72, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_two_pictures_tab.t17', 'homepage_new_login_reg.or'], 'path' => static function () { return 'https://viarcanvas.com/images/sharj/v1.svg?1691276006'; }],
            ['line' => 81, 'alt' => '', 'title' => '', 'translations' => ['homepage_new_login_reg.or', 'pages.portrait_oil.tab_two_pictures_tab.t21', 'pages.portrait_oil.tab_two_pictures_tab.t22'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/t2_3.png')); }],
            ['line' => 97, 'alt' => '', 'title' => '', 'translations' => ['homepage_new_login_reg.or', 'pages.portrait_oil.tab_two_pictures_tab.t21', 'pages.portrait_oil.tab_two_pictures_tab.t22', 'pages.portrait_oil.tab_two_pictures_tab.t23', 'pages.portrait_oil.tab_two_pictures_tab.t24'], 'path' => static function () { return (ver_asset('images/sharj/i1.svg')); }],
            ['line' => 107, 'alt' => '', 'title' => '', 'translations' => ['homepage_new_login_reg.or', 'pages.portrait_oil.tab_two_pictures_tab.t21', 'pages.portrait_oil.tab_two_pictures_tab.t22', 'pages.portrait_oil.tab_two_pictures_tab.t23', 'pages.portrait_oil.tab_two_pictures_tab.t24'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/checked.svg')); }],
            ['line' => 120, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_two_pictures_tab.t21', 'pages.portrait_oil.tab_two_pictures_tab.t22', 'pages.portrait_oil.tab_two_pictures_tab.t23', 'pages.portrait_oil.tab_two_pictures_tab.t24', 'pages.portrait_oil.tab_two_pictures_tab.t25', 'pages.portrait_oil.tab_two_pictures_tab.t26'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/t2_b4.jpg')); }],
            ['line' => 124, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_two_pictures_tab.t23', 'pages.portrait_oil.tab_two_pictures_tab.t24', 'pages.portrait_oil.tab_two_pictures_tab.t25', 'pages.portrait_oil.tab_two_pictures_tab.t26', 'pages.portrait_oil.tab_two_pictures_tab.t27', 'pages.portrait_oil.tab_two_pictures_tab.t28'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/t2_b3.jpg')); }],
            ['line' => 129, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_two_pictures_tab.t24', 'pages.portrait_oil.tab_two_pictures_tab.t25', 'pages.portrait_oil.tab_two_pictures_tab.t26', 'pages.portrait_oil.tab_two_pictures_tab.t27', 'pages.portrait_oil.tab_two_pictures_tab.t28'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/checked.svg')); }],
            ['line' => 141, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_two_pictures_tab.t25', 'pages.portrait_oil.tab_two_pictures_tab.t26', 'pages.portrait_oil.tab_two_pictures_tab.t27', 'pages.portrait_oil.tab_two_pictures_tab.t28'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/checked.svg')); }],
            ['line' => 154, 'alt' => '', 'title' => '', 'translations' => ['pages.portrait_oil.tab_two_pictures_tab.t26', 'pages.portrait_oil.tab_two_pictures_tab.t27', 'pages.portrait_oil.tab_two_pictures_tab.t28'], 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/t2_b5.jpg')); }],
            ['line' => 167, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/t2_bg2.png')); }],
            ['line' => 177, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset(env('THEME') . 'images/oil/t2_bg2.png')); }],
        ],
    ],
    'theme/viar/pages/portrait/tabs/six.blade.php' => [
        'images' => [
            ['line' => 63, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('img/how-create-1.svg')); }],
            ['line' => 68, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('img/how-create-2.svg')); }],
            ['line' => 205, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('img/how-create-3.svg')); }],
            ['line' => 210, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('img/how-create-4.svg')); }],
            ['line' => 296, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('img/how-create-5.svg')); }],
            ['line' => 301, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('img/how-create-6.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait/tabs/two_types.blade.php' => [
        'images' => [
            ['line' => 40, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_title', 'portrait_royal.tab_two_types_body_subtitle', 'portrait_royal.tab_two_types_body_type_canvas'], 'path' => static function () { return (ver_asset('images/sharj/i1.svg')); }],
            ['line' => 64, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_1', 'portrait_royal.tab_two_types_body_list_2', 'portrait_royal.tab_two_types_body_list_3'], 'path' => static function () { return (ver_asset('images/sharj/ai1.webp')); }],
            ['line' => 68, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_1', 'portrait_royal.tab_two_types_body_list_2', 'portrait_royal.tab_two_types_body_list_3', 'portrait_royal.tab_two_types_body_type_canvas_border'], 'path' => static function () { return (ver_asset('images/sharj/ai2.webp')); }],
            ['line' => 72, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_1', 'portrait_royal.tab_two_types_body_list_2', 'portrait_royal.tab_two_types_body_list_3', 'portrait_royal.tab_two_types_body_type_canvas_border'], 'path' => static function () { return (ver_asset('images/sharj/ai3.webp')); }],
            ['line' => 78, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_2', 'portrait_royal.tab_two_types_body_list_3', 'portrait_royal.tab_two_types_body_type_canvas_border'], 'path' => static function () { return (ver_asset('images/sharj/i2.svg')); }],
            ['line' => 107, 'alt' => '', 'title' => '', 'translations' => ['homepage_new_login_reg.or', 'portrait_royal.tab_two_types_body_type_paper'], 'path' => static function () { return (ver_asset('images/sharj/i3.svg')); }],
            ['line' => 131, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_4', 'portrait_royal.tab_two_types_body_list_5', 'portrait_royal.tab_two_types_body_type_paper_border'], 'path' => static function () { return (ver_asset('images/sharj/ai4.webp')); }],
            ['line' => 135, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_4', 'portrait_royal.tab_two_types_body_list_5', 'portrait_royal.tab_two_types_body_type_paper_border'], 'path' => static function () { return (ver_asset('images/sharj/ai5.webp')); }],
            ['line' => 141, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_4', 'portrait_royal.tab_two_types_body_list_5', 'portrait_royal.tab_two_types_body_type_paper_border'], 'path' => static function () { return (ver_asset('images/sharj/i4.svg')); }],
        ],
    ],
    'theme/viar/pages/portrait_oil.blade.php' => [
        'images' => [
            ['line' => 32, 'alt' => 'img', 'title' => '', 'translations' => ['portrait.tabs_title'], 'path' => static function () { return (asset(env('THEME').'images/icon/ellipse-whete.svg')); }],
            ['line' => 109, 'alt' => '', 'title' => '', 'translations' => ['gallery.see'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => [], 'media' => ['reason_example'], 'where' => ['active' => 1]]],
            ['line' => 126, 'alt' => '', 'title' => '', 'translations' => ['gallery.see'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => [], 'media' => ['reason_example'], 'where' => ['active' => 1]]],
            ['line' => 178, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('img/video-image.svg')); }],
        ],
    ],
    'theme/viar/pages/review/index.blade.php' => [
        'images' => [
            ['line' => 58, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text1', 'pages.review_text14', 'pages.review_text25', 'pages.review_text26', 'pages.review_text27'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/client.svg')); }],
            ['line' => 75, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text25', 'pages.review_text26', 'pages.review_text27', 'pages.review_text15'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/1.svg')); }],
            ['line' => 96, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text0', 'pages.review_text3'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/2.svg')); }],
            ['line' => 270, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text13', 'pages.review_text17', 'pages.review_text18'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/3.svg')); }],
            ['line' => 275, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text13', 'pages.review_text17', 'pages.review_text18', 'pages.review_text30'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/f.svg')); }],
            ['line' => 280, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text17', 'pages.review_text18', 'pages.review_text30', 'pages.review_text21'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/g.svg')); }],
            ['line' => 298, 'alt' => 'img', 'title' => '', 'translations' => ['pages.review_text18', 'pages.review_text30', 'pages.review_text21', 'pages.review_text22', 'pages.review_text23'], 'path' => static function () { return (asset(config('theme.current') . '/images/icon/ellipse-black.svg')); }],
            ['line' => 310, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text30', 'pages.review_text21', 'pages.review_text22', 'pages.review_text23', 'pages.review_text24'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/o1.svg')); }],
            ['line' => 318, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text21', 'pages.review_text22', 'pages.review_text23', 'pages.review_text24'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/o2.svg')); }],
            ['line' => 326, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text21', 'pages.review_text22', 'pages.review_text23', 'pages.review_text24'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/o3.svg')); }],
            ['line' => 339, 'alt' => 'img', 'title' => '', 'translations' => ['pages.review_text22', 'pages.review_text23', 'pages.review_text24', 'pages.review_text28'], 'path' => static function () { return (asset(config('theme.current') . '/images/icon/ellipse-black.svg')); }],
            ['line' => 6, 'alt' => '', 'title' => '', 'path' => static function () { return '/images/stock/pngwing23_1.webp'; }],
            ['line' => 21, 'alt' => '', 'title' => '', 'path' => static function () { return '/images/stock/bg.webp'; }],
        ],
    ],
    'theme/viar/pages/send_rev/index.blade.php' => [
        'images' => [
            ['line' => 58, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text1', 'pages.review_text14', 'pages.review_text25', 'pages.review_text26', 'pages.review_text27'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/client.svg')); }],
            ['line' => 75, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text25', 'pages.review_text26', 'pages.review_text27', 'pages.review_text15'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/1.svg')); }],
            ['line' => 96, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text0', 'pages.review_text3'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/2.svg')); }],
            ['line' => 270, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text13', 'pages.review_text17', 'pages.review_text18'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/3.svg')); }],
            ['line' => 275, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text13', 'pages.review_text17', 'pages.review_text18', 'pages.review_text30'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/f.svg')); }],
            ['line' => 280, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text17', 'pages.review_text18', 'pages.review_text30', 'pages.review_text21'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/g.svg')); }],
            ['line' => 298, 'alt' => 'img', 'title' => '', 'translations' => ['pages.review_text18', 'pages.review_text30', 'pages.review_text21', 'pages.review_text22', 'pages.review_text23'], 'path' => static function () { return (asset(config('theme.current') . '/images/icon/ellipse-black.svg')); }],
            ['line' => 310, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text30', 'pages.review_text21', 'pages.review_text22', 'pages.review_text23', 'pages.review_text24'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/o1.svg')); }],
            ['line' => 318, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text21', 'pages.review_text22', 'pages.review_text23', 'pages.review_text24'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/o2.svg')); }],
            ['line' => 326, 'alt' => '', 'title' => '', 'translations' => ['pages.review_text21', 'pages.review_text22', 'pages.review_text23', 'pages.review_text24', 'pages.review_text28'], 'path' => static function () { return (asset(config('theme.current') . '/images/review/o3.svg')); }],
            ['line' => 339, 'alt' => 'img', 'title' => '', 'translations' => ['pages.review_text22', 'pages.review_text23', 'pages.review_text24', 'pages.review_text28'], 'path' => static function () { return (asset(config('theme.current') . '/images/icon/ellipse-black.svg')); }],
            ['line' => 6, 'alt' => '', 'title' => '', 'path' => static function () { return '/images/stock/pngwing23_1.webp'; }],
            ['line' => 21, 'alt' => '', 'title' => '', 'path' => static function () { return '/images/stock/bg.webp'; }],
        ],
    ],
    'theme/viar/pages/sharj/categories.blade.php' => [
        'images' => [
            ['line' => 43, 'alt' => '', 'title' => '', 'translations' => ['simpson.yellow_btn'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['new_main_image'], 'media' => [], 'where' => ['active' => 1]]],
        ],
    ],
    'theme/viar/pages/sharj/decoration.blade.php' => [
        'images' => [
            ['line' => 35, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate4'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/pngwing.webp'; }],
            ['line' => 54, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate5', 'sharj.translate6', 'sharj.translate7'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/i1.svg'; }],
            ['line' => 76, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate8', 'sharj.translate9', 'sharj.translate10'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/ai1.webp'; }],
            ['line' => 80, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate8', 'sharj.translate9', 'sharj.translate10', 'sharj.translate11'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/ai2.webp'; }],
            ['line' => 84, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate8', 'sharj.translate9', 'sharj.translate10', 'sharj.translate11'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/ai3.webp'; }],
            ['line' => 90, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate9', 'sharj.translate10', 'sharj.translate11'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/i2.svg'; }],
            ['line' => 117, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate96', 'sharj.translate12'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/i3.svg'; }],
            ['line' => 139, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate13', 'sharj.translate14', 'sharj.translate15'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/ai4.webp'; }],
            ['line' => 143, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate13', 'sharj.translate14', 'sharj.translate15'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/ai5.webp'; }],
            ['line' => 149, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate13', 'sharj.translate14', 'sharj.translate15'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/i4.svg'; }],
        ],
    ],
    'theme/viar/pages/sharj/default-pattern-new-items.blade.php' => [
        'images' => [
            ['line' => 25, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate18', 'sharj.translate19'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/s-pattern1.webp'; }],
            ['line' => 40, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate19', 'sharj.translate20'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/s-pattern2.webp'; }],
            ['line' => 55, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate20', 'sharj.translate21'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/s-pattern3.webp?v=1'; }],
            ['line' => 217, 'alt' => '', 'title' => '', 'translations' => ['sharj.cat_po_shablonu', 'simpson.yellow_btn'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => [], 'media' => ['our_works_new'], 'where' => ['active' => 1]]],
            ['line' => 238, 'alt' => 'img', 'title' => '', 'translations' => ['sharj.cat_po_shablonu', 'simpson.yellow_btn', 'homepage_new.top_sales_more_btn_title', 'homepage_new.hide'], 'path' => static function () { return 'https://viarcanvas.com/images/icon/load-more.png'; }],
        ],
    ],
    'theme/viar/pages/sharj/default-pattern.blade.php' => [
        'images' => [
            ['line' => 23, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate17', 'sharj.translate18', 'sharj.translate19'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/s-pattern1.webp'; }],
            ['line' => 33, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate19', 'sharj.translate20'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/s-pattern2.webp'; }],
            ['line' => 43, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate20', 'sharj.translate21'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/s-pattern3.webp?v=1'; }],
            ['line' => 164, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate1'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => [], 'media' => ['our_works_new'], 'where' => ['active' => 1]]],
        ],
    ],
    'theme/viar/pages/sharj/examples.blade.php' => [
        'images' => [
            ['line' => 67, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate30', 'homepage_new.top_sales_more_btn_title', 'homepage_new.hide'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => [], 'media' => ['our_works_new'], 'where' => ['active' => 1]]],
            ['line' => 84, 'alt' => 'img', 'title' => '', 'translations' => ['sharj.translate30', 'homepage_new.top_sales_more_btn_title', 'homepage_new.hide'], 'path' => static function () { return 'https://viarcanvas.com/images/icon/load-more.png'; }],
        ],
    ],
    'theme/viar/pages/sharj/form.blade.php' => [
        'images' => [
            ['line' => 26, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate31', 'sharj.translate32', 'simpson.simpson-form.kviz-input1.kviz-input__title'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/men.webp')); }],
            ['line' => 107, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-form.kviz-input3.kviz-input__title', 'simpson.simpson-form.kviz-input4.kviz-input__title'], 'path' => static function () { return 'https://viarcanvas.com/img/icons/phone.svg'; }],
        ],
    ],
    'theme/viar/pages/sharj/gift.blade.php' => [
        'images' => [
            ['line' => 41, 'alt' => 'Viar Image', 'title' => '', 'translations' => ['sharj.translate33', 'sharj.translate34', 'sharj.translate1', 'sharj.translate35'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/collage/gift.svg'; }],
            ['line' => 55, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate35'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/categories/gift3.webp?v=1'; }],
        ],
    ],
    'theme/viar/pages/sharj/modal.blade.php' => [
        'images' => [
            ['line' => 14, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-formalization.simpson-titleBlock'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/formTitle1.webp')); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item1.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format7.svg')); }],
            ['line' => 48, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p1.webp')); }],
            ['line' => 74, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p2.webp')); }],
            ['line' => 102, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p3.webp')); }],
            ['line' => 129, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p4.webp')); }],
            ['line' => 157, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p5.webp')); }],
            ['line' => 185, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p6.webp')); }],
            ['line' => 213, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p7.webp')); }],
            ['line' => 241, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p8.webp')); }],
            ['line' => 269, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item1.formalization-prompt--inner.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ficon1.webp')); }],
            ['line' => 288, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item3.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format1.svg')); }],
            ['line' => 314, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item3.kviz-radio1.value_for_input'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/simpsonHolst.webp')); }],
            ['line' => 336, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item3.kviz-radio2.value_for_input'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/simpsonPaper.webp')); }],
            ['line' => 351, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item3.formalization-prompt--inner.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ficon1.webp')); }],
        ],
    ],
    'theme/viar/pages/sharj/other-categories.blade.php' => [
        'images' => [
            ['line' => 26, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => ['new_main_image'], 'media' => [], 'where' => ['active' => 1]]],
        ],
    ],
    'theme/viar/pages/sharj/popup-order.blade.php' => [
        'images' => [
            ['line' => 64, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate38'], 'path' => static function () { return (ver_asset('images/sharj/subRow1.webp')); }],
            ['line' => 72, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate38'], 'path' => static function () { return (ver_asset('images/sharj/format1.svg')); }],
            ['line' => 90, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_type_paper'], 'path' => static function () { return (asset(config('theme.current') . 'images/sharj/new/categories/holstSharj.webp')); }],
            ['line' => 105, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . 'images/sharj/new/categories/paperSharj.webp')); }],
            ['line' => 119, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_type_hint'], 'path' => static function () { return (ver_asset('images/sharj/ficon1.webp')); }],
            ['line' => 129, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_type_hint', 'portrait_royal.form_group_upload_title'], 'path' => static function () { return (ver_asset('images/sharj/format2.svg')); }],
            ['line' => 191, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step1_bot_desc'], 'path' => static function () { return (ver_asset('images/sharj/ficon2.webp')); }],
            ['line' => 202, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step1_bot_desc', 'portrait_royal.form_group_size_title', 'portrait_royal.form_group_size_preview'], 'path' => static function () { return (ver_asset('images/sharj/format3.svg')); }],
            ['line' => 254, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step3_bot_desc'], 'path' => static function () { return (ver_asset('images/sharj/ficon3.webp')); }],
            ['line' => 265, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step3_bot_desc', 'portrait_royal.form_group_people_title'], 'path' => static function () { return (ver_asset('images/sharj/format4.svg')); }],
            ['line' => 323, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step4_bot_desc'], 'path' => static function () { return (asset('images/prompt4.png')); }],
            ['line' => 334, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step4_bot_desc', 'portrait_royal.form_group_frame_title'], 'path' => static function () { return (ver_asset('images/sharj/format5.svg')); }],
            ['line' => 458, 'alt' => 'Viar', 'title' => '', 'translations' => ['gallery.code'], 'binding' => ['model' => 'App\\Models\\CanvasRam', 'fields' => ['img'], 'media' => []]],
            ['line' => 559, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_frame_note'], 'path' => static function () { return (asset('images/prompt7.png')); }],
            ['line' => 568, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_frame_note', 'portrait_royal.form_group_frame2_title'], 'path' => static function () { return (ver_asset('images/sharj/format5.svg')); }],
            ['line' => 603, 'alt' => 'Viar', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasRam', 'fields' => ['img'], 'media' => []]],
            ['line' => 824, 'alt' => 'Viar', 'title' => '', 'translations' => ['gallery.code'], 'binding' => ['model' => 'App\\Models\\CanvasRam', 'fields' => ['img'], 'media' => []]],
            ['line' => 927, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_frame_note'], 'path' => static function () { return (asset('images/prompt7.png')); }],
            ['line' => 937, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_frame_note', 'portrait_buy_form.step_comment_title'], 'path' => static function () { return (ver_asset('images/sharj/format6.svg')); }],
            ['line' => 1002, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step_comment_bot_desc'], 'path' => static function () { return (asset('images/prompt9.png')); }],
            ['line' => 1038, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 1083, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('images/sharj/cancel.svg')); }],
        ],
    ],
    'theme/viar/pages/sharj/service-info.blade.php' => [
        'images' => [
            ['line' => 21, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service1.webp')); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service2.webp')); }],
            ['line' => 100, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service3.webp')); }],
            ['line' => 105, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service4.webp')); }],
            ['line' => 174, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service5.webp')); }],
            ['line' => 179, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service6.webp')); }],
            ['line' => 252, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service7.webp')); }],
            ['line' => 257, 'alt' => '', 'title' => '', 'translations' => ['simpson.service-info__simpson.service-info__row3.content-text.title', 'sharj.translate39', 'simpson.service-info__simpson.service-info__row3.content-text.p', 'sharj.translate40'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service8.webp')); }],
            ['line' => 284, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate40'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service9.webp')); }],
            ['line' => 289, 'alt' => '', 'title' => '', 'translations' => ['simpson.service-info__simpson.service-info__row4.content-text.title', 'sharj.translate41', 'simpson.service-info__simpson.service-info__row4.content-text.p', 'sharj.translate42'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service10.webp')); }],
            ['line' => 316, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate42'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service11.webp')); }],
            ['line' => 321, 'alt' => '', 'title' => '', 'translations' => ['simpson.service-info__simpson.service-info__row5.content-text.title', 'sharj.translate43', 'simpson.service-info__simpson.service-info__row5.content-text.p', 'sharj.translate40'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service12.webp')); }],
        ],
    ],
    'theme/viar/pages/sharj/slider-page.blade.php' => [
        'images' => [
            ['line' => 32, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['png'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 39, 'alt' => 'Viar Image', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharjs.png')); }],
            ['line' => 59, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate44'], 'path' => static function () { return (asset(config('theme.current') . '/images/wing.svg')); }],
            ['line' => 208, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/ellipse-whete.svg'; }],
        ],
    ],
    'theme/viar/pages/sharj/slider.blade.php' => [
        'images' => [
            ['line' => 33, 'alt' => 'Viar Image', 'title' => '', 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['png'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 45, 'alt' => 'Viar Image', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharjs.png')); }],
            ['line' => 65, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate44'], 'path' => static function () { return (asset(config('theme.current') . '/images/wing.svg')); }],
            ['line' => 207, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/icon/ellipse-whete.svg'; }],
        ],
    ],
    'theme/viar/pages/sharj/steps-order.blade.php' => [
        'images' => [
            ['line' => 56, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate48', 'sharj.translate49', 'sharj.translate50'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/express.svg'; }],
            ['line' => 60, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate48', 'sharj.translate49', 'sharj.translate50'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/standart.svg'; }],
        ],
    ],
    'theme/viar/pages/sharj/tabs.blade.php' => [
        'images' => [
            ['line' => 84, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => [], 'media' => ['new_before_items'], 'where' => ['active' => 1]]],
            ['line' => 99, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => [], 'media' => ['new_after_items'], 'where' => ['active' => 1]]],
            ['line' => 148, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate68'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/categories/c1.webp'; }],
            ['line' => 166, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate69', 'sharj.translate70'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/categories/c2.webp'; }],
            ['line' => 186, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate71'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/categories/c3.webp'; }],
            ['line' => 205, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate72'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/categories/c4.webp'; }],
            ['line' => 218, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate72', 'sharj.translate5', 'sharj.translate6'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/categories/arr.svg'; }],
            ['line' => 232, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate5', 'sharj.translate6', 'sharj.translate7'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/i1.svg'; }],
            ['line' => 258, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_1', 'portrait_royal.tab_two_types_body_list_2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/ai1.webp'; }],
            ['line' => 262, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_1', 'portrait_royal.tab_two_types_body_list_2', 'portrait_royal.tab_two_types_body_list_3'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/ai2.webp'; }],
            ['line' => 266, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_1', 'portrait_royal.tab_two_types_body_list_2', 'portrait_royal.tab_two_types_body_list_3', 'sharj.translate11'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/ai3.webp'; }],
            ['line' => 272, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_2', 'portrait_royal.tab_two_types_body_list_3', 'sharj.translate11'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/i2.svg'; }],
            ['line' => 303, 'alt' => '', 'title' => '', 'translations' => ['homepage_new_login_reg.or', 'sharj.translate12'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/i3.svg'; }],
            ['line' => 329, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_4', 'portrait_royal.tab_two_types_body_list_5'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/ai4.webp'; }],
            ['line' => 333, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_4', 'portrait_royal.tab_two_types_body_list_5', 'sharj.translate15'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/ai5.webp'; }],
            ['line' => 339, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.tab_two_types_body_list_4', 'portrait_royal.tab_two_types_body_list_5', 'sharj.translate15'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/sharj/new/page/i4.svg'; }],
            ['line' => 393, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate75', 'sharj.translate76', 'sharj.translate77'], 'path' => static function () { return '/images/about-m1.png'; }],
            ['line' => 411, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate78'], 'path' => static function () { return '/images/fit-1.jpg'; }],
            ['line' => 428, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate79'], 'path' => static function () { return '/images/fit-2.jpg'; }],
            ['line' => 445, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate80'], 'path' => static function () { return '/images/fit-3.jpg'; }],
            ['line' => 476, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate74', 'sharj.translate73', 'sharj.translate81'], 'path' => static function () { return '/images/clock.png'; }],
            ['line' => 485, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate81', 'sharj.translate82', 'sharj.translate3'], 'path' => static function () { return '/images/three-days.jpg'; }],
            ['line' => 507, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate84', 'sharj.translate85'], 'path' => static function () { return '/images/one-day.jpg'; }],
            ['line' => 525, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate85', 'sharj.translate86', 'sharj.translate87'], 'path' => static function () { return '/images/on-date.jpg'; }],
            ['line' => 542, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate86', 'sharj.translate87', 'sharj.translate59'], 'path' => static function () { return '/images/delivery.png'; }],
            ['line' => 551, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate59', 'sharj.translate82'], 'path' => static function () { return '/images/van.jpg'; }],
            ['line' => 565, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate82', 'sharj.translate88', 'sharj.translate89'], 'path' => static function () { return '/images/on-adress.jpg'; }],
            ['line' => 581, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate88', 'sharj.translate89', 'sharj.translate90', 'sharj.translate91'], 'path' => static function () { return '/images/abroad.jpg'; }],
            ['line' => 601, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate91', 'sharj.translate92', 'sharj.translate93'], 'path' => static function () { return '/images/venipak.png'; }],
            ['line' => 612, 'alt' => '', 'title' => '', 'translations' => ['sharj.translate93', 'sharj.translate94', 'sharj.translate95'], 'path' => static function () { return '/images/dpd.png'; }],
        ],
    ],
    'theme/viar/pages/simpsons/index.blade.php' => [
        'images' => [
            ['line' => 60, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson_screen.content.text2', 'simpson.simpson_screen.content.text3', 'simpson.yellow_btn', 'simpson.img_badge'], 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['png'], 'media' => []]],
            ['line' => 65, 'alt' => '', 'title' => '', 'translations' => ['simpson.yellow_btn', 'simpson.img_badge'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/i1.webp')); }],
            ['line' => 151, 'alt' => '', 'title' => '', 'translations' => ['simpson.about_tabs.about_tab4', 'simpson.about__screen__blocks.simpson_title', 'simpson.about__screen__blocks.simpson_image_text'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/arr-bottom.svg')); }],
            ['line' => 172, 'alt' => '', 'title' => '', 'translations' => ['cart_new.obraz'], 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => [], 'media' => ['background'], 'where' => ['active' => 1]]],
            ['line' => 191, 'alt' => 'img', 'title' => '', 'translations' => ['homepage_new.simpsons_top_sales_more_btn_title', 'homepage_new.hide', 'homepage_new.top_sales_more_btn_title'], 'path' => static function () { return 'https://viarcanvas.com/images/icon/load-more.png'; }],
            ['line' => 245, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block2.text1', 'simpson.about__block2.text2', 'simpson.about__block_types_row.img1.flex.text1'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/i1.svg')); }],
            ['line' => 277, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row.ul.li1', 'simpson.about__block_types_row.ul.li2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/page/ai1.webp')); }],
            ['line' => 285, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row.ul.li1', 'simpson.about__block_types_row.ul.li2', 'simpson.about__block_types_row.ul.li3'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/page/ai2.webp')); }],
            ['line' => 294, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row.ul.li1', 'simpson.about__block_types_row.ul.li2', 'simpson.about__block_types_row.ul.li3', 'simpson.about__block_types_row.img2.flex.text1'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/page/ai3.webp')); }],
            ['line' => 305, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row.ul.li2', 'simpson.about__block_types_row.ul.li3', 'simpson.about__block_types_row.img2.flex.text1'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/i2.svg')); }],
            ['line' => 345, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__screen__blocks.page_title.center_title', 'simpson.about__screen__blocks.about__block_types_row2.img.flex'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/i3.svg')); }],
            ['line' => 377, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row2.ul.li1', 'simpson.about__block_types_row2.ul.li2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/page/ai4.webp')); }],
            ['line' => 383, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row2.ul.li1', 'simpson.about__block_types_row2.ul.li2', 'simpson.about__block_types_row2.img.flex'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/page/ai5.webp')); }],
            ['line' => 391, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row2.ul.li1', 'simpson.about__block_types_row2.ul.li2', 'simpson.about__block_types_row2.img.flex'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/i4.svg')); }],
            ['line' => 638, 'alt' => '', 'title' => '', 'translations' => ['simpson.about.about_deadline.deadline_title'], 'path' => static function () { return (ver_asset('images/clock.png')); }],
            ['line' => 717, 'alt' => '', 'title' => '', 'translations' => ['simpson.about.about_deadline.deadline_items.deadline_item3.deadline_desc', 'simpson.about.about_deadline.deadline_items.deadline_item3.deadline_text', 'simpson.about.deadline_block.deadline_title'], 'path' => static function () { return (ver_asset('images/delivery.png')); }],
            ['line' => 1008, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson_pattern.simpson_pattern__row.item1.img', 'simpson.simpson_pattern.simpson_pattern__row.item2.img'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern1.webp')); }],
            ['line' => 1027, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson_pattern.simpson_pattern__row.item2.img', 'simpson.simpson_pattern.simpson_pattern__row.item3.img'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern2.webp')); }],
            ['line' => 1046, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson_pattern.simpson_pattern__row.item3.img', 'simpson.simpson_pattern.simpson_pattern__grid.col.list.text1'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern3.webp')) . '?v=1'; }],
            ['line' => 1264, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-form__inner.simpson-title', 'simpson.simpson-form__inner.p', 'simpson.simpson-form.kviz-input1.kviz-input__title'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/homer.webp')); }],
            ['line' => 1343, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-form.kviz-input3.kviz-input__title', 'simpson.simpson-form.kviz-input4.kviz-input__title', 'stock.submit'], 'path' => static function () { return 'https://viarcanvas.com/img/icons/phone.svg'; }],
            ['line' => 1441, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.title-row.simpson-title', 'simpson.steps-order-simpson.title-row.p'], 'path' => static function () { return site_image('simpsons_gift', config('theme.current') . '/images/sharj/new/simpson/gift2.png')['src']; }],
            ['line' => 1469, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.title-row.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/stepsIcon1.webp')); }],
            ['line' => 1480, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/stepsIcon2.webp')); }],
            ['line' => 1497, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item1.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item1.p2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/mask1.webp')); }],
            ['line' => 1521, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item2.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item2.p2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/mask2.webp')); }],
            ['line' => 1547, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item3.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item3.p2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/mask3.webp')); }],
            ['line' => 1572, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item4.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item4.p2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/mask4.webp')); }],
            ['line' => 1598, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item5.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item5.p2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/mask5.webp')); }],
            ['line' => 1624, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item6.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item6.p2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/mask6.webp')); }],
            ['line' => 1646, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item6.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item6.p2', 'simpson.steps-order__top.steps-order__top-item1', 'simpson.steps-order__top.steps-order__top-item2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/express.svg')); }],
            ['line' => 1655, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item6.p2', 'simpson.steps-order__top.steps-order__top-item1', 'simpson.steps-order__top.steps-order__top-item2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/standart.svg')); }],
            ['line' => 1757, 'alt' => '', 'title' => '', 'path' => static function () { return array_map(static function ($i) { return site_image('simpsons_service_' . $i, config('theme.current') . '/images/sharj/new/service' . $i . '.png')['src']; }, range(1, 6)); }],
            ['line' => 1773, 'alt' => '', 'title' => '', 'path' => static function () { return array_map(static function ($i) { return site_image('simpsons_service_' . $i, config('theme.current') . '/images/sharj/new/service' . $i . '.png')['src']; }, range(1, 6)); }],
            ['line' => 1866, 'alt' => '', 'title' => '', 'path' => static function () { return array_map(static function ($i) { return site_image('simpsons_service_' . $i, config('theme.current') . '/images/sharj/new/service' . $i . '.png')['src']; }, range(1, 6)); }],
            ['line' => 1882, 'alt' => '', 'title' => '', 'path' => static function () { return array_map(static function ($i) { return site_image('simpsons_service_' . $i, config('theme.current') . '/images/sharj/new/service' . $i . '.png')['src']; }, range(1, 6)); }],
            ['line' => 1970, 'alt' => '', 'title' => '', 'path' => static function () { return array_map(static function ($i) { return site_image('simpsons_service_' . $i, config('theme.current') . '/images/sharj/new/service' . $i . '.png')['src']; }, range(1, 6)); }],
            ['line' => 1986, 'alt' => '', 'title' => '', 'path' => static function () { return array_map(static function ($i) { return site_image('simpsons_service_' . $i, config('theme.current') . '/images/sharj/new/service' . $i . '.png')['src']; }, range(1, 6)); }],
            ['line' => 2078, 'alt' => '', 'title' => '', 'path' => static function () { return array_map(static function ($i) { return site_image('simpsons_service_' . $i, config('theme.current') . '/images/sharj/new/service' . $i . '.png')['src']; }, range(1, 6)); }],
            ['line' => 2095, 'alt' => '', 'title' => '', 'translations' => ['simpson.service-info__simpson.service-info__row3.content-text.title', 'simpson.service-info__simpson.service-info__row3.content-text.p'], 'path' => static function () { return array_map(static function ($i) { return site_image('simpsons_service_' . $i, config('theme.current') . '/images/sharj/new/service' . $i . '.png')['src']; }, range(1, 6)); }],
            ['line' => 2137, 'alt' => '', 'title' => '', 'path' => static function () { return array_map(static function ($i) { return site_image('simpsons_service_' . $i, config('theme.current') . '/images/sharj/new/service' . $i . '.png')['src']; }, range(1, 6)); }],
            ['line' => 2155, 'alt' => '', 'title' => '', 'translations' => ['simpson.service-info__simpson.service-info__row4.content-text.title', 'simpson.service-info__simpson.service-info__row4.content-text.p'], 'path' => static function () { return array_map(static function ($i) { return site_image('simpsons_service_' . $i, config('theme.current') . '/images/sharj/new/service' . $i . '.png')['src']; }, range(1, 6)); }],
            ['line' => 2196, 'alt' => '', 'title' => '', 'path' => static function () { return array_map(static function ($i) { return site_image('simpsons_service_' . $i, config('theme.current') . '/images/sharj/new/service' . $i . '.png')['src']; }, range(1, 6)); }],
            ['line' => 2214, 'alt' => '', 'title' => '', 'translations' => ['simpson.service-info__simpson.service-info__row5.content-text.title'], 'path' => static function () { return array_map(static function ($i) { return site_image('simpsons_service_' . $i, config('theme.current') . '/images/sharj/new/service' . $i . '.png')['src']; }, range(1, 6)); }],
        ],
    ],
    'theme/viar/pages/simpsons/index2.blade.php' => [
        'images' => [
            ['line' => 52, 'alt' => '', 'title' => '', 'translations' => ['simpson.img_badge'], 'binding' => ['model' => 'App\\Models\\PortraitSlider', 'fields' => ['png'], 'media' => [], 'prefix' => '', 'image_original' => true]],
            ['line' => 56, 'alt' => '', 'title' => '', 'translations' => ['simpson.img_badge'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/i1.webp')); }],
            ['line' => 120, 'alt' => '', 'title' => '', 'translations' => ['simpson.about_tabs.about_tab4', 'simpson.about__screen__blocks.simpson_title', 'simpson.about__screen__blocks.simpson_image_text', 'simpson.portrait_list__item1'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/arr-bottom.svg')); }],
            ['line' => 132, 'alt' => '', 'title' => '', 'translations' => ['simpson.portrait_list__item1', 'gallery.trybuy_btn', 'simpson.portrait_list__item2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p1.webp')); }],
            ['line' => 150, 'alt' => '', 'title' => '', 'translations' => ['simpson.portrait_list__item2', 'gallery.trybuy_btn', 'simpson.portrait_list__item3'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p2.webp')); }],
            ['line' => 169, 'alt' => '', 'title' => '', 'translations' => ['simpson.portrait_list__item3', 'gallery.trybuy_btn', 'simpson.portrait_list__item4'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p3.webp')); }],
            ['line' => 185, 'alt' => '', 'title' => '', 'translations' => ['simpson.portrait_list__item4', 'gallery.trybuy_btn', 'simpson.portrait_list__item5'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p4.webp')); }],
            ['line' => 203, 'alt' => '', 'title' => '', 'translations' => ['simpson.portrait_list__item5', 'gallery.trybuy_btn', 'simpson.portrait_list__item6'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p5.webp')); }],
            ['line' => 222, 'alt' => '', 'title' => '', 'translations' => ['simpson.portrait_list__item6', 'gallery.trybuy_btn', 'simpson.portrait_list__item7'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p6.webp')); }],
            ['line' => 241, 'alt' => '', 'title' => '', 'translations' => ['simpson.portrait_list__item7', 'gallery.trybuy_btn', 'simpson.portrait_list__item8'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p7.webp')); }],
            ['line' => 259, 'alt' => '', 'title' => '', 'translations' => ['simpson.portrait_list__item8', 'gallery.trybuy_btn'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p8.webp')); }],
            ['line' => 299, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block2.text1', 'simpson.about__block2.text2', 'simpson.about__block_types_row.img1.flex.text1'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/i1.svg')); }],
            ['line' => 310, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row.ul.li1'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/a4.webp')); }],
            ['line' => 315, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row.ul.li1', 'simpson.about__block_types_row.ul.li2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/page/ai1.webp')); }],
            ['line' => 321, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row.ul.li1', 'simpson.about__block_types_row.ul.li2', 'simpson.about__block_types_row.ul.li3'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/page/ai2.webp')); }],
            ['line' => 328, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row.ul.li1', 'simpson.about__block_types_row.ul.li2', 'simpson.about__block_types_row.ul.li3', 'simpson.about__block_types_row.img2.flex.text1'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/page/ai3.webp')); }],
            ['line' => 337, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row.ul.li2', 'simpson.about__block_types_row.ul.li3', 'simpson.about__block_types_row.img2.flex.text1'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/i2.svg')); }],
            ['line' => 349, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__screen__blocks.page_title.center_title'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/a1.webp')); }],
            ['line' => 361, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__screen__blocks.page_title.center_title', 'simpson.about__screen__blocks.about__block_types_row2.img.flex'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/i3.svg')); }],
            ['line' => 373, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row2.ul.li1'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/a3.webp')); }],
            ['line' => 378, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row2.ul.li1', 'simpson.about__block_types_row2.ul.li2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/page/ai4.webp')); }],
            ['line' => 385, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row2.ul.li1', 'simpson.about__block_types_row2.ul.li2', 'simpson.about__block_types_row2.img.flex'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/page/ai5.webp')); }],
            ['line' => 394, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__block_types_row2.ul.li1', 'simpson.about__block_types_row2.ul.li2', 'simpson.about__block_types_row2.img.flex'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/i4.svg')); }],
            ['line' => 406, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/a2.webp')); }],
            ['line' => 450, 'alt' => '', 'title' => '', 'translations' => ['simpson.about__screen__blocks.fit_block__inner.fit_content.fit_text', 'simpson.about_offers.about_offer1'], 'path' => static function () { return (asset(config('theme.current') . '/images/about-m1.png')); }],
            ['line' => 469, 'alt' => '', 'title' => '', 'translations' => ['simpson.about_offers.about_offer2'], 'path' => static function () { return (asset(config('theme.current') . '/images/fit-1.jpg')); }],
            ['line' => 488, 'alt' => '', 'title' => '', 'translations' => ['simpson.about_offers.about_offer3'], 'path' => static function () { return (asset(config('theme.current') . '/images/fit-2.jpg')); }],
            ['line' => 506, 'alt' => '', 'title' => '', 'translations' => ['simpson.about.about__notes.about__note1'], 'path' => static function () { return (asset(config('theme.current') . '/images/fit-3.jpg')); }],
            ['line' => 554, 'alt' => '', 'title' => '', 'translations' => ['simpson.about.hidden_trigger', 'simpson.about.about_deadline.deadline_title'], 'path' => static function () { return (asset(config('theme.current') . '/images/clock.png')); }],
            ['line' => 571, 'alt' => '', 'title' => '', 'translations' => ['simpson.about.about_deadline.deadline_items.deadline_item1.deadline_desc', 'simpson.about.about_deadline.deadline_items.deadline_item1.deadline_text'], 'path' => static function () { return (asset(config('theme.current') . '/images/three-days.jpg')); }],
            ['line' => 600, 'alt' => '', 'title' => '', 'translations' => ['simpson.about.about_deadline.deadline_items.deadline_item2.deadline_desc', 'simpson.about.about_deadline.deadline_items.deadline_item2.deadline_text'], 'path' => static function () { return (asset(config('theme.current') . '/images/one-day.jpg')); }],
            ['line' => 625, 'alt' => '', 'title' => '', 'translations' => ['simpson.about.about_deadline.deadline_items.deadline_item3.deadline_desc', 'simpson.about.about_deadline.deadline_items.deadline_item3.deadline_text'], 'path' => static function () { return (asset(config('theme.current') . '/images/on-date.jpg')); }],
            ['line' => 641, 'alt' => '', 'title' => '', 'translations' => ['simpson.about.about_deadline.deadline_items.deadline_item3.deadline_desc', 'simpson.about.about_deadline.deadline_items.deadline_item3.deadline_text', 'simpson.about.deadline_block.deadline_title'], 'path' => static function () { return (asset(config('theme.current') . '/images/delivery.png')); }],
            ['line' => 650, 'alt' => '', 'title' => '', 'translations' => ['simpson.about.deadline_block.deadline_title', 'simpson.about.deadline_content.deadline_desc'], 'path' => static function () { return (asset(config('theme.current') . '/images/van.jpg')); }],
            ['line' => 670, 'alt' => '', 'title' => '', 'translations' => ['simpson.about.deadline_content2.deadline_text', 'simpson.about.deadline_content2.deadline_desc'], 'path' => static function () { return (asset(config('theme.current') . '/images/on-adress.jpg')); }],
            ['line' => 695, 'alt' => '', 'title' => '', 'translations' => ['simpson.about.deadline_content3.deadline_text', 'simpson.about.deadline_content3.deadline_desc'], 'path' => static function () { return (asset(config('theme.current') . '/images/abroad.jpg')); }],
            ['line' => 721, 'alt' => '', 'title' => '', 'translations' => ['simpson.about.courier_info.courier_text', 'simpson.about.courier_item1.courier_txt'], 'path' => static function () { return (asset(config('theme.current') . '/images/venipak.png')); }],
            ['line' => 734, 'alt' => '', 'title' => '', 'translations' => ['simpson.about.courier_item1.courier_txt', 'simpson.about.courier_item2.courier_txt'], 'path' => static function () { return (asset(config('theme.current') . '/images/dpd.png')); }],
            ['line' => 792, 'alt' => 'img', 'title' => '', 'translations' => ['simpson.examples.section_frame.examples_wrap.before_after__title', 'simpson.examples.section_frame.examples_wrap.before_after__subtitle'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/ex1.webp')); }],
            ['line' => 798, 'alt' => 'img', 'title' => '', 'translations' => ['simpson.before_after__block.before_after__text'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/ex4.webp')); }],
            ['line' => 844, 'alt' => 'img', 'title' => '', 'translations' => ['simpson.examples.examples_slider__block.examples_slider__title'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/ex1.webp')); }],
            ['line' => 853, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/ex2.webp')); }],
            ['line' => 862, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/ex3.webp')); }],
            ['line' => 917, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson_pattern.simpson_pattern__row.item1.img', 'simpson.simpson_pattern.simpson_pattern__row.item2.img'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern1.webp')); }],
            ['line' => 930, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson_pattern.simpson_pattern__row.item2.img', 'simpson.simpson_pattern.simpson_pattern__row.item3.img'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern2.webp')); }],
            ['line' => 943, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson_pattern.simpson_pattern__row.item3.img', 'simpson.simpson_pattern.simpson_pattern__grid.col.list.text1'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/s-pattern3.webp')) . '?v=1'; }],
            ['line' => 1058, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-pattern__slider.portrait-list__item1', 'gallery.trybuy_btn', 'simpson.simpson-pattern__slider.portrait-list__item2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/s-slider1.webp')); }],
            ['line' => 1077, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-pattern__slider.portrait-list__item2', 'gallery.trybuy_btn', 'simpson.simpson-pattern__slider.portrait-list__item3'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/s-slider2.webp')); }],
            ['line' => 1095, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-pattern__slider.portrait-list__item3', 'gallery.trybuy_btn', 'simpson.simpson-pattern__slider.portrait-list__item4'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/s-slider3.webp')); }],
            ['line' => 1113, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-pattern__slider.portrait-list__item4', 'gallery.trybuy_btn', 'simpson.simpson-pattern__slider.portrait-list__item5'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/s-slider4.webp')); }],
            ['line' => 1131, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-pattern__slider.portrait-list__item5', 'gallery.trybuy_btn', 'simpson.simpson-pattern__slider.portrait-list__item6'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/s-slider1.webp')); }],
            ['line' => 1148, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-pattern__slider.portrait-list__item6', 'gallery.trybuy_btn'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/s-slider2.webp')); }],
            ['line' => 1194, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-form__inner.simpson-title', 'simpson.simpson-form__inner.p', 'simpson.simpson-form.kviz-input1.kviz-input__title'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/homer.webp')); }],
            ['line' => 1269, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-form.kviz-input3.kviz-input__title', 'simpson.simpson-form.kviz-input4.kviz-input__title', 'stock.submit'], 'path' => static function () { return 'https://viarcanvas.com/img/icons/phone.svg'; }],
            ['line' => 1319, 'alt' => '', 'title' => '', 'translations' => ['simpson.portrait-gift__section.portrait-content.p', 'simpson.yellow_btn', 'simpson.steps-order-simpson.title-row.simpson-title', 'simpson.steps-order-simpson.title-row.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/simpson/gift2.webp')); }],
            ['line' => 1343, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.title-row.simpson-title', 'simpson.steps-order-simpson.title-row.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/stepsIcon1.webp')); }],
            ['line' => 1348, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/stepsIcon2.webp')); }],
            ['line' => 1359, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item1.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item1.p2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/mask1.webp')); }],
            ['line' => 1377, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item1.p2', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item2.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item2.p2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/mask2.webp')); }],
            ['line' => 1397, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item2.p2', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item3.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item3.p2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/mask3.webp')); }],
            ['line' => 1416, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item3.p2', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item4.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item4.p2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/mask4.webp')); }],
            ['line' => 1436, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item4.p2', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item5.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item5.p2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/mask5.webp')); }],
            ['line' => 1456, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item5.p2', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item6.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item6.p2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/mask6.webp')); }],
            ['line' => 1476, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item6.p1', 'simpson.steps-order-simpson.simpson-steps.simpson-steps__item6.p2', 'simpson.steps-order__top.steps-order__top-item1', 'simpson.steps-order__top.steps-order__top-item2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/express.svg')); }],
            ['line' => 1483, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order-simpson.simpson-steps.simpson-steps__item6.p2', 'simpson.steps-order__top.steps-order__top-item1', 'simpson.steps-order__top.steps-order__top-item2', 'simpson.simpson-formalization.simpson-titleBlock'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/standart.svg')); }],
            ['line' => 1501, 'alt' => '', 'title' => '', 'translations' => ['simpson.steps-order__top.steps-order__top-item2', 'simpson.simpson-formalization.simpson-titleBlock'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/formTitle1.webp')); }],
            ['line' => 1513, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item1.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format7.svg')); }],
            ['line' => 1535, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p1.webp')); }],
            ['line' => 1561, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p2.webp')); }],
            ['line' => 1589, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p3.webp')); }],
            ['line' => 1616, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p4.webp')); }],
            ['line' => 1644, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p5.webp')); }],
            ['line' => 1672, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p6.webp')); }],
            ['line' => 1700, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p7.webp')); }],
            ['line' => 1728, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p8.webp')); }],
            ['line' => 1756, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item1.formalization-prompt--inner.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ficon1.webp')); }],
            ['line' => 1775, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item3.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format1.svg')); }],
            ['line' => 1801, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item3.kviz-radio1.value_for_input'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/simpsonHolst.webp')); }],
            ['line' => 1823, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item3.kviz-radio2.value_for_input'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/simpsonPaper.webp')); }],
            ['line' => 1838, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item3.formalization-prompt--inner.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ficon1.webp')); }],
            ['line' => 1917, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service1.webp')); }],
            ['line' => 1922, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service2.webp')); }],
            ['line' => 1996, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service3.webp')); }],
            ['line' => 2001, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service4.webp')); }],
            ['line' => 2070, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service5.webp')); }],
            ['line' => 2075, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service6.webp')); }],
            ['line' => 2148, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service7.webp')); }],
            ['line' => 2153, 'alt' => '', 'title' => '', 'translations' => ['simpson.service-info__simpson.service-info__row3.content-text.title', 'simpson.service-info__simpson.service-info__row3.content-text.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service8.webp')); }],
            ['line' => 2182, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service9.webp')); }],
            ['line' => 2187, 'alt' => '', 'title' => '', 'translations' => ['simpson.service-info__simpson.service-info__row4.content-text.title', 'simpson.service-info__simpson.service-info__row4.content-text.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service10.webp')); }],
            ['line' => 2215, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service11.webp')); }],
            ['line' => 2220, 'alt' => '', 'title' => '', 'translations' => ['simpson.service-info__simpson.service-info__row5.content-text.title'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/service12.webp')); }],
            ['line' => 2504, 'alt' => 'img', 'title' => '', 'translations' => ['simpson.target-frame.kviz-input2.kviz-input__title'], 'path' => static function () { return (asset(config('theme.current') . '/images/flag/lv.svg')); }],
            ['line' => 2508, 'alt' => 'img', 'title' => '', 'translations' => ['simpson.target-frame.kviz-input2.kviz-input__title'], 'path' => static function () { return (asset(config('theme.current') . '/images/flag/lv.svg')); }],
            ['line' => 2513, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/lt.svg')); }],
            ['line' => 2518, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/es.svg')); }],
            ['line' => 2523, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/fl.svg')); }],
            ['line' => 2528, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/no.svg')); }],
            ['line' => 2533, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/pl.svg')); }],
            ['line' => 2538, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/ge.svg')); }],
            ['line' => 2543, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/da.svg')); }],
            ['line' => 2548, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/cz.svg')); }],
            ['line' => 2553, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/se.svg')); }],
            ['line' => 2558, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/nl.svg')); }],
            ['line' => 2563, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/be.svg')); }],
            ['line' => 2977, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/portrait-form.png')); }],
            ['line' => 3075, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/lv.svg')); }],
            ['line' => 3079, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/lv.svg')); }],
            ['line' => 3084, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/lt.svg')); }],
            ['line' => 3089, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/es.svg')); }],
            ['line' => 3094, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/fl.svg')); }],
            ['line' => 3099, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/no.svg')); }],
            ['line' => 3104, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/pl.svg')); }],
            ['line' => 3109, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/ge.svg')); }],
            ['line' => 3114, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/da.svg')); }],
            ['line' => 3119, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/cz.svg')); }],
            ['line' => 3124, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/se.svg')); }],
            ['line' => 3129, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/nl.svg')); }],
            ['line' => 3134, 'alt' => 'img', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/flag/be.svg')); }],
            ['line' => 3196, 'alt' => 'img', 'title' => '', 'path' => static function () { return './images/icon/check-done.svg'; }],
            ['line' => 3223, 'alt' => 'ViarCanvas', 'title' => '', 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/cardreproduction/frame.jpg'; }],
        ],
    ],
    'theme/viar/pages/simpsons/modal.blade.php' => [
        'images' => [
            ['line' => 14, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-formalization.simpson-titleBlock'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/formTitle1.webp')); }],
            ['line' => 26, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item1.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format7.svg')); }],
            ['line' => 48, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p1.webp')); }],
            ['line' => 74, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p2.webp')); }],
            ['line' => 102, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p3.webp')); }],
            ['line' => 129, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p4.webp')); }],
            ['line' => 157, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p5.webp')); }],
            ['line' => 185, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p6.webp')); }],
            ['line' => 213, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p7.webp')); }],
            ['line' => 241, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/p8.webp')); }],
            ['line' => 269, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item1.formalization-prompt--inner.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ficon1.webp')); }],
            ['line' => 288, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item3.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format1.svg')); }],
            ['line' => 314, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item3.kviz-radio1.value_for_input'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/simpsonHolst.webp')); }],
            ['line' => 336, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item3.kviz-radio2.value_for_input'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/simpsonPaper.webp')); }],
            ['line' => 351, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item3.formalization-prompt--inner.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ficon1.webp')); }],
        ],
    ],
    'theme/viar/pages/simpsons/modal.blade_backup.php' => [
        'images' => [
            ['line' => 32, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/subRow2.webp')); }],
            ['line' => 42, 'alt' => '', 'title' => '', 'translations' => ['simpson.popup-wrapper.formalization-item1.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format4.svg')); }],
            ['line' => 195, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-item1.formalization-prompt--inner'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ficon4.webp')); }],
            ['line' => 210, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-item1.formalization-prompt--inner', 'simpson.popup-wrapper.formalization-item2.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format1.svg')); }],
            ['line' => 232, 'alt' => '', 'title' => '', 'translations' => ['simpson.popup-wrapper.formalization-item2.kviz-radio1.span'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/simpsonHolst.webp')); }],
            ['line' => 248, 'alt' => '', 'title' => '', 'translations' => ['simpson.popup-wrapper.formalization-item2.kviz-radio2.span'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/simpsonPaper.webp')); }],
            ['line' => 262, 'alt' => '', 'title' => '', 'translations' => ['simpson.popup-wrapper.formalization-item2.formalization-prompt--inner.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ficon1.webp')); }],
            ['line' => 276, 'alt' => '', 'title' => '', 'translations' => ['simpson.popup-wrapper.formalization-item2.formalization-prompt--inner.p', 'simpson.popup-wrapper.formalization-item3.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format2.svg')); }],
            ['line' => 337, 'alt' => '', 'title' => '', 'translations' => ['simpson.popup-wrapper.formalization-item3.formalization-prompt--inner.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ficon2.webp')); }],
            ['line' => 352, 'alt' => '', 'title' => '', 'translations' => ['simpson.popup-wrapper.formalization-item3.formalization-prompt--inner.p', 'simpson.popup-wrapper.formalization-item4.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format3.svg')); }],
            ['line' => 472, 'alt' => '', 'title' => '', 'translations' => ['simpson.popup-wrapper.formalization-item4.formalization-prompt--inner.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ficon3.webp')); }],
            ['line' => 487, 'alt' => '', 'title' => '', 'translations' => ['simpson.popup-wrapper.formalization-item4.formalization-prompt--inner.p', 'simpson.popup-wrapper.formalization-item5.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format5.svg')); }],
            ['line' => 530, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ram1.webp')); }],
            ['line' => 606, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ram1.webp')); }],
            ['line' => 687, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ram1.webp')); }],
            ['line' => 766, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ram1.webp')); }],
            ['line' => 966, 'alt' => 'Viar', 'title' => '', 'translations' => ['gallery.code'], 'path' => static function () { return 'https://viarcanvas.com/storage/canvas-rams/January2023/ECxyswCt5jI5o9cZ5ghL.jpg'; }],
            ['line' => 1049, 'alt' => 'Viar', 'title' => '', 'translations' => ['gallery.code'], 'path' => static function () { return 'https://viarcanvas.com/storage/canvas-rams/February2023/SHVhNNe38XFNZrJNzq4a.webp'; }],
            ['line' => 1151, 'alt' => '', 'title' => '', 'translations' => ['simpson.popup-wrapper.formalization-item5.formalization-prompt--inner.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/prompt7.png')); }],
            ['line' => 1167, 'alt' => '', 'title' => '', 'translations' => ['simpson.popup-wrapper.formalization-item5.formalization-prompt--inner.p', 'simpson.popup-wrapper.formalization-item6.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format6.svg')); }],
            ['line' => 1243, 'alt' => '', 'title' => '', 'translations' => ['simpson.popup-wrapper.formalization-item6.formalization-prompt--inner.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/prompt9.png')); }],
            ['line' => 1275, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/icon/info.svg')); }],
            ['line' => 1282, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/icon/info.svg')); }],
            ['line' => 1290, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/icon/info.svg')); }],
            ['line' => 1319, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/cancel.svg')); }],
        ],
    ],
    'theme/viar/pages/simpsons/popup-order.blade.php' => [
        'images' => [
            ['line' => 28, 'alt' => '', 'title' => '', 'translations' => ['simpson.simpson-formalization.simpson-titleBlock', 'portrait_royal.popup_subtitle', 'portrait_royal.popup_subtitle_2'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/formTitle1.webp')); }],
            ['line' => 45, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('images/sharj/subRow1.webp')); }],
            ['line' => 53, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item1.formalization-tab'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/format7.svg')); }],
            ['line' => 69, 'alt' => '', 'title' => '', 'binding' => ['model' => 'App\\Models\\GalleryItem', 'fields' => [], 'media' => ['background'], 'where' => ['active' => 1]]],
            ['line' => 100, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item1.formalization-prompt--inner.p'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/ficon1.webp')); }],
            ['line' => 111, 'alt' => '', 'title' => '', 'translations' => ['simpson.formalization-items.formalization-item1.formalization-prompt--inner.p', 'portrait_royal.form_group_type_title'], 'path' => static function () { return (ver_asset('images/sharj/format1.svg')); }],
            ['line' => 129, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_type_paper'], 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/simpsonHolst.webp')); }],
            ['line' => 144, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sharj/new/simpson/simpsonPaper.webp')); }],
            ['line' => 158, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_type_hint'], 'path' => static function () { return (ver_asset('images/sharj/ficon1.webp')); }],
            ['line' => 168, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_type_hint', 'portrait_royal.form_group_upload_title'], 'path' => static function () { return (ver_asset('images/sharj/format2.svg')); }],
            ['line' => 230, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step1_bot_desc'], 'path' => static function () { return (ver_asset('images/sharj/ficon2.webp')); }],
            ['line' => 240, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step1_bot_desc', 'portrait_royal.form_group_size_title', 'portrait_royal.form_group_size_preview'], 'path' => static function () { return (ver_asset('images/sharj/format3.svg')); }],
            ['line' => 310, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step3_bot_desc'], 'path' => static function () { return (ver_asset('images/sharj/ficon3.webp')); }],
            ['line' => 320, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step3_bot_desc', 'portrait_royal.form_group_people_title'], 'path' => static function () { return (ver_asset('images/sharj/format4.svg')); }],
            ['line' => 378, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step4_bot_desc'], 'path' => static function () { return (asset('images/prompt4.png')); }],
            ['line' => 389, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step4_bot_desc', 'portrait_royal.form_group_frame2_title'], 'path' => static function () { return (ver_asset('images/sharj/format5.svg')); }],
            ['line' => 424, 'alt' => 'Viar', 'title' => '', 'binding' => ['model' => 'App\\Models\\CanvasRam', 'fields' => ['img'], 'media' => []]],
            ['line' => 644, 'alt' => 'Viar', 'title' => '', 'translations' => ['gallery.code'], 'binding' => ['model' => 'App\\Models\\CanvasRam', 'fields' => ['img'], 'media' => []]],
            ['line' => 747, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_frame_note'], 'path' => static function () { return (asset('images/prompt7.png')); }],
            ['line' => 756, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_frame_note', 'portrait_royal.form_group_frame_title'], 'path' => static function () { return (ver_asset('images/sharj/format5.svg')); }],
            ['line' => 880, 'alt' => 'Viar', 'title' => '', 'translations' => ['gallery.code'], 'binding' => ['model' => 'App\\Models\\CanvasRam', 'fields' => ['img'], 'media' => []]],
            ['line' => 981, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_frame_note'], 'path' => static function () { return (asset('images/prompt7.png')); }],
            ['line' => 990, 'alt' => '', 'title' => '', 'translations' => ['portrait_royal.form_group_frame_note', 'portrait_buy_form.step_comment_title'], 'path' => static function () { return (ver_asset('images/sharj/format6.svg')); }],
            ['line' => 1055, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.step_comment_bot_desc'], 'path' => static function () { return (asset('images/prompt9.png')); }],
            ['line' => 1089, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('images/icon/info.svg')); }],
            ['line' => 1135, 'alt' => '', 'title' => '', 'path' => static function () { return (ver_asset('images/sharj/cancel.svg')); }],
        ],
    ],
    'theme/viar/pages/sizesprices/index.blade.php' => [
        'images' => [
            ['line' => 94, 'alt' => 'img', 'title' => '', 'translations' => ['pages.sizeprices.silder.text3', 'pages.sizeprices.item.style'], 'path' => static function () { return (asset(config('theme.current') . '/images/icon/ellipse-whete.svg')); }],
            ['line' => 119, 'alt' => '', 'title' => '', 'translations' => ['pages.sizeprices.item.style'], 'binding' => ['model' => 'App\\Models\\CanvasSlider', 'fields' => ['size_img'], 'media' => []]],
            ['line' => 210, 'alt' => '', 'title' => '', 'translations' => ['pages.sizeprices.item.style'], 'binding' => ['model' => 'App\\Models\\ACollageSlider', 'fields' => ['size_img'], 'media' => []]],
            ['line' => 318, 'alt' => '', 'title' => '', 'translations' => ['pages.sizeprices.item.style'], 'binding' => ['sources' => [['model' => 'App\\Models\\PortraitSlider', 'fields' => ['size_img', 'png'], 'media' => []], ['model' => 'App\\Models\\GalleryItem', 'fields' => ['images'], 'media' => [], 'where' => ['active' => 1]]]]],
        ],
    ],
    'theme/viar/pages/sizesprices/sizes.blade.php' => [
        'images' => [
            ['line' => 17, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z1.webp')); }],
            ['line' => 39, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z2.webp')); }],
            ['line' => 61, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z3.webp')); }],
            ['line' => 83, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z4.webp')); }],
            ['line' => 107, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z5.webp')); }],
            ['line' => 129, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z6.webp')); }],
            ['line' => 151, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z7.webp')); }],
            ['line' => 173, 'alt' => '', 'title' => '', 'path' => static function () { return (asset(config('theme.current') . '/images/sizesprices/z8.webp')); }],
        ],
    ],
    'theme/viar/pages/stocks/modals.blade.php' => [
        'images' => [
            ['line' => 48, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.modal_1_2', 'stock.modal_1_3', 'stock.modal_1_4'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/p1.png'; }],
            ['line' => 312, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.modal_2_10'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/c1.jpg'; }],
            ['line' => 333, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.modal_2_11', 'stock.modal_3_1'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/p2.png'; }],
            ['line' => 388, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.modal_3_2', 'stock.modal_3_3', 'stock.modal_3_4', 'stock.modal_3_5', 'stock.modal_3_6'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/p3.png'; }],
            ['line' => 404, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.modal_3_5', 'stock.modal_3_6'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/p4.png'; }],
            ['line' => 505, 'alt' => 'Viar', 'title' => '', 'path' => static function () { return (asset(env('THEME') . 'images')) . '/canvas/canvas.png'; }],
            ['line' => 608, 'alt' => 'Viar WhatsApp', 'title' => '', 'translations' => ['stock.modal_succesful_dates_title', 'stock.modal_succesful_dates_text'], 'path' => static function () { return 'https://viarcanvas.com/images/premium-icon-whatsapp.svg'; }],
            ['line' => 629, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.modal_succesful_dates_title', 'stock.modal_succesful_dates_text', 'stock.modal_succesful_friend', 'stock.modal_succesful_friend_text'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/popIcon.svg'; }],
            ['line' => 649, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.modal_succesful_friend', 'stock.modal_succesful_friend_text', 'stock.modal_facebook_thx', 'stock.modal_facebook_photo_send', 'stock.modal_facebook_sditext'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/popIcon.svg'; }],
            ['line' => 693, 'alt' => '', 'title' => '', 'translations' => ['stock.modal_facebook_photo_send', 'stock.modal_facebook_sditext'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/feedback-photo.webp'; }],
            ['line' => 750, 'alt' => '', 'title' => '', 'translations' => ['stock.modal_30_40_text1', 'stock.modal_30_40_text2', 'stock.modal_30_40_text3', 'stock.modal_stock-activation_1', 'stock.modal_stock-activation_2', 'stock.modal_error_have_coupon'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/pngwing22.webp'; }],
            ['line' => 916, 'alt' => 'Viar', 'title' => '', 'translations' => ['header.popup-date-new-second-date', 'header.popup_dates_button', 'stock.text_4_1', 'stock.modal_facebook_detail', 'stock.modal_friend_text', 'stock.text_4_4'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/popIcon.svg'; }],
            ['line' => 958, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.modal_copy_code', 'stock.modal_facebook_detail', 'stock.modal_facebook_send_screen', 'stock.modal_facebook_sale'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/p2.png'; }],
            ['line' => 1024, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.submit'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/p3.png'; }],
        ],
    ],
    'theme/viar/pages/stocks/stocks.blade.php' => [
        'images' => [
            ['line' => 58, 'alt' => 'img', 'title' => '', 'path' => static function () { return 'https://viarcanvas.com/theme/viar/images/icon/ellipse-whete.svg'; }],
            ['line' => 289, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_6_5'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/v1.svg'; }],
            ['line' => 368, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_7_2', 'stock.text_7_3'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/v2.svg'; }],
            ['line' => 436, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_8_2', 'stock.modal_3_1_podarok_button'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/v3.svg'; }],
            ['line' => 489, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_9_1', 'stock.text_9_2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/o1.png'; }],
            ['line' => 495, 'alt' => 'Viar', 'title' => '', 'translations' => ['stock.text_9_2'], 'path' => static function () { return (asset(env('THEME') . 'images')) . '/stock/v4.svg'; }],
            ['line' => 512, 'alt' => 'Viar', 'title' => '', 'binding' => ['model' => 'App\\Models\\AMailTopSale', 'fields' => ['image'], 'media' => []]],
        ],
    ],
    'theme/viar/partials/thanks_part.blade.php' => [
        'images' => [
            ['line' => 59, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.ths_text_3', 'portrait_buy_form.ths_we_will_process_your_order_instantly', 'portrait_buy_form.setting_whatsapp_phone_href', 'portrait_buy_form.ths_contact_us_now'], 'path' => static function () { return 'https://viarcanvas.com/images/deadline.svg'; }],
            ['line' => 64, 'alt' => '', 'title' => '', 'translations' => ['portrait_buy_form.ths_text_3', 'portrait_buy_form.ths_we_will_process_your_order_instantly', 'portrait_buy_form.setting_whatsapp_phone_href', 'portrait_buy_form.ths_contact_us_now'], 'path' => static function () { return 'https://viarcanvas.com/images/premium-icon-whatsapp.svg'; }],
        ],
    ],
    'theme/viar/payment_request/show.blade.php' => [
        'images' => [
            ['line' => 265, 'alt' => '', 'title' => '', 'translations' => ['account_new.payment_request_amount'], 'path' => static function () { return array_map(static function ($method) { return asset(env('THEME') . $method['img']); }, array_filter(\App\Http\Controllers\Libwebtopay\WebToPay::PAYSERA_METHODS_MAP, static function ($method) { return !empty($method['img']); })); }],
        ],
    ],
    'layots/head_new_css.blade.php' => [
        'images' => [
            ['line' => 2, 'alt' => '', 'title' => '', 'path' => static function () { return '/images/bg/gift-bg.svg'; }],
            ['line' => 2, 'alt' => '', 'title' => '', 'path' => static function () { return '/images/icon/angle-down.svg'; }],
        ],
    ],
    'partials/account/bonuses.blade.php' => [
        'images' => [
            ['line' => 24, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/bonuses-item-img1.png')); }],
            ['line' => 41, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/bonuses-item-img2.png')); }],
            ['line' => 55, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/bonuses-item-img3.png')); }],
        ],
    ],
    'partials/family/bot_styles.blade.php' => [
        'images' => [
            ['line' => 59, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/generate-title.png'; }],
        ],
    ],
    'partials/family_construtor.blade.php' => [
        'images' => [
            ['line' => 125, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/generate-title.png'; }],
        ],
    ],
    'partials/head_devs.blade.php' => [
        'images' => [
            ['line' => 454, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/popup-bg.png'; }],
            ['line' => 459, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/popup-bg.webp'; }],
            ['line' => 464, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/popup-link.png'; }],
            ['line' => 469, 'alt' => '', 'title' => '', 'path' => static function () { return '/img/popup-link.webp'; }],
            ['line' => 534, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/download-icon.png')); }],
        ],
    ],
    'partials/index_new/head_styles.blade.php' => [
        'images' => [
            ['line' => 2192, 'alt' => '', 'title' => '', 'path' => static function () { return '/images/bg/gift-bg.svg'; }],
            ['line' => 2931, 'alt' => '', 'title' => '', 'path' => static function () { return '/images/icon/angle-down.svg'; }],
        ],
    ],
    'partials/module_pics/tab1.blade.php' => [
        'images' => [
            ['line' => 29, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/split_v.svg')); }],
            ['line' => 33, 'alt' => '', 'title' => '', 'path' => static function () { return (asset('img/collage7.png')); }],
        ],
    ],
    'theme/viar/pages/collage/why.blade.php' => [
        'images' => [
            ['line' => 11, 'alt' => '', 'title' => '', 'path' => static function () { return array_values(array_filter(\Illuminate\Support\Arr::only(site_image('collage_portait-why', env('THEME').'images/bg/portrait-why.webp'), ['src', 'src_webp']))); }],
        ],
    ],
    'theme/viar/pages/index/head_styles.blade.php' => [
        'images' => [
            ['line' => 2192, 'alt' => '', 'title' => '', 'path' => static function () { return '/images/bg/gift-bg.svg'; }],
            ['line' => 2931, 'alt' => '', 'title' => '', 'path' => static function () { return '/images/icon/angle-down.svg'; }],
        ],
    ],
];
