<?php

use App\Models\ACollageAdvantageScreen;
use App\Models\ACollageSlider;
use App\Models\Address;
use App\Models\AllStylesPage;
use App\Models\AMailTopSale;
use App\Models\BlogAuthor;
use App\Models\BlogCategoriesShortBlock;
use App\Models\BlogPost;
use App\Models\BlogReklama;
use App\Models\CanvasInterier;
use App\Models\CanvasNew;
use App\Models\CanvasRam;
use App\Models\CanvasSlider;
use App\Models\GalleryHolst;
use App\Models\GalleryItem;
use App\Models\ModGallPage;
use App\Models\ModPhotoPage;
use App\Models\ModReprPage;
use App\Models\NewhomeService;
use App\Models\NewhomeTopSlider;
use App\Models\NewhomeTopWorkEx;
use App\Models\NewhomeWorkEx;
use App\Models\OurWork;
use App\Models\OurWorkingProcess;
use App\Models\Page;
use App\Models\PageFaq;
use App\Models\PortraitSlider;
use App\Models\Review;
use App\Models\SiteImage;
use App\Models\SliderMod;
use App\Models\SliderPhoto;
use App\Models\SliderRepr;
use App\Services\AltGeneration\PublicUrlResolver;

$routeUrl = static function (string $route, string $fallback, array $parameters = []): array {
    $url = [
        'route' => $route,
        'fallback' => $fallback,
    ];

    if (!empty($parameters)) {
        $url['parameters'] = $parameters;
    }

    return $url;
};

$stocksPageTitle = [
    'ru' => 'Акции',
    'en' => 'Special offers',
    'de' => 'Aktionen',
    'lv' => 'Akcijas',
    'lt' => 'Akcijos',
    'pl' => 'Promocje',
    'ee' => 'Kampaaniad',
];

$stocksPurpose = [
    'ru' => 'Акции и специальные предложения ViarCanvas',
    'en' => 'ViarCanvas special offers and discounts',
    'de' => 'Aktionen und Sonderangebote von ViarCanvas',
    'lv' => 'ViarCanvas akcijas un īpašie piedāvājumi',
    'lt' => 'ViarCanvas akcijos ir specialūs pasiūlymai',
    'pl' => 'Promocje i oferty specjalne ViarCanvas',
    'ee' => 'ViarCanvas kampaaniad ja eripakkumised',
];

$stocksCategory = [
    'ru' => 'Акции и скидки',
    'en' => 'Special offers',
    'de' => 'Aktionen und Rabatte',
    'lv' => 'Akcijas un atlaides',
    'lt' => 'Akcijos ir nuolaidos',
    'pl' => 'Promocje i rabaty',
    'ee' => 'Kampaaniad ja soodustused',
];

$stocksHeroBlock = [
    'ru' => 'главный баннер',
    'en' => 'hero banner',
    'de' => 'Hauptbanner',
    'lv' => 'galvenais baneris',
    'lt' => 'pagrindinis baneris',
    'pl' => 'główny baner',
    'ee' => 'põhibänner',
];

$stocksMobileHeroBlock = [
    'ru' => 'мобильный баннер',
    'en' => 'mobile banner',
    'de' => 'mobiler Banner',
    'lv' => 'mobilais baneris',
    'lt' => 'mobilusis baneris',
    'pl' => 'baner mobilny',
    'ee' => 'mobiilibänner',
];

$stocksMethodBlock = [
    'ru' => 'блок условий акции',
    'en' => 'promotion conditions block',
    'de' => 'Block mit Aktionsbedingungen',
    'lv' => 'akcijas nosacījumu bloks',
    'lt' => 'akcijos sąlygų blokas',
    'pl' => 'blok warunków promocji',
    'ee' => 'kampaania tingimuste plokk',
];

$stocksBonusBlock = [
    'ru' => 'акционное предложение',
    'en' => 'bonus offer',
    'de' => 'Aktionsangebot',
    'lv' => 'akcijas piedāvājums',
    'lt' => 'akcijos pasiūlymas',
    'pl' => 'oferta promocyjna',
    'ee' => 'kampaaniapakkumine',
];

$addPlacement = static function (
    array &$placements,
    string $page,
    string $position,
    array $url,
    $block,
    $contextText,
    $pageTitle = null,
    $purpose = null,
    $category = null
): void {
    $placements[$page][$position] = [
        'url' => $url,
        'block' => $block,
        'context_text' => $contextText,
    ];

    if ($pageTitle !== null) {
        $placements[$page][$position]['page_title'] = $pageTitle;
    }

    if ($purpose !== null) {
        $placements[$page][$position]['purpose'] = $purpose;
    }

    if ($category !== null) {
        $placements[$page][$position]['category'] = $category;
    }
};

