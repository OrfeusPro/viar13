<?php

namespace App\Http\Controllers;

use App\Models\PageFaqDesc;
use DB;
use App;
use App\Models\PageFaq;
use App\Models\AllStyle;
use App\Models\PageSlug;
use App\Models\CanvasNew;
use App\Models\CanvasPhotoImprove;
use App\Models\CanvasRam;
use App\Entity\BasketType;
use App\Models\CountryTel;
use App\Models\ACollageFaq;
use App\Models\ACollageFon;
use App\Models\GalleryItem;
use Illuminate\Support\Arr;
use App\Models\ACollageHead;
use App\Models\AllStyleForm;
use App\Models\CanvasGlobal;
use App\Models\CanvasHeader;
use App\Models\CanvasSlider;
use App\Models\CreepingLine;
use Illuminate\Http\Request;
use App\Models\Locale as Loc;
use App\Models\NewhomeWorkEx;
use App\Models\ACollageSlider;
use App\Models\ACollageStiker;
use App\Models\NewhomeService;
use App\Models\PortraitSlider;
use App\Models\AProductionTime;
use App\Models\NewhomeTopWorkEx;
use App\Models\ACollageWhyScreen;
use App\Models\ACollageOrderScreen;
use App\Models\ACollageStickerGroup;
use App\Models\ACollagePopularScreen;
use App\Models\ACollageGeneratorColor;
use App\Models\ACollageAdvantageScreen;
use App\Models\WhyAreYouLeavingQuestion;
use App\Http\Controllers\IndexController;
use App\Models\HomepageOption as HomeData;
use App\Repositories\GalleryBoxRepository;
use Stevebauman\Location\Facades\Location;
use Illuminate\Support\Facades\Storage;
use App\Repositories\GalleryHolstRepository;
use App\Repositories\GalleryDecorationRepository;

class PortraitPageController extends Controller
{
    public function __construct()
    {
        $this->template = (config('theme.resource') ?: 'theme.viar.') . '.index';
    }

    // public function index(Request $request, GalleryHolstRepository $galleryHolstRepository, GalleryDecorationRepository $galleryDecorationRepository, GalleryBoxRepository $galleryBoxRepository, IndexController $IndexController)
    // {
    //     $item = GalleryItem::withTranslation(App::getLocale(), false)->whereTranslation('slug', $request->slug)->firstOrFail();
    //     $works_ids = \DB::table('gallery_items_newhome_top_work_ex')->where('gallery_item_id', $item->id)->pluck('newhome_top_work_ex_id')->toArray();

    //     $style = $IndexController->get_styles_for_quiz(App::getLocale());

    // //dd(GalleryItem::withTranslation(App::getLocale(), false)->whereTranslation('slug', '!=', $request->slug)->where('id_type', 5)->first()->id)
    //    // $current_quiz_style_id = GalleryItem::withTranslation(App::getLocale(), false)->whereTranslation('slug', '=', $request->slug)->where('id_type', 5)->first()->id;
    //     $current_quiz_style_id = GalleryItem::withTranslation(App::getLocale(), false)->whereTranslation('slug', '=', $request->slug)->where('id_type', 5)->first();
    //     if($current_quiz_style_id)
    //     {
    //         $current_quiz_style_id = $current_quiz_style_id->id;
    //     }

    //     $AProductionTime = AProductionTime::where('category', 'portrait')->first()->translate(App::getLocale(), 'ru');

    //     return view('portrait_new')->with([
    //         'item' =>  $item,
    //         'AProductionTime' =>  $AProductionTime,
    //         'style' => $style,
    //         'home_slides' => PortraitSlider::where('cat_id', $item->id)->where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru'),
    //         'current_quiz_style_id' => $current_quiz_style_id,
    //         'is_portrait_page' => 1,
    //         'is_hide_url_params' => 1,
    //         'canvas_items' => $galleryHolstRepository->getAll(),
    //         'sets' => $galleryBoxRepository->getAll(),
    //         'etc_styles_items' => GalleryItem::withTranslation(App::getLocale(), false)->whereTranslation('slug', '!=', $request->slug)->where('id_type', 5)->get(),
    //         'art_items' => $galleryDecorationRepository->getAll(),
    //         'frames' => CanvasRam::withTranslation(App::getLocale(), false)->get(),
    //         'work_ex' =>  NewhomeWorkEx::withTranslation(App::getLocale(), false)->whereIn('id', $works_ids)->get(),
    //         'home' => HomeData::withTranslation(App::getLocale(), false)->select('all_styles', 'all_sizes')->first()->get()[0],
    //         'bot_form' => AllStyleForm::where('id', 1)->get()->translate(App::getLocale(), 'lv')[0],
    //         'top_form' => AllStyle::withTranslation(App::getLocale(), false)->where('id', 1)->get()[0],
    //         'faqs' => PageFaq::withTranslation(App::getLocale(), false)->get(),
    //     ]);
    // }

