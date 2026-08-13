<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

Container::make('post_meta', 'Home')
->where('post_type', '=', 'page')
->where('post_template', '=', 'page-main-test.php')
->add_tab(__('Header'), [
    Field::make('text', 'h_title_1', 'Title-1'),
    Field::make('complex', 'h_title_2_list', 'Titles list')
    ->set_layout('tabbed-horizontal')
    ->add_fields([
        Field::make('text', 't_text', 'text')
    ]),
    Field::make('text', 'h_subtitle', 'Subtitle'),
    Field::make('text', 'h_started_text', 'Get started text'),
    Field::make('text', 'h_started_link', 'Get started link'),
    Field::make('text', 'h_video_text', 'Video text'),
    Field::make('text', 'h_video_link', 'Video link'),
])->add_tab(__('Solutions'), [
    Field::make('text', 'hs_title', 'Section title'),
    Field::make('complex', 'solutions', '')
    ->set_layout('tabbed-horizontal')
    ->add_fields([
        Field::make('image', 'sol_image', 'image'),
        Field::make('image', 'sol_image_webp', 'image webp'),
        Field::make('text', 'sol_link', 'link'),
        Field::make('text', 'sol_title', 'title'),
        Field::make('text', 'sol_text', 'text'),
        Field::make('text', 'sol_icon', 'icon'),
        Field::make('rich_text', 'sol_text_bg', 'background text'),
    ])
])->add_tab(__('Clients'), [
    Field::make('complex', 'cl_list', '')
    ->set_layout('tabbed-horizontal')
    ->add_fields([
        Field::make('image', 'image_url', 'image'),
        Field::make('image', 'image_orig_url', 'image orig'),
])
])->add_tab(__('Founder'), [
    Field::make('text', 'fr_title', 'Title'),
    Field::make('text', 'fr_text', 'Text'),
])->add_tab(__('Team members'), [
    Field::make('text', 'tm_title', 'Title'),
    Field::make('complex', 'tm_items', '')
    ->set_layout('tabbed-horizontal')
    ->add_fields([
        Field::make('text', 'tm_icon', 'icon'),
        Field::make('text', 'tm_title', 'title'),
        Field::make('rich_text', 'tm_text', 'text'),
    ])
])->add_tab(__('Solutions-2'), [
    Field::make('text', 'prod_title', 'Title'),
    Field::make('image', 'sol_image_url', 'Main image'),
    Field::make('complex', 'sol_list', '')
    ->set_layout('tabbed-horizontal')
    ->add_fields([
        Field::make('text', 'sol_icon', 'icon'),
        Field::make('text', 'sol_title', 'title'),
        Field::make('image', 'sol_image', 'image'),
        Field::make('rich_text', 'sol_text', 'text'),
    ])
])->add_tab(__('Why choose'), [
    Field::make('text', 'wc_title', 'Title'),
    Field::make('complex', 'wc_items', '')
    ->set_layout('tabbed-horizontal')
    ->add_fields([
        Field::make('text', 'wc_icon', 'icon'),
        Field::make('text', 'wc_title', 'title'),
        Field::make('text', 'wc_link', 'link'),
        Field::make('rich_text', 'wc_text', 'text'),
    ])
])->add_tab(__('Integrations'), [
        Field::make('text', 'int_home_title', 'Title'),
        Field::make('rich_text', 'int_home_text', 'Text'),
        Field::make('text', 'int_home_btn_title', 'Button title'),
        Field::make('text', 'int_home_btn_link', 'Button link'),
])->add_tab(__('Reviews'), [
    Field::make('text', 'rev_main_title', 'Title'),
    Field::make('complex', 'reviews_post', 'Reviews')
    ->set_layout('tabbed-horizontal')
    ->add_fields([
        Field::make('image', 'image', 'image'),
        Field::make('text', 'title', 'title'),
        Field::make('text', 'content', 'text'),
])
])->add_tab(__('Ready block'), [
    Field::make('text', 'rb_title', 'Title-1'),
    Field::make('text', 'rb_sub', 'Title-2'),
    Field::make('text', 'tr_title', 'Trial title'),
]);