$siteImagePlacements = [
    'home' => [
        'home_banner_1' => [
            'url' => $routeUrl('home', '/'),
            'block' => 'hero',
            'context_text' => 'Home page happy-customer hero background.',
        ],
        'home_kviz_1' => [
            'url' => $routeUrl('home', '/'),
            'block' => 'quiz',
            'context_text' => 'Home page quiz visual option 1.',
        ],
        'home_kviz_2' => [
            'url' => $routeUrl('home', '/'),
            'block' => 'quiz',
            'context_text' => 'Home page quiz visual option 2.',
        ],
        'home_kviz_3' => [
            'url' => $routeUrl('home', '/'),
            'block' => 'quiz',
            'context_text' => 'Home page quiz visual option 3.',
        ],
        'home_why_block' => [
            'url' => $routeUrl('home', '/'),
            'block' => 'why',
            'context_text' => 'Home page why-choose-us image.',
        ],
        'home_15_min' => [
            'url' => $routeUrl('home', '/'),
            'block' => 'form',
            'context_text' => 'Home page quick consultation form image.',
        ],
        'home_banner_2' => [
            'url' => $routeUrl('home', '/'),
            'block' => 'works',
            'context_text' => 'Home page work examples background.',
        ],
        'home_certificate' => [
            'url' => $routeUrl('home', '/'),
            'block' => 'reviews',
            'context_text' => 'Home page certificate and reviews block image.',
        ],
    ],

    'about' => [
        'about_slide' => [
            'url' => $routeUrl('about', '/about'),
            'block' => 'hero',
            'context_text' => 'About page hero background.',
        ],
    ],

    'faq' => [
        'faq_q1' => [
            'url' => $routeUrl('faq', '/faq'),
            'block' => 'faq',
            'context_text' => 'FAQ page question illustration.',
        ],
        'faq_q1_mob' => [
            'url' => $routeUrl('faq', '/faq'),
            'block' => 'faq',
            'context_text' => 'FAQ page mobile question illustration.',
        ],
    ],

    'gallery' => [
        'mcard_about_block_1' => [
            'url' => $routeUrl('gallery.index', '/gallery'),
            'block' => 'gallery item about',
            'context_text' => 'Gallery item page about block image 1.',
        ],
        'mcard_about_block_2' => [
            'url' => $routeUrl('gallery.index', '/gallery'),
            'block' => 'gallery item about',
            'context_text' => 'Gallery item page about block image 2.',
        ],
        'mcard_about_block_3' => [
            'url' => $routeUrl('gallery.index', '/gallery'),
            'block' => 'gallery item about',
            'context_text' => 'Gallery item page about block image 3.',
        ],
    ],

    'canvas' => [
        'canvas_advantages' => [
            'url' => $routeUrl('canvas', '/new/canvas'),
            'block' => 'advantages',
            'context_text' => 'Canvas page advantages block image.',
        ],
        'canvas_1_size' => [
            'url' => $routeUrl('canvas', '/new/canvas'),
            'block' => 'requirements',
            'context_text' => 'Canvas page minimum photo size example.',
        ],
        'canvas_2_social' => [
            'url' => $routeUrl('canvas', '/new/canvas'),
            'block' => 'requirements',
            'context_text' => 'Canvas page social network photo example.',
        ],
        'canvas_3_selfie' => [
            'url' => $routeUrl('canvas', '/new/canvas'),
            'block' => 'requirements',
            'context_text' => 'Canvas page selfie example.',
        ],
        'canvas_4_social_min' => [
            'url' => $routeUrl('canvas', '/new/canvas'),
            'block' => 'requirements',
            'context_text' => 'Canvas page small social photo example.',
        ],
    ],

    'collage' => [
        'collage_portait-why' => [
            'url' => $routeUrl('collage', '/collage'),
            'block' => 'why',
            'context_text' => 'Collage page why-order collage image.',
        ],
        'collage_f_order_1' => [
            'url' => $routeUrl('collage', '/collage'),
            'block' => 'order',
            'context_text' => 'Collage page order screen example 1.',
        ],
        'collage_f_order_2' => [
            'url' => $routeUrl('collage', '/collage'),
            'block' => 'order',
            'context_text' => 'Collage page order screen example 2.',
        ],
        'collage_f_order_3' => [
            'url' => $routeUrl('collage', '/collage'),
            'block' => 'order',
            'context_text' => 'Collage page order screen example 3.',
        ],
        'collage_f_order_4' => [
            'url' => $routeUrl('collage', '/collage'),
            'block' => 'order',
            'context_text' => 'Collage page order screen example 4.',
        ],
    ],

    'gift_card' => [
        'gift_card_slide' => [
            'url' => $routeUrl('gift_card_new', '/new/gift-card'),
            'block' => 'hero',
            'context_text' => 'Gift card page hero image.',
        ],
        'gift_card_slide_mob' => [
            'url' => $routeUrl('gift_card_new', '/new/gift-card'),
            'block' => 'hero',
            'context_text' => 'Gift card page mobile hero image.',
        ],
        'gift_card_gift_card' => [
            'url' => $routeUrl('gift_card_new', '/new/gift-card'),
            'block' => 'card preview',
            'context_text' => 'Gift card page gift card preview.',
        ],
    ],

    'stocks' => [
        'stocks_slide' => [
            'url' => $routeUrl('new_stocks', '/stocks'),
            'block' => $stocksHeroBlock,
            'page_title' => $stocksPageTitle,
            'purpose' => $stocksPurpose,
            'category' => $stocksCategory,
            'context_text' => [
                'ru' => 'Главный баннер страницы акций ViarCanvas.',
                'en' => 'ViarCanvas special offers page hero banner.',
                'de' => 'Hauptbanner der Aktionsseite von ViarCanvas.',
                'lv' => 'ViarCanvas akciju lapas galvenais baneris.',
                'lt' => 'ViarCanvas akcijų puslapio pagrindinis baneris.',
                'pl' => 'Główny baner strony promocji ViarCanvas.',
                'ee' => 'ViarCanvas kampaanialehe põhibänner.',
            ],
        ],
        'stocks_slide_mob' => [
            'url' => $routeUrl('new_stocks', '/stocks'),
            'block' => $stocksMobileHeroBlock,
            'page_title' => $stocksPageTitle,
            'purpose' => $stocksPurpose,
            'category' => $stocksCategory,
            'context_text' => [
                'ru' => 'Мобильный баннер страницы акций ViarCanvas.',
                'en' => 'ViarCanvas special offers page mobile banner.',
                'de' => 'Mobiler Banner der Aktionsseite von ViarCanvas.',
                'lv' => 'ViarCanvas akciju lapas mobilais baneris.',
                'lt' => 'ViarCanvas akcijų puslapio mobilusis baneris.',
                'pl' => 'Mobilny baner strony promocji ViarCanvas.',
                'ee' => 'ViarCanvas kampaanialehe mobiilibänner.',
            ],
        ],
    ],
];