    public function hbindex($slug,Request $request, GalleryHolstRepository $galleryHolstRepository, GalleryDecorationRepository $galleryDecorationRepository, GalleryBoxRepository $galleryBoxRepository, IndexController $IndexController)
    {
        if ($slug && $slug=='portrait-caricature') {
            return redirect('/new/caricature', 301);
        }
        $item = GalleryItem::withTranslation(App::getLocale(), false)->whereTranslation('slug', $request->slug)->firstOrFail();
        $works_ids = \DB::table('gallery_items_newhome_top_work_ex')->where('gallery_item_id', $item->id)->pluck('newhome_top_work_ex_id')->toArray();

        $style = $IndexController->get_styles_for_quiz(App::getLocale());

        // dd(GalleryItem::withTranslation(App::getLocale(), false)->whereTranslation('slug', '!=', $request->slug)->where('id_type', 5)->first()->id)
        // $current_quiz_style_id = GalleryItem::withTranslation(App::getLocale(), false)->whereTranslation('slug', '=', $request->slug)->where('id_type', 5)->first()->id;

        $current_quiz_style_id = GalleryItem::withTranslation(App::getLocale(), false)->whereTranslation('slug', '=', $request->slug)->where('id_type', 5)->first();
        if($current_quiz_style_id)
        {
            $current_quiz_style_id = $current_quiz_style_id->id;
        }

        $AProductionTime = AProductionTime::where('category', 'portrait')->first()->translate(App::getLocale(), 'ru');

        $locale = \Illuminate\Support\Facades\App::getLocale();
        $h2_titles = $item->translate($locale);

        $currentUrl = url('/');
        $process = function($item) use ($currentUrl) {
            $audioArr = json_decode($item->a_player, true);
            $download = $audioArr[0]['download_link'] ?? null;
            $item->audiolink = $download
                ? $currentUrl . '/storage/' . $download
                : '';
            return $item;
        };

        $old = DB::table('our_works')->where('active', 1)->where('orig_locale', App::getLocale())->get()->map($process);
        $new = DB::table('reviews')->where('active', 1)->where('pid', $item->id)->where('orig_locale', App::getLocale())->get()->map($process);
        $revs = $old->merge($new)->sortByDesc('created_at')->values();
        $creepingLine = CreepingLine::where('page->portret', 'portret')->where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');
        $portraitSlide = PortraitSlider::where('cat_id', $item->id)
            ->where('is_show', 1)
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'desc')
            ->first() ?: $item->sliderImage;
        $canonicalUrl = url()->current();
        $ogImageUrl = $portraitSlide && !empty($portraitSlide->size_img)
            ? asset('storage/' . ltrim((string) $portraitSlide->size_img, '/'))
            : ($portraitSlide && !empty($portraitSlide->png)
                ? asset('storage/' . ltrim((string) $portraitSlide->png, '/'))
                : url('/img/logo.png'));
        $ogImageUrl = single_image_url('portrait-' . $item->id, seo_image_version($portraitSlide && $portraitSlide->updated_at ? $portraitSlide->updated_at : ($item->updated_at ?? null)));
//        if ($slug === 'kartiny') {
//            $faq_desc = PageFaqDesc::first()->translate(App::getLocale(), 'ru');
//            $content = view((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait_oil')->with([
//                'h2_titles' => $h2_titles,
//                'item' =>  $item,
//                'revs' => $revs,
//                'creepingLine' => $creepingLine,
//                'AProductionTime' =>  $AProductionTime,
//                'style' => $style,
//                'list' => $this->fetchExamples($item),
//                'home_slides' => PortraitSlider::where('cat_id', $item->id)->where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru'),
//                'current_quiz_style_id' => $current_quiz_style_id,
//                'is_portrait_page' => 1,
//                'is_hide_url_params' => 1,
//                'canvas_items' => $galleryHolstRepository->getAll(),
//                'sets' => $galleryBoxRepository->getAll(),
//                'etc_styles_items' => GalleryItem::withTranslation(App::getLocale(), false)->whereTranslation('slug', '!=', $request->slug)->where('id_type', 5)->get(),
//                'art_items' => $galleryDecorationRepository->getAll(),
//                'frames' => CanvasRam::withTranslation(App::getLocale(), false)->get(),
//                'work_ex' =>  NewhomeWorkEx::withTranslation(App::getLocale(), false)->whereIn('id', $works_ids)->get(),
//                'home' => HomeData::withTranslation(App::getLocale(), false)->select('all_styles', 'all_sizes')->first()->get()[0],
//                'bot_form' => AllStyleForm::where('id', 1)->get()->translate(App::getLocale(), 'lv')[0],
//                'top_form' => AllStyle::withTranslation(App::getLocale(), false)->where('id', 1)->get()[0],
//                'faqs' => PageFaq::where('page->portraits_all', 'portraits_all')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),
//                'faq_desc' => $faq_desc,
//            ])->render();
//        } else {
            $content = view((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait')->with([
                'h2_titles' => $h2_titles,
                'item' =>  $item,
                'revs' => $revs,
                'creepingLine' => $creepingLine,
                'AProductionTime' =>  $AProductionTime,
                'style' => $style,
                'home_slides' => PortraitSlider::where('cat_id', $item->id)->where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru'),
                'current_quiz_style_id' => $current_quiz_style_id,
                'is_portrait_page' => 1,
                'is_hide_url_params' => 1,
                'canvas_items' => $galleryHolstRepository->getAll(),
                'sets' => $galleryBoxRepository->getAll(),
                'etc_styles_items' => GalleryItem::withTranslation(App::getLocale(), false)->whereTranslation('slug', '!=', $request->slug)->where('id_type', 5)->get(),
                'art_items' => $galleryDecorationRepository->getAll(),
                'frames' => CanvasRam::withTranslation(App::getLocale(), false)->get(),
                'work_ex' =>  NewhomeWorkEx::withTranslation(App::getLocale(), false)->whereIn('id', $works_ids)->get(),
                'home' => HomeData::withTranslation(App::getLocale(), false)->select('all_styles', 'all_sizes')->first()->get()[0],
                'bot_form' => AllStyleForm::where('id', 1)->get()->translate(App::getLocale(), 'lv')[0],
                'top_form' => AllStyle::withTranslation(App::getLocale(), false)->where('id', 1)->get()[0],
                'faqs' => PageFaq::where('page->portraits_all', 'portraits_all')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),
            ])->render();