foreach ([
             'delivery_block_1_1' => 'Delivery page hero block image.',
             'delivery_block_1_info' => 'Delivery page first information block image.',
             'delivery_block_2_1' => 'Delivery page method image 1.',
             'delivery_block_2_2' => 'Delivery page method image 2.',
             'delivery_block_2_3' => 'Delivery page method image 3.',
             'delivery_block_2_info' => 'Delivery page second information block image.',
             'delivery_block_3_1' => 'Delivery page city delivery block image.',
             'delivery_block_3_info' => 'Delivery page third information block image.',
             'delivery_block_4_1' => 'Delivery page international delivery image 1.',
             'delivery_block_4_2' => 'Delivery page international delivery image 2.',
             'delivery_block_4_3' => 'Delivery page international delivery image 3.',
             'delivery_block_4_info' => 'Delivery page fourth information block image.',
         ] as $position => $contextText) {
    $addPlacement($siteImagePlacements, 'delivery', $position, $routeUrl('delivery_page', '/page/delivery'), 'delivery', $contextText);
    $addPlacement($siteImagePlacements, 'delivery', $position . '_mob', $routeUrl('delivery_page', '/page/delivery'), 'delivery', 'Mobile ' . lcfirst($contextText));
}

$deliveryPageTitle = [
    'ru' => 'Доставка',
    'en' => 'Delivery',
    'de' => 'Lieferung',
    'lv' => 'Piegāde',
    'lt' => 'Pristatymas',
    'pl' => 'Dostawa',
    'ee' => 'Tarne',
];

$deliveryWarrantyPurpose = [
    'ru' => 'Гарантия качества и возврата фото на холсте, портретов и карикатур',
    'en' => 'Quality guarantee and return conditions for canvas prints, portraits and caricatures',
    'de' => 'Qualitätsgarantie und Rückgabebedingungen für Leinwandbilder, Porträts und Karikaturen',
    'lv' => 'Kvalitātes garantija un atgriešanas nosacījumi kanvām, portretiem un karikatūrām',
    'lt' => 'Kokybės garantija ir grąžinimo sąlygos drobėms, portretams ir karikatūroms',
    'pl' => 'Gwarancja jakości i warunki zwrotu obrazów na płótnie, portretów i karykatur',
    'ee' => 'Kvaliteedigarantii ja tagastustingimused lõuendiprintidele, portreedele ja karikatuuridele',
];

$deliveryWarrantyCategory = [
    'ru' => 'Доставка и гарантия',
    'en' => 'Delivery and guarantee',
    'de' => 'Lieferung und Garantie',
    'lv' => 'Piegāde un garantija',
    'lt' => 'Pristatymas ir garantija',
    'pl' => 'Dostawa i gwarancja',
    'ee' => 'Tarne ja garantii',
];

$deliveryWarrantyBlock = [
    'ru' => 'условие возврата',
    'en' => 'return condition',
    'de' => 'Rückgabebedingung',
    'lv' => 'atgriešanas nosacījums',
    'lt' => 'grąžinimo sąlyga',
    'pl' => 'warunek zwrotu',
    'ee' => 'tagastustingimus',
];

foreach ([
    'delivery_block_4_1' => 'pages.delivery_third_block_box1',
    'delivery_block_4_2' => 'pages.delivery_third_block_box2',
    'delivery_block_4_3' => 'pages.delivery_third_block_box3',
] as $position => $translationKey) {
    foreach ([$position, $position . '_mob'] as $placementKey) {
        if (!isset($siteImagePlacements['delivery'][$placementKey]) || !is_array($siteImagePlacements['delivery'][$placementKey])) {
            continue;
        }

        $siteImagePlacements['delivery'][$placementKey]['block'] = $deliveryWarrantyBlock;
        $siteImagePlacements['delivery'][$placementKey]['page_title'] = $deliveryPageTitle;
        $siteImagePlacements['delivery'][$placementKey]['purpose'] = $deliveryWarrantyPurpose;
        $siteImagePlacements['delivery'][$placementKey]['category'] = $deliveryWarrantyCategory;
        $siteImagePlacements['delivery'][$placementKey]['context_translation_key'] = $translationKey;
    }
}

foreach (range(1, 6) as $number) {
    $addPlacement(
        $siteImagePlacements,
        'gift_card',
        'gift_card_step_' . $number,
        $routeUrl('gift_card_new', '/new/gift-card'),
        'advantages',
        'Gift card page advantage image ' . $number . '.'
    );
}

foreach (range(1, 3) as $number) {
    $addPlacement(
        $siteImagePlacements,
        'stocks',
        'stocks_block_1_' . $number,
        $routeUrl('new_stocks', '/stocks'),
        $stocksMethodBlock,
        [
            'ru' => 'Страница акций ViarCanvas, блок с условиями акции, изображение ' . $number . '.',
            'en' => 'ViarCanvas special offers page, promotion conditions block image ' . $number . '.',
            'de' => 'Aktionsseite von ViarCanvas, Block mit Aktionsbedingungen, Bild ' . $number . '.',
            'lv' => 'ViarCanvas akciju lapa, akcijas nosacījumu bloks, attēls ' . $number . '.',
            'lt' => 'ViarCanvas akcijų puslapis, akcijos sąlygų blokas, vaizdas ' . $number . '.',
            'pl' => 'Strona promocji ViarCanvas, blok warunków promocji, obraz ' . $number . '.',
            'ee' => 'ViarCanvas kampaanialeht, kampaania tingimuste plokk, pilt ' . $number . '.',
        ],
        $stocksPageTitle,
        $stocksPurpose,
        $stocksCategory
    );
}

foreach (['stocks_bonus_1_1', 'stocks_bonus_1_2', 'stocks_bonus_2_1', 'stocks_bonus_2_2', 'stocks_bonus_3_1'] as $position) {
    $addPlacement(
        $siteImagePlacements,
        'stocks',
        $position,
        $routeUrl('new_stocks', '/stocks'),
        $stocksBonusBlock,
        [
            'ru' => 'Страница акций ViarCanvas, изображение акционного предложения.',
            'en' => 'ViarCanvas special offers page, bonus offer image.',
            'de' => 'Aktionsseite von ViarCanvas, Bild eines Aktionsangebots.',
            'lv' => 'ViarCanvas akciju lapa, akcijas piedāvājuma attēls.',
            'lt' => 'ViarCanvas akcijų puslapis, akcijos pasiūlymo vaizdas.',
            'pl' => 'Strona promocji ViarCanvas, obraz oferty promocyjnej.',
            'ee' => 'ViarCanvas kampaanialeht, kampaaniapakkumise pilt.',
        ],
        $stocksPageTitle,
        $stocksPurpose,
        $stocksCategory
    );
}

foreach (range(1, 9) as $number) {
    $addPlacement(
        $siteImagePlacements,
        'canvas',
        'canvas_about_tab_about_deadline_' . $number,
        $routeUrl('canvas', '/new/canvas'),
        'delivery deadline',
        'Canvas page deadline and delivery option image ' . $number . '.'
    );
    $addPlacement(
        $siteImagePlacements,
        'portrait',
        'portrait_about_tab_about_deadline_' . $number,
        $routeUrl('graphic_portrait.new_page', '/new/graphic-portrait/portrait', ['slug' => 'portrait']),
        'delivery deadline',
        'Portrait page deadline and delivery option image ' . $number . '.'
    );
    $addPlacement(
        $siteImagePlacements,
        'simpsons',
        'canvas_about_tab_about_deadline_' . $number,
        $routeUrl('simpsons', '/simpsons'),
        'delivery deadline',
        'Simpsons portrait page deadline and delivery option image ' . $number . '.'
    );
}

foreach (range(1, 7) as $number) {
    $position = 'collage_order_content_' . $number;
    $addPlacement($siteImagePlacements, 'collage', $position, $routeUrl('collage', '/collage'), 'order', 'Collage page order content image ' . $number . '.');
    $addPlacement($siteImagePlacements, 'collage', $position . '_mob', $routeUrl('collage', '/collage'), 'order', 'Collage page mobile order content image ' . $number . '.');
}

foreach ([
             'portrait_historical_on_canvas',
             'portrait_historical_on_canvas_in_a_baguette_frame',
             'portrait_historical_on_paper_in_a_simple_frame',
             'portrait_historical_on_paper_in_a_premium_frame',
         ] as $position) {
    $addPlacement($siteImagePlacements, 'portrait', $position, $routeUrl('graphic_portrait.new_page', '/new/graphic-portrait/portrait', ['slug' => 'portrait']), 'format', 'Portrait page product format image.');
    $addPlacement($siteImagePlacements, 'portrait', $position . '_mob', $routeUrl('graphic_portrait.new_page', '/new/graphic-portrait/portrait', ['slug' => 'portrait']), 'format', 'Portrait page mobile product format image.');
}