//        }

        $modals = view((config('theme.resource') ?: 'theme.viar.') . 'pages.index.modals')
        ->with([
            'style' => $style,
            'current_quiz_style_id' => $current_quiz_style_id,
            'is_portrait_page' => 1,
            'item' => $item,
            ])
            ->render();

        $this->vars = Arr::add($this->vars, 'creepingLine', $creepingLine);
        $this->vars = Arr::add($this->vars, 'title', $item->getTranslatedAttribute('meta_title'));
        $this->vars = Arr::add($this->vars, 'meta_desc', $item->getTranslatedAttribute('meta_desc'));
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);
        $this->vars = Arr::add($this->vars, 'seo', $item->getTranslatedAttribute('seo'));
        $this->vars = Arr::add($this->vars, 'is_cities_on_page', $item->is_cities_on_page);
        $this->vars = Arr::add($this->vars, 'seo_city_title', $item->getTranslatedAttribute('seo_city_title'));
        $this->vars = Arr::add($this->vars, 'seo_city_desc', $item->getTranslatedAttribute('seo_city_desc'));
        $this->vars = Arr::add($this->vars, 'updated_at', $item->updated_at);
        $this->vars = Arr::add($this->vars, 'og_url', $canonicalUrl);
        $this->vars = Arr::add($this->vars, 'og_image', $ogImageUrl);
        $this->vars = Arr::add($this->vars, 'image_src', $ogImageUrl);
        $this->vars = Arr::add($this->vars, 'og_type', 'website');
        return $this->renderOutput();
    }

    // public function canvas(GalleryDecorationRepository $galleryDecorationRepository, GalleryBoxRepository $galleryBoxRepository, IndexController $IndexController)
    // {
    //     $style = $IndexController->get_styles_for_quiz(App::getLocale());
    //     $c_tels = CountryTel::orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');
    //     $AProductionTime = AProductionTime::where('category', 'canvas')->first()->translate(App::getLocale(), 'ru');

    //     return view('canvas_new')->with([
    //         'sets' => $galleryBoxRepository->getAll(),
    //         'AProductionTime' => $AProductionTime,
    //         'item' => CanvasNew::first(),
    //         'home_slides' => CanvasSlider::All()->where('is_show', 1)->translate(App::getLocale(), 'ru'),
    //         'style' => $style,
    //         'c_tels' => $c_tels,
    //         'current_quiz_style_id' => 2,
    //         'art_items' => $galleryDecorationRepository->getAll(),
    //         'int_globs' => CanvasGlobal::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
    //         'canvas_head' => CanvasHeader::first()->get()->translate(App::getLocale(), 'ru')[0],
    //         'etc_styles_items' => GalleryItem::withTranslation(App::getLocale(), false)->where('id_type', 5)->get(),
    //         'top_form' => AllStyle::withTranslation(App::getLocale(), false)->where('id', 1)->get()[0],
    //         'bot_form' => AllStyleForm::where('id', 1)->get()->translate(App::getLocale(), 'lv')[0],
    //     ]);
    // }

    public function hbcanvas(GalleryDecorationRepository $galleryDecorationRepository, GalleryBoxRepository $galleryBoxRepository, IndexController $IndexController,GalleryHolstRepository $galleryHolstRepository)
    {
        $style = $IndexController->get_styles_for_quiz(App::getLocale());
        $c_tels = CountryTel::orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');
        $AProductionTime = AProductionTime::where('category', 'canvas')->first()->translate(App::getLocale(), 'ru');
        $item = CanvasNew::first();
        $canvas_head = CanvasHeader::first()->get()->translate(App::getLocale(), 'ru')[0];
        $canonicalUrl = url()->current();
        $canvasMainImage = $this->resolveCanvasMainImageSource();
        $canvasMainImageUrl = $this->resolveCanvasMainImageUrl($canvasMainImage);
        $canvasMainImageSize = $this->resolveCanvasMainImageSize($canvasMainImage);

        $currentUrl = url('/');
        $process = function($item) use ($currentUrl) {
            $audioArr = json_decode($item->a_player, true);
            $download = $audioArr[0]['download_link'] ?? null;
            $item->audiolink = $download
                ? $currentUrl . '/storage/' . $download
                : '';
            return $item;
        };

        // 1) Записи из старой таблицы
        $old = DB::table('our_works')->where('active', 1)->where('orig_locale', App::getLocale())->get()->map($process);
        $new = DB::table('reviews')->where('active', 1)->where('pid', BasketType::CANVAS_TYPE)->where('orig_locale', App::getLocale())->get()->map($process);
        $revs = $old->merge($new)->sortByDesc('created_at')->values();

        $creepingLine = CreepingLine::where('page->kanvas', 'kanvas')->where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');

        $content = view((config('theme.resource') ?: 'theme.viar.') . 'pages.canvas')->with([
            'sets' => $galleryBoxRepository->getAll(),
            'canvas_items' => $galleryHolstRepository->getAll(),
            'photo_improvements' => CanvasPhotoImprove::where('is_active', 1)->orderBy('sort', 'asc')
                ->get()
                ->translate(App::getLocale(), 'ru'),
            'revs' => $revs,
            'AProductionTime' => $AProductionTime,
            'item' => $item,
            'creepingLine' => $creepingLine,
            'home_slides' => CanvasSlider::where('is_show', 1)->where('sub_canvas', 0)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru'),
            'style' => $style,
            'c_tels' => $c_tels,
            'current_quiz_style_id' => 2,
            'art_items' => $galleryDecorationRepository->getAll(),
            'int_globs' => CanvasGlobal::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'canvas_head' => $canvas_head,
            'etc_styles_items' => GalleryItem::withTranslation(App::getLocale(), false)->where('id_type', 5)->get(),
            'top_form' => AllStyle::withTranslation(App::getLocale(), false)->where('id', 1)->get()[0],
            'bot_form' => AllStyleForm::where('id', 1)->get()->translate(App::getLocale(), 'lv')[0],
            'faqs' => PageFaq::where('page->kanvas', 'kanvas')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),
        ]);

        $structuredData = $this->buildCanvasStructuredData($canvas_head, $canonicalUrl, $canvasMainImageUrl, $canvasMainImageSize);

        $modals = view((config('theme.resource') ?: 'theme.viar.') . 'pages.index.modals')
        ->with([
            'style' => $style,
            'current_quiz_style_id' => 2,
            'item' => $item,
            ])
            ->render();

        $this->vars = Arr::add($this->vars, 'creepingLine', $creepingLine);
        $this->vars = Arr::add($this->vars, 'title', $canvas_head['meta_title']);
        $this->vars = Arr::add($this->vars, 'meta_desc', $canvas_head['meta_desc']);
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);
        $this->vars = Arr::add($this->vars, 'seo', $canvas_head['seo']);
        $this->vars = Arr::add($this->vars, 'is_cities_on_page', $canvas_head->is_cities_on_page);
        $this->vars = Arr::add($this->vars, 'seo_city_title', $canvas_head->seo_city_title);
        $this->vars = Arr::add($this->vars, 'seo_city_desc', $canvas_head->seo_city_desc);
        $this->vars = Arr::add($this->vars, 'updated_at', $canvas_head->updated_at);
        $this->vars = Arr::add($this->vars, 'og_url', $canonicalUrl);
        $this->vars = Arr::add($this->vars, 'og_image', $canvasMainImageUrl);
        $this->vars = Arr::add($this->vars, 'image_src', $canvasMainImageUrl);
        $this->vars = Arr::add($this->vars, 'og_image_width', $canvasMainImageSize['width']);
        $this->vars = Arr::add($this->vars, 'og_image_height', $canvasMainImageSize['height']);
        $this->vars = Arr::add($this->vars, 'og_type', 'website');
        $this->vars = Arr::add($this->vars, 'structured_data', $structuredData);
        return $this->renderOutput();
    }

    private function buildCanvasStructuredData($canvasHead, string $canonicalUrl, string $ogImageUrl, array $ogImageSize = []): array
    {
        $pageTitle = trim(strip_tags((string) ($canvasHead['meta_title'] ?? '')));
        $pageDescription = trim(strip_tags((string) ($canvasHead['meta_desc'] ?? '')));
        $productName = trim(strip_tags((string) ($canvasHead['c_right_top'] ?? '')));

        if ($productName === '') {
            $productName = $pageTitle !== '' ? explode('|', $pageTitle)[0] : trans('canvas.canvas');
            $productName = trim((string) $productName);
        }

        $brandName = trim(strip_tags((string) config('app.name', 'ViarCanvas')));
        if ($brandName === '') {
            $brandName = 'ViarCanvas';
        }

        $offerPrice = $this->resolveCanvasStructuredOfferPrice();
        $aggregateRating = $this->resolveCanvasAggregateRating();

        $structuredData = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $pageTitle,
                'description' => $pageDescription,
                'url' => $canonicalUrl,
                'image' => $ogImageUrl,
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => trans('account.index1'),
                        'item' => url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => trans('canvas.canvas'),
                        'item' => $canonicalUrl,
                    ],
                ],
            ],
            array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'ImageObject',
                '@id' => $canonicalUrl . '#main-image',
                'name' => $productName,
                'caption' => $pageDescription !== '' ? $pageDescription : $productName,
                'contentUrl' => $ogImageUrl,
                'url' => $ogImageUrl,
                'width' => $ogImageSize['width'] ?? null,
                'height' => $ogImageSize['height'] ?? null,
                'representativeOfPage' => true,
            ], static function ($value) {
                return $value !== null;
            }),
            [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $productName,
                'description' => $pageDescription !== '' ? $pageDescription : $productName,
                'image' => [$ogImageUrl],
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $brandName,
                ],
                'offers' => [
                    [
                        '@type' => 'Offer',
                        'url' => $canonicalUrl,
                        'priceCurrency' => 'EUR',
                        'price' => number_format($offerPrice, 2, '.', ''),
                        'availability' => 'https://schema.org/InStock',
                        'itemCondition' => 'https://schema.org/NewCondition',
                    ],
                ],
                'aggregateRating' => $aggregateRating,
            ],
        ];

        return $structuredData;
    }

    private function resolveCanvasMainImageSource(): ?CanvasSlider
    {
        return CanvasSlider::where('is_show', 1)
            ->where('sub_canvas', 0)
            ->orderBy('sort', 'asc')
            ->first();
    }

    private function resolveCanvasMainImageUrl(?CanvasSlider $canvasSlider = null): string
    {
        $version = seo_image_version($canvasSlider && $canvasSlider->updated_at ? $canvasSlider->updated_at : null);

        return single_image_url('canvas', $version);
    }

    private function resolveCanvasMainImageSize(?CanvasSlider $canvasSlider = null): array
    {
        $source = $this->resolveCanvasMainImageFile($canvasSlider);

        if (!empty($source['path']) && is_file($source['path'])) {
            $dimensions = @getimagesize($source['path']);
        } elseif (!empty($source['url'])) {
            $binary = @file_get_contents($source['url']);
            $dimensions = $binary ? @getimagesizefromstring($binary) : null;
        } else {
            $dimensions = null;
        }

        if (is_array($dimensions) && !empty($dimensions[0]) && !empty($dimensions[1])) {
            return [
                'width' => (int) $dimensions[0],
                'height' => (int) $dimensions[1],
            ];
        }

        return [
            'width' => 1356,
            'height' => 848,
        ];
    }

    private function resolveCanvasMainImageFile(?CanvasSlider $canvasSlider = null): array
    {
        if ($canvasSlider && !empty($canvasSlider->size_img)) {
            $relativePath = ltrim((string) $canvasSlider->size_img, '/');

            return [
                'path' => Storage::disk('public')->exists($relativePath) ? Storage::disk('public')->path($relativePath) : null,
                'url' => url('/storage/' . $relativePath),
                'relative' => $relativePath,
            ];
        }

        if ($canvasSlider && !empty($canvasSlider->image)) {
            $sourceUrl = \Voyager::image($canvasSlider->image);
            $path = parse_url((string) $sourceUrl, PHP_URL_PATH);

            return [
                'path' => $path ? public_path(ltrim($path, '/')) : null,
                'url' => (string) $sourceUrl,
                'relative' => ltrim((string) $canvasSlider->image, '/'),
            ];
        }

        return [
            'path' => public_path('img/logo.png'),
            'url' => url('/img/logo.png'),
            'relative' => 'img/logo.png',
        ];
    }

    private function resolveCanvasStructuredOfferPrice(): float
    {
        $row = DB::table('canvas_header')->orderBy('id')->first(['sizes_30x40', 'sizes_38x38', 'sizes_40x30', 'sizes_60x30']);
        if (!$row) {
            return 0.0;
        }

        $rawSizes = implode(',', array_filter([
            (string) ($row->sizes_30x40 ?? ''),
            (string) ($row->sizes_38x38 ?? ''),
            (string) ($row->sizes_40x30 ?? ''),
            (string) ($row->sizes_60x30 ?? ''),
        ]));

        $basePrice = $this->extractMinConfiguredPrice($rawSizes, null);
        if ($basePrice === null || $basePrice <= 0) {
            return 0.0;
        }

        return (float) round($basePrice * $this->resolveCanvasCountryMultiplier(), 2);
    }

    private function resolveCanvasAggregateRating(): array
    {
        $cfg = (array) config('services.google', []);
        $widget = (array) ($cfg['reviews_widget'] ?? []);

        $rating = isset($widget['rating']) && is_numeric($widget['rating'])
            ? (float) $widget['rating']
            : 4.9;
        $reviewCount = isset($widget['total']) && is_numeric($widget['total'])
            ? (int) $widget['total']
            : 202;

        return [
            '@type' => 'AggregateRating',
            'ratingValue' => number_format($rating, 1, '.', ''),
            'reviewCount' => $reviewCount,
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }

    private function resolveCanvasCountryMultiplier(): float
    {
        if (app()->runningInConsole()) {
            return 1.0;
        }

        try {
            $position = Location::get(request()->ip());
        } catch (\Throwable $e) {
            $position = null;
        }

        if (!$position || empty($position->countryCode)) {
            return 1.0;
        }

        $countryCode = strtoupper((string) $position->countryCode);
        if ($countryCode === 'FI') {
            $countryCode = 'FIN';
        }

        $contryMult = DB::table('country_tels')->where('country_code', $countryCode)->pluck('price_country_mltpr')->first();
        if ($contryMult === null || !is_numeric($contryMult)) {
            return 1.0;
        }

        $multiplier = (float) $contryMult;
        return $multiplier > 0 ? $multiplier : 1.0;
    }

    private function extractMinConfiguredPrice(string $raw, ?float $fallback): ?float
    {
        $values = [];

        if ($raw !== '') {
            preg_match_all('/\[(.*?)\]/', $raw, $groups);
            foreach (($groups[1] ?? []) as $group) {
                preg_match_all('/\d+(?:[.,]\d+)?/', (string) $group, $numbers);
                foreach (($numbers[0] ?? []) as $number) {
                    $normalized = str_replace(',', '.', (string) $number);
                    $value = (float) $normalized;
                    if ($value > 0) {
                        $values[] = $value;
                    }
                }
            }
        }

        if (!empty($values)) {
            return (float) min($values);
        }

        if ($fallback !== null && $fallback > 0) {
            return (float) $fallback;
        }

        return null;
    }

    public function ads_canvas(GalleryDecorationRepository $galleryDecorationRepository, GalleryBoxRepository $galleryBoxRepository, IndexController $IndexController,GalleryHolstRepository $galleryHolstRepository)
    {
        $style = $IndexController->get_styles_for_quiz(App::getLocale());
        $c_tels = CountryTel::orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');
        $AProductionTime = AProductionTime::where('category', 'canvas')->first()->translate(App::getLocale(), 'ru');
        $item = CanvasNew::where('id', 1)->get()[0];
        $canvas_head = CanvasHeader::where('id', 2)->get()->translate(App::getLocale(), 'ru')[0];

        $currentUrl = url('/');
        $process = function($item) use ($currentUrl) {
            $audioArr = json_decode($item->a_player, true);
            $download = $audioArr[0]['download_link'] ?? null;
            $item->audiolink = $download
                ? $currentUrl . '/storage/' . $download
                : '';
            return $item;
        };

        // 1) Записи из старой таблицы
        $old = DB::table('our_works')->where('active', 1)->where('orig_locale', App::getLocale())->get()->map($process);
        $new = DB::table('reviews')->where('active', 1)->where('pid', BasketType::CANVAS_TYPE)->where('orig_locale', App::getLocale())->get()->map($process);
        $revs = $old->merge($new)->sortByDesc('created_at')->values();

        $creepingLine = CreepingLine::where('page->kanvas', 'kanvas')->where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');

        $content = view((config('theme.resource') ?: 'theme.viar.') . 'pages.canvas')->with([
            'sets' => $galleryBoxRepository->getAll(),
            'canvas_items' => $galleryHolstRepository->getAll(),
            'photo_improvements' => CanvasPhotoImprove::where('is_active', 1)->orderBy('sort', 'asc')
                ->get()
                ->translate(App::getLocale(), 'ru'),
            'revs' => $revs,
            'AProductionTime' => $AProductionTime,
            'item' => $item,
            'creepingLine' => $creepingLine,
            'home_slides' => CanvasSlider::where('is_show', 1)->where('sub_canvas', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru'),
            'style' => $style,
            'c_tels' => $c_tels,
            'current_quiz_style_id' => 2,
            'art_items' => $galleryDecorationRepository->getAll(),
            'int_globs' => CanvasGlobal::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'canvas_head' => $canvas_head,
            'etc_styles_items' => GalleryItem::withTranslation(App::getLocale(), false)->where('id_type', 5)->get(),
            'top_form' => AllStyle::withTranslation(App::getLocale(), false)->where('id', 1)->get()[0],
            'bot_form' => AllStyleForm::where('id', 1)->get()->translate(App::getLocale(), 'lv')[0],
            'faqs' => PageFaq::where('page->kanvas', 'kanvas')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),
        ]);

        $modals = view((config('theme.resource') ?: 'theme.viar.') . 'pages.index.modals')
        ->with([
            'style' => $style,
            'current_quiz_style_id' => 2,
            'item' => $item,
            ])
            ->render();

        $this->vars = Arr::add($this->vars, 'creepingLine', $creepingLine);
        $this->vars = Arr::add($this->vars, 'title', $canvas_head['meta_title']);
        $this->vars = Arr::add($this->vars, 'meta_desc', $canvas_head['meta_desc']);
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);
        $this->vars = Arr::add($this->vars, 'seo', $canvas_head['seo']);
        $this->vars = Arr::add($this->vars, 'is_cities_on_page', $canvas_head->is_cities_on_page);
        $this->vars = Arr::add($this->vars, 'seo_city_title', $canvas_head->seo_city_title);
        $this->vars = Arr::add($this->vars, 'seo_city_desc', $canvas_head->seo_city_desc);
        $this->vars = Arr::add($this->vars, 'updated_at', $canvas_head->updated_at);
        return $this->renderOutput();
    }


    public function hbcollage(Request $request, GalleryHolstRepository $galleryHolstRepository, GalleryDecorationRepository $galleryDecorationRepository, GalleryBoxRepository $galleryBoxRepository, IndexController $IndexController)
    {
        $style = $IndexController->get_styles_for_quiz(App::getLocale());

        $collageHeadSource = ACollageHead::first();
        $collage_head = ACollageHead::get()->translate(App::getLocale(), 'ru')[0];
        $AProductionTime = AProductionTime::where('category', 'collage')->first()->translate(App::getLocale(), 'ru');
        $collage_head['sizes_30x40'] = $this->collage_price_for_country($collage_head['sizes_30x40']);
        $collage_head['sizes_38x38'] = $this->collage_price_for_country($collage_head['sizes_38x38']);
        $collage_head['sizes_40x30'] = $this->collage_price_for_country($collage_head['sizes_40x30']);

        $item = CanvasNew::first();
        $firstSlideSource = ACollageSlider::where('is_show', 1)->orderBy('sort', 'asc')->first();
        $homeSlides = ACollageSlider::where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');

        $canonicalUrl = url()->current();
        $firstSlide = $homeSlides->first();
        $ogImageUrl = asset(env('THEME') . 'images/collage/main.webp');

        if ($firstSlide) {
            $slideImage = $firstSlide->size_img ?? ($firstSlide[App::getLocale()] ?? $firstSlide['ru'] ?? null);
            if ($slideImage) {
                $ogImageUrl = asset('storage/' . ltrim((string) $slideImage, '/'));
            }
        }
        $collageImageVersion = seo_image_version($firstSlideSource && $firstSlideSource->updated_at
            ? $firstSlideSource->updated_at
            : ($collageHeadSource && $collageHeadSource->updated_at ? $collageHeadSource->updated_at : null));
        $ogImageUrl = single_image_url('collage', $collageImageVersion);

        $structuredData = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => trim(strip_tags((string) ($collage_head['meta_title'] ?? __('collage_new.c_page_title')))),
                'description' => trim(strip_tags((string) ($collage_head['meta_desc'] ?? __('collage_new.c_page_description')))),
                'url' => $canonicalUrl,
                'image' => $ogImageUrl,
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => trans('account.index1'),
                        'item' => url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => trim(strip_tags((string) __('collage_new._breads'))),
                        'item' => $canonicalUrl,
                    ],
                ],
            ],
            seo_product_schema(
                (string) ($collage_head['meta_title'] ?? __('collage_new.c_page_title')),
                (string) ($collage_head['meta_desc'] ?? __('collage_new.c_page_description')),
                $canonicalUrl,
                $ogImageUrl
            ),
        ];

        $currentUrl = url('/');
        $process = function($item) use ($currentUrl) {
            $audioArr = json_decode($item->a_player, true);
            $download = $audioArr[0]['download_link'] ?? null;
            $item->audiolink = $download
                ? $currentUrl . '/storage/' . $download
                : '';
            return $item;
        };

        // 1) Записи из старой таблицы
        $old = DB::table('our_works')->where('active', 1)->where('orig_locale', App::getLocale())->get()->map($process);
        $new = DB::table('reviews')->where('active', 1)->where('pid', BasketType::COLLAGE_TYPE)->where('orig_locale', App::getLocale())->get()->map($process);
        $revs = $old->merge($new)->sortByDesc('created_at')->values();

        $creepingLine = CreepingLine::where('page->collage', 'collage')->where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');

        $content = view((config('theme.resource') ?: 'theme.viar.') . 'pages.collage')->with(
            [
                'locales' => Loc::all(),
                'style' => $style,
                'AProductionTime' => $AProductionTime,
                'current_quiz_style_id' => 2,
                'home_slides' => $homeSlides,
                'item' => $item,
                'revs' => $revs,
                'creepingLine' => $creepingLine,
                'sets' => $galleryBoxRepository->getAll(),
                'ACollageAdvantageScreen' => ACollageAdvantageScreen::withTranslation(App::getLocale(), false)->orderBy('sort', 'asc')->get(),
                'ACollagePopularScreen' => ACollagePopularScreen::withTranslation(App::getLocale(), false)->orderBy('sort', 'asc')->get(),
                'ACollageOrderScreen' => ACollageOrderScreen::inRandomOrder()->limit(1)->first(),
                'ACollageWhyScreen' => ACollageWhyScreen::withTranslation(App::getLocale(), false)->orderBy('sort', 'asc')->get(),
                'ACollageGeneratorColor' => ACollageGeneratorColor::orderBy('sort', 'asc')->get(),
                'ACollageFon' => ACollageFon::orderBy('sort', 'asc')->get(),
                'ACollageStickerGroup' => ACollageStickerGroup::withTranslation(App::getLocale(), false)->orderBy('sort', 'asc')->get(),
                'ACollageStiker' => ACollageStiker::orderBy('sort', 'asc')->get(),
                'collage_head' => $collage_head,
                'faqs' => ACollageFaq::withTranslation(App::getLocale(), false)->orderBy('sort', 'asc')->get(),
                'revs' => DB::table('our_works')->where('active', 1)->where('orig_locale', App::getLocale())->get(),
                'work_ex' => NewhomeWorkEx::withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
                'top_work_ex' => NewhomeTopWorkEx::withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
                'services' => NewhomeService::withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
                'top_form' => AllStyle::withTranslation(App::getLocale(), false)->where('id', 1)->get()[0],
                'bot_form' => AllStyleForm::where('id', 1)->get()->translate(App::getLocale(), 'lv')[0],
                'cur_loc' => App::getLocale(),
            ]
        );

        $modals = view((config('theme.resource') ?: 'theme.viar.') . 'pages.index.modals')
        ->with([
            'style' => $style,
            'current_quiz_style_id' => 2,
            'item' => $item,
            ])
            ->render();

        $this->vars = Arr::add($this->vars, 'creepingLine', $creepingLine);
        $this->vars = Arr::add($this->vars, 'title', __('collage_new.c_page_title'));
        $this->vars = Arr::add($this->vars, 'meta_desc', __('collage_new.c_page_description'));
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);
        $this->vars = Arr::add($this->vars, 'seo', $collage_head['seo']);
        $this->vars = Arr::add($this->vars, 'is_cities_on_page', $collage_head->is_cities_on_page);
        $this->vars = Arr::add($this->vars, 'seo_city_title', $collage_head->seo_city_title);
        $this->vars = Arr::add($this->vars, 'seo_city_desc', $collage_head->seo_city_desc);
        $this->vars = Arr::add($this->vars, 'updated_at', $collage_head->updated_at);
        $this->vars = Arr::add($this->vars, 'og_url', $canonicalUrl);
        $this->vars = Arr::add($this->vars, 'og_image', $ogImageUrl);
        $this->vars = Arr::add($this->vars, 'image_src', $ogImageUrl);
        $this->vars = Arr::add($this->vars, 'og_type', 'website');
        $this->vars = Arr::add($this->vars, 'structured_data', $structuredData);
        return $this->renderOutput();
    }

    protected function fetchExamples(GalleryItem $entity): array
    {
        $list = $entity->getMedia('reason_example');
        $result = [];
        foreach ($list as $item) {
            $result[$item->getCustomProperty('category')] = (object) [
                'id' => $item->id,
                'title' => $item->getCustomProperty('title_'.app()->getLocale()),
                'src' => $item->getUrl(),
            ];
        }

        return [
            'list_category'=>explode("\n", $entity->royal_category),
            'list'=>$result
        ];
        //return $result;
    }

    public function collage_js()
    {

        $collage_head = ACollageHead::first();
        $collage_head['sizes_30x40'] = $this->collage_price_for_country($collage_head['sizes_30x40']);
        $collage_head['sizes_38x38'] = $this->collage_price_for_country($collage_head['sizes_38x38']);
        $collage_head['sizes_40x30'] = $this->collage_price_for_country($collage_head['sizes_40x30']);

        return view((config('theme.resource') ?: 'theme.viar.') . 'pages.collage.js')->with(
            [
                'collage_head' => $collage_head,
            ]
        )->render();
    }

    public function collage_price_for_country($srt)
    {
        if (isset($_SERVER['REQUEST_URI']) && !str_contains($_SERVER['REQUEST_URI'], '/admin/')){
            if ($position = Location::get(request()->ip())) {
                $country_code = $position->countryCode;
                if ($country_code == 'FI') {
                    $country_code = 'FIN';
                }

                $contry_mult = \DB::table('country_tels')->where('country_code', $country_code)->pluck('price_country_mltpr')->first();
                if ($contry_mult == null) {
                    $contry_mult = 1;
                }
            } else {
                $contry_mult = 1;
            }
        }

        $custom_sizes = explode(',', $srt);
        $custom_sizes_srt = array();
        $i = 0;
        if(is_array($custom_sizes) && !empty($custom_sizes))
        {
            foreach ( collect($custom_sizes)->chunk(4) as $chunk_items)
            {
                foreach($chunk_items as $size_item)
                {
                    $i++;
                    $size_clear_vals = explode('x', substr($size_item, 0, strpos($size_item, '[')));
                    $prices_vals = explode('-', get_string_between($size_item, '[', ']'));

                    $sale_price = null;

                    $check_price = $prices_vals[0];
                    if(isset($prices_vals[1])){
                        $sale_price = $prices_vals[1];
                        $check_price = $sale_price;
                        $prices_vals[1] = $prices_vals[1]*$contry_mult;
                    }

                    $check_price = $check_price*$contry_mult;
                    $prices_vals[0] = $prices_vals[0]*$contry_mult;

                    $custom_sizes_srt[$i] = implode('x', $size_clear_vals).'['.implode('-', $prices_vals).']';
                }
            }
        }
        return implode(',', $custom_sizes_srt);
    }

}