foreach ([
             'caricature_on_canvas',
             'caricature_on_canvas_in_a_baguette_frame',
             'caricature_on_paper_in_a_simple_frame',
             'caricature_on_paper_in_a_premium_frame',
         ] as $position) {
    $addPlacement($siteImagePlacements, 'sharj', $position, $routeUrl('graphic_portrait.new_page', '/new/graphic-portrait/portrait-caricature', ['slug' => 'portrait-caricature']), 'format', 'Caricature page product format image.');
    $addPlacement($siteImagePlacements, 'sharj', $position . '_mob', $routeUrl('graphic_portrait.new_page', '/new/graphic-portrait/portrait-caricature', ['slug' => 'portrait-caricature']), 'format', 'Caricature page mobile product format image.');
}

foreach ([
             'simpsons_on_canvas',
             'simpsons_on_canvas_in_a_baguette_frame',
             'simpsons_on_paper_in_a_simple_frame',
             'simpsons_on_paper_in_a_premium_frame',
         ] as $position) {
    $addPlacement($siteImagePlacements, 'simpsons', $position, $routeUrl('simpsons', '/simpsons'), 'format', 'Simpsons portrait page product format image.');
    $addPlacement($siteImagePlacements, 'simpsons', $position . '_mob', $routeUrl('simpsons', '/simpsons'), 'format', 'Simpsons portrait page mobile product format image.');
}

foreach (range(0, 3) as $number) {
    $position = 'simpsons_snapshot_requirement_' . $number;
    $addPlacement($siteImagePlacements, 'portrait', $position, $routeUrl('graphic_portrait.new_page', '/new/graphic-portrait/portrait', ['slug' => 'portrait']), 'requirements', 'Portrait page snapshot requirement image ' . $number . '.');
    $addPlacement($siteImagePlacements, 'portrait', $position . '_mob', $routeUrl('graphic_portrait.new_page', '/new/graphic-portrait/portrait', ['slug' => 'portrait']), 'requirements', 'Portrait page mobile snapshot requirement image ' . $number . '.');
    $addPlacement($siteImagePlacements, 'simpsons', $position, $routeUrl('simpsons', '/simpsons'), 'requirements', 'Simpsons portrait page snapshot requirement image ' . $number . '.');
    $addPlacement($siteImagePlacements, 'simpsons', $position . '_mob', $routeUrl('simpsons', '/simpsons'), 'requirements', 'Simpsons portrait page mobile snapshot requirement image ' . $number . '.');
}

foreach (range(1, 5) as $number) {
    $position = 'caricature_ordering_step_' . $number;
    $addPlacement($siteImagePlacements, 'sharj', $position, $routeUrl('graphic_portrait.new_page', '/new/graphic-portrait/portrait-caricature', ['slug' => 'portrait-caricature']), 'ordering steps', 'Caricature page ordering step image ' . $number . '.');
    $addPlacement($siteImagePlacements, 'sharj', $position . '_mob', $routeUrl('graphic_portrait.new_page', '/new/graphic-portrait/portrait-caricature', ['slug' => 'portrait-caricature']), 'ordering steps', 'Caricature page mobile ordering step image ' . $number . '.');
}

foreach (range(1, 4) as $number) {
    $addPlacement(
        $siteImagePlacements,
        'portrait',
        'portrait_historical_ordering_step_' . $number,
        $routeUrl('graphic_portrait.new_page', '/new/graphic-portrait/portrait', ['slug' => 'portrait']),
        'ordering steps',
        'Portrait page ordering step image ' . $number . '.'
    );
}

foreach ([
             'portrait_historical_example' => 'Portrait page large example image.',
             'portrait_historical_example_mob' => 'Portrait page mobile large example image.',
             'portrait_historical_gift' => 'Portrait page gift block image.',
             'portrait_historical_gift_mob' => 'Portrait page mobile gift block image.',
             'portrait_historical_royal_special' => 'Portrait page royal style special image.',
             'portrait_historical_royal_special_mob' => 'Portrait page mobile royal style special image.',
         ] as $position => $contextText) {
    $addPlacement(
        $siteImagePlacements,
        'portrait',
        $position,
        $routeUrl('graphic_portrait.new_page', '/new/graphic-portrait/portrait', ['slug' => 'portrait']),
        'content',
        $contextText
    );
}

foreach (['simpsons_gift', 'simpsons_gift_mob'] as $position) {
    $addPlacement($siteImagePlacements, 'simpsons', $position, $routeUrl('simpsons', '/simpsons'), 'gift', 'Simpsons portrait page gift block image.');
}

foreach (range(1, 12) as $number) {
    $position = 'simpsons_service_' . $number;
    $addPlacement($siteImagePlacements, 'simpsons', $position, $routeUrl('simpsons', '/simpsons'), 'services', 'Simpsons portrait page service image ' . $number . '.');
    $addPlacement($siteImagePlacements, 'simpsons', $position . '_mob', $routeUrl('simpsons', '/simpsons'), 'services', 'Simpsons portrait page mobile service image ' . $number . '.');
}

return [
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'fallback_model' => 'gpt-4o',
        'timeout' => 60,
    ],

    'queue' => env('ALT_GEN_QUEUE', 'alt-gen'),

    'page_fetch_delay_ms' => (int) env('ALT_GEN_PAGE_FETCH_DELAY_MS', 1000),
    'page_fetch_timeout_seconds' => (int) env('ALT_GEN_PAGE_FETCH_TIMEOUT', 10),
    'page_fetch_retries' => (int) env('ALT_GEN_PAGE_FETCH_RETRIES', 2),

    'languages' => array_values(array_filter(array_map('trim', explode(',', (string) env('ALT_GEN_LOCALES', ''))))),

    'locale_fallback' => [
        'supported_locales' => array_values(array_filter(array_map('trim', explode(',', (string) env('ALT_GEN_LOCALES', 'ru,en,de,lv,lt,pl,ee'))))),
        'default_locale' => env('ALT_GEN_DEFAULT_LOCALE'),
    ],

    'limits' => [
        'alt_max' => 125,
        'title_max' => 70,
    ],

    'project_context' => <<<'TEXT'
ViarCanvas / Viar is a custom wall art and personalized gift brand. The catalog includes canvas prints, photo paintings, portraits, collages, modular paintings, oil-style artwork, caricatures, reproductions, and gift cards. Image alt and title text should be accurate, useful, warm, and natural. It should help shoppers and search engines understand the visible artwork or product in the current page language without sounding mechanical or over-optimized.
TEXT,

    'rules' => [
        'Describe the real visible content of the image; do not invent people, objects, styles, colors, or settings.',
        'Match the page language and keep the wording natural for that language.',
        'Avoid keyword stuffing, repeated product phrases, and lists of search terms.',
        'Mention Viar, ViarCanvas, canvas, portrait, collage, or gift products only when the image or page context makes it relevant.',
        'Prefer concise, specific alt text that remains readable for screen readers.',
        'Use title text as a short supporting label, not as a duplicate of the alt text.',
        'Avoid phrases like "image of" or "picture of" unless needed for clarity.',
        'Preserve meaningful proper names, artwork titles, and product names from page context.',
        'For decorative or non-informative images, suggest an empty alt value and explain the reason in prompt context.',
        'Never include prices, promotions, delivery claims, or guarantees unless they are visibly part of the image.',
    ],

    'auto_apply' => false,

    'auto_observer' => env('ALT_GEN_AUTO_OBSERVER', false),

    'daily_call_limit' => 2000,

    'exclude' => [
        'path_prefixes' => [
            'admin/',
            'admin/user_images/',
            'orders/',
            'order/',
            'users/avatars/',
            'user-upload/',
            'user_uploads/',
            'user_images/',
            'tmp/',
            'private/',
            'invoices/',
            'invoice/',
            'payments/',
            'payment/',
        ],
        'extensions' => ['.pdf', '.zip'],
        'file_size' => [
            'min_bytes' => 2048,
            'max_bytes' => 8 * 1024 * 1024,
        ],
        'exclude_patterns' => [
            '#(^|/)avatars?/#i',
            '#(^|/)admin/#i',
            '#(^|/)user_images/#i',
            '#(^|/)user[-_]?uploads?/#i',
            '#(^|/)orders?/#i',
            '#(^|/)invoices?/#i',
            '#(^|/)payments?/#i',
            '#(^|/)tmp/#i',
            '#(^|/)private/#i',
            '#(^|/)icon(s)?/#i',
            '#(^|/)sprite\.svg$#i',
        ],
    ],

    'site_image_placements' => $siteImagePlacements,

    'targets' => [
        BlogPost::class => [
            'enabled' => true,
            'image_fields' => ['image', 'image_preview'],
            'gallery_fields' => [],
            'html_fields' => ['text'],
            'context' => [
                'title' => ['title', 'meta_title'],
                'description' => ['excerpt', 'meta_desc'],
                'body' => ['text'],
                'category' => ['tags'],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'blogPost'],
            'language' => '@app_locale',
        ],

        BlogAuthor::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['name'],
                'description' => ['description', 'bio'],
                'body' => ['description', 'bio'],
                'category' => [],
                'purpose' => ['name'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'blog'],
            'language' => '@app_locale',
        ],

        BlogCategoriesShortBlock::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => [],
            'html_fields' => ['text_top', 'text_right', 'text_bottom'],
            'context' => [
                'title' => ['title'],
                'description' => ['text_top', 'text_right', 'text_bottom'],
                'body' => ['text_top', 'text_right', 'text_bottom'],
                'category' => [],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'blogCategoryAll'],
            'language' => '@app_locale',
        ],

        BlogReklama::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['title'],
                'description' => ['promo_text', 'size'],
                'body' => ['promo_text'],
                'category' => ['type_id'],
                'purpose' => ['btn_text'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'blog'],
            'language' => '@app_locale',
        ],

        AMailTopSale::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['title'],
                'description' => ['size', 'price'],
                'body' => ['title'],
                'category' => [],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'blog'],
            'language' => '@app_locale',
        ],

        GalleryItem::class => [
            'enabled' => true,
            'image_fields' => [
                'add_image1',
                'add_image_bg',
                'add_image2_inner',
                'add_image3_inner',
                'etc_style_image',
                'background',
                'new_main_image',
                'new_image1',
                'new_image2',
                'new_image1_inner',
                'new_image2_inner',
                'new_image3_inner',
            ],
            'gallery_fields' => ['images', 'our_works', 'backgrounds'],
            'html_fields' => ['description', 'short_desc', 'item_text', 'seo'],
            'media_collections' => [
                'new_prices_sizes',
                'new_sizes',
                'works_examples_new',
                'our_works_new',
                'background',
                'reason_example',
                'new_before_items',
                'new_after_items',
                'new_clients_works',
            ],
            'context' => [
                'title' => ['name', 'shortname', 'meta_title'],
                'description' => ['short_desc', 'meta_desc'],
                'body' => ['description', 'item_text', 'seo'],
                'category' => ['genre', 'style', 'id_type', 'id_category'],
                'purpose' => ['name', 'shortname'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'galleryItem'],
            'language' => '@app_locale',
        ],

        GalleryHolst::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['name'],
                'description' => ['density', 'hint'],
                'body' => ['hint'],
                'category' => [],
                'purpose' => ['name'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'canvas'],
            'language' => '@app_locale',
        ],

        CanvasRam::class => [
            'enabled' => true,
            'image_fields' => ['img', 'img_bg'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['name'],
                'description' => ['type', 'price'],
                'body' => ['css_params'],
                'category' => ['type'],
                'purpose' => ['name'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'canvas'],
            'language' => '@app_locale',
        ],

        CanvasInterier::class => [
            'enabled' => true,
            'image_fields' => ['inter_img'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['size_1', 'size_2'],
                'description' => ['size_1', 'size_2'],
                'body' => [],
                'category' => [],
                'purpose' => ['size_1', 'size_2'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'canvas'],
            'language' => '@app_locale',
        ],

        CanvasNew::class => [
            'enabled' => true,
            'image_fields' => [],
            'gallery_fields' => [],
            'html_fields' => ['description', 'seo'],
            'media_collections' => [
                'prices_sizes',
                'tab_what_is',
                'tab_examples',
                'photo_work_basic',
                'photo_work_basic_after',
                'photo_work_optimal',
                'photo_work_optimal_after',
                'photo_work_premium',
                'photo_work_premium_after',
                'sizes_bot_items',
                'our_works_new',
            ],
            'context' => [
                'title' => ['title', 'name', 'meta_title'],
                'description' => ['description', 'meta_desc'],
                'body' => ['description', 'seo'],
                'category' => [],
                'purpose' => ['title', 'name'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'canvas'],
            'language' => '@app_locale',
        ],

        CanvasSlider::class => [
            'enabled' => true,
            'image_fields' => ['image', 'image_mob'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['title', 'sub_title', 'size_title'],
                'description' => ['text1', 'text2', 'text3', 'text4', 'size_text'],
                'body' => ['text_gift'],
                'category' => ['sub_canvas'],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'canvasSlider'],
            'language' => '@app_locale',
        ],

        PortraitSlider::class => [
            'enabled' => true,
            'image_fields' => ['image', 'image_mob'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['title', 'sub_title', 'size_title'],
                'description' => ['text1', 'text2', 'text3', 'text4', 'size_text'],
                'body' => ['text_gift'],
                'category' => ['sub_canvas'],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'portrait'],
            'language' => '@app_locale',
        ],

        SliderPhoto::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['title', 'sub_cat_title'],
                'description' => ['text', 'sub_cat_text', 'main_category_text'],
                'body' => ['text'],
                'category' => [],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'gallery'],
            'language' => '@app_locale',
        ],

        SliderMod::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['title', 'sub_cat_title'],
                'description' => ['text', 'sub_cat_text', 'main_category_text'],
                'body' => ['text'],
                'category' => [],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'gallery'],
            'language' => '@app_locale',
        ],

        SliderRepr::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['title', 'sub_cat_title'],
                'description' => ['text', 'sub_cat_text', 'main_category_text'],
                'body' => ['text'],
                'category' => [],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'gallery'],
            'language' => '@app_locale',
        ],

        Page::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => [],
            'html_fields' => ['content', 'body'],
            'context' => [
                'title' => ['title', 'meta_title'],
                'description' => ['meta_description', 'excerpt'],
                'body' => ['content', 'body'],
                'category' => ['template', 'url', 'slug'],
                'purpose' => ['title', 'template'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'page'],
            'language' => '@app_locale',
        ],

        PageFaq::class => [
            'enabled' => true,
            'image_fields' => [],
            'gallery_fields' => [],
            'html_fields' => ['answer'],
            'context' => [
                'title' => ['question'],
                'description' => ['answer'],
                'body' => ['answer'],
                'category' => ['page'],
                'purpose' => ['question'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'pageFaq'],
            'language' => '@app_locale',
        ],

        Address::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => ['images'],
            'html_fields' => ['subtitle', 'working_hours', 'gmail_url_embed'],
            'context' => [
                'title' => ['title', 'city'],
                'description' => ['subtitle', 'type'],
                'body' => ['working_hours', 'gmail_url_embed'],
                'category' => ['type', 'city'],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'contacts'],
            'language' => '@app_locale',
        ],

        AllStylesPage::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => [],
            'html_fields' => ['text'],
            'context' => [
                'title' => ['title'],
                'description' => ['text', 'price'],
                'body' => ['text'],
                'category' => ['is_photo'],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'allStylesPage'],
            'language' => '@app_locale',
        ],

        NewhomeTopSlider::class => [
            'enabled' => true,
            'image_fields' => ['image', 'image_mob'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['title'],
                'description' => ['text'],
                'body' => ['text'],
                'category' => [],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'homeWhenShown'],
            'language' => '@app_locale',
        ],

        NewhomeService::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => [],
            'html_fields' => ['desc'],
            'context' => [
                'title' => ['name', 'title'],
                'description' => ['desc'],
                'body' => ['desc'],
                'category' => [],
                'purpose' => ['name', 'title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'homeWhenShown'],
            'language' => '@app_locale',
        ],

        NewhomeTopWorkEx::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['title'],
                'description' => ['link'],
                'body' => ['title'],
                'category' => [],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'home'],
            'language' => '@app_locale',
        ],

        NewhomeWorkEx::class => [
            'enabled' => true,
            'image_fields' => ['image'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['title'],
                'description' => ['size'],
                'body' => ['title'],
                'category' => ['catid'],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'home'],
            'language' => '@app_locale',
        ],

        OurWorkingProcess::class => [
            'enabled' => true,
            'image_fields' => ['image', 'image_mob'],
            'gallery_fields' => [],
            'html_fields' => ['text'],
            'context' => [
                'title' => ['title'],
                'description' => ['text'],
                'body' => ['text'],
                'category' => [],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'homeWhenShown'],
            'language' => '@app_locale',
        ],

        ACollageAdvantageScreen::class => [
            'enabled' => true,
            'image_fields' => ['image', 'image_mob'],
            'gallery_fields' => [],
            'html_fields' => ['text'],
            'context' => [
                'title' => ['title'],
                'description' => ['text'],
                'body' => ['text'],
                'category' => [],
                'purpose' => ['title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'collage'],
            'language' => '@app_locale',
        ],

        ACollageSlider::class => [
            'enabled' => true,
            'image_fields' => ['image', 'image_mob'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['size_title'],
                'description' => ['size_text'],
                'body' => ['size_text'],
                'category' => [],
                'purpose' => ['size_title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'collage'],
            'language' => '@app_locale',
        ],

        ModGallPage::class => [
            'enabled' => true,
            'image_fields' => ['mod_img'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['mod_title', 'meta_title'],
                'description' => ['mod_subtitle', 'meta_desc'],
                'body' => ['mod_subtitle'],
                'category' => [],
                'purpose' => ['mod_title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'gallery'],
            'language' => '@app_locale',
        ],

        ModPhotoPage::class => [
            'enabled' => true,
            'image_fields' => ['mod_img'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['mod_title', 'meta_title'],
                'description' => ['mod_subtitle', 'meta_desc'],
                'body' => ['mod_subtitle'],
                'category' => [],
                'purpose' => ['mod_title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'gallery'],
            'language' => '@app_locale',
        ],

        ModReprPage::class => [
            'enabled' => true,
            'image_fields' => ['mod_img'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['mod_title', 'meta_title'],
                'description' => ['mod_subtitle', 'meta_desc'],
                'body' => ['mod_subtitle'],
                'category' => [],
                'purpose' => ['mod_title'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'gallery'],
            'language' => '@app_locale',
        ],

        SiteImage::class => [
            'enabled' => true,
            'image_fields' => ['img', 'svg'],
            'gallery_fields' => [],
            'html_fields' => [],
            'apply_map' => [
                'img' => [
                    'alt' => 'img_alt',
                    'title' => 'img_title',
                ],
                'svg' => [
                    'alt' => 'svg_alt',
                    'title' => 'svg_title',
                ],
            ],
            'context' => [
                'title' => ['position_name'],
                'description' => ['page', 'position_name'],
                'body' => [],
                'category' => ['page'],
                'purpose' => ['position_name'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'siteImage'],
            'language' => '@app_locale',
        ],

        OurWork::class => [
            'enabled' => false,
            'image_fields' => ['img', 'avatar'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['name', 'city'],
                'description' => ['text'],
                'body' => ['text'],
                'category' => ['orig_locale'],
                'purpose' => ['name'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'reviewWhenActive'],
            'language' => 'orig_locale',
        ],

        Review::class => [
            'enabled' => false,
            'image_fields' => ['img', 'avatar'],
            'gallery_fields' => [],
            'html_fields' => [],
            'context' => [
                'title' => ['name'],
                'description' => ['text'],
                'body' => ['text'],
                'category' => ['orig_locale', 'pid'],
                'purpose' => ['name'],
            ],
            'url_resolver' => [PublicUrlResolver::class, 'reviewWhenActive'],
            'language' => 'orig_locale',
        ],
    ],
];
