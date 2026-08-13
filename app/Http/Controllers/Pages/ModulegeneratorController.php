<?php

namespace App\Http\Controllers\Pages;

use DB;
use App;
use App\Models\Stock;
use App\Entity\BasketType;
use App\Models\GalleryItem;
use App\Models\GalleryType;
use Illuminate\Support\Arr;
use App\Models\NewhomeService;
use Illuminate\Support\Carbon;
use App\Models\AProductionTime;
use App\Models\GalleryCategory;
use App\Models\ModularPicsHead;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ModularPicsWhatSizePrice;
use App\Repositories\GalleryBoxRepository;
use App\Repositories\GalleryDecorationRepository;

class ModulegeneratorController extends Controller
{
    public function __construct() {
        $this->template = (config('theme.resource') ?: 'theme.viar.') . '.index';
    }

    public function index()
    {

        $type = 'module';
        $category = false;
        $random = true;

        $items = GalleryItem::getItems(2, $category, [], false, $random);


        $modHeadSource = ModularPicsHead::where('id', 1)->first();
        $mod_head = ModularPicsHead::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0];
        $canonicalUrl = url()->current();
        $ogImageUrl = single_image_url('module-generator', seo_image_version($modHeadSource && $modHeadSource->updated_at ? $modHeadSource->updated_at : null));
        $structuredData = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => trim(strip_tags((string) ($mod_head['meta_title'] ?? __('pages.modular-generator.slider.page-title')))),
                'description' => trim(strip_tags((string) ($mod_head['meta_desc'] ?? ''))),
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
                        'name' => trim(strip_tags((string) __('pages.modular-generator.bread_title'))),
                        'item' => $canonicalUrl,
                    ],
                ],
            ],
            seo_product_schema(
                (string) ($mod_head['meta_title'] ?? __('pages.modular-generator.slider.page-title')),
                (string) ($mod_head['meta_desc'] ?? ''),
                $canonicalUrl,
                $ogImageUrl
            ),
        ];

        $modals = view((config('theme.resource') ?: 'theme.viar.') . 'modals.popup_add_to_cart').
        view((config('theme.resource') ?: 'theme.viar.') . 'modals.login_reg_res');

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
        $new = DB::table('reviews')->where('active', 1)->where('pid', BasketType::MODULAR_PICTURES_TYPE)->where('orig_locale', App::getLocale())->get()->map($process);
        $revs = $old->merge($new)->sortByDesc('created_at')->values();

        $content = view((config('theme.resource') ?: 'theme.viar.') . 'pages.modular-generator.index')->with([
            'art_items' => GalleryDecorationRepository::getAll(),
            'sets' => GalleryBoxRepository::getAll(),
            'popular' => $items,
            'revs' => $revs,
            'AProductionTime' => AProductionTime::where('category', 'canvas')->first()->translate(App::getLocale(), 'ru'),
            'mod_head' => $mod_head,
            'our_works' => ModularPicsHead::where('id', 1)->pluck('our_works')->first(),
            'what_size' => ModularPicsWhatSizePrice::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'services' => NewhomeService::where('is_show', 1)->withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
        ]);


// dd($mod_head['meta_title']);
        // $pop_mod_ids = DB::table('gallery_items_mod_head')->pluck('gallery_item_id')->toArray();

        // return view('modular-pictures', [
        //     'usl_pages' => 1,
        //     'translated_slugs' => $translated_slugs,
        //     'galleryBoxes' => $this->galleryBoxRepository->getAll(),
        //     'galleryHolsts' => $this->galleryHolstRepository->getAll(),
        //     'def_inter_images' => CanvasInterier::all(),
        //     'int_globs' => CanvasGlobal::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
        //     'galleryDecorations' => $this->galleryDecorationRepository->getAll(),
        //     'tab' => CanvasHeader::first()->get()->translate(App::getLocale(), 'ru')[0],
        //     'canvas_del_time' => CanvasDelTime::first()->get()->translate(App::getLocale(), 'ru')[0],
        //     'canvas_req' => CanvasReq::first()->get()->translate(App::getLocale(), 'ru')[0],
        //     'serv_qual' => CanvasWorkServ::first()->get()->translate(App::getLocale(), 'ru')[0],
        //     'def_inter_rams' => CanvasRam::all()->translate(App::getLocale(), 'ru'),
        //     'pop_mod_items' => GalleryItem::whereIn('id', $pop_mod_ids)->get()->translate(App::getLocale(), 'ru'),
        //     'canvas_work_serv' => CanvasWorkServ::first()->get()->translate(App::getLocale(), 'ru')[0],
        //     'del_time' => CanvasDelTime::first()->get()->translate(App::getLocale(), 'ru')[0],
        //     'canvas_what' => CanvasWhat::first()->get()->translate(App::getLocale(), 'ru')[0],
        //     'reviews' =>  DB::table('our_works')->where('active', 1)->where('orig_locale', App::getLocale())->get(),
        //     'canv_bot' => CanvasCompStepsArtsPack::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
        // ]);



        $this->vars = Arr::add($this->vars, 'title', $mod_head['meta_title']);
        $this->vars = Arr::add($this->vars, 'meta_desc', $mod_head['meta_desc']);
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);
        $this->vars = Arr::add($this->vars, 'is_cities_on_page', $mod_head->is_cities_on_page);
        $this->vars = Arr::add($this->vars, 'seo_city_title', $mod_head->seo_city_title);
        $this->vars = Arr::add($this->vars, 'seo_city_desc', $mod_head->seo_city_desc);
        $this->vars = Arr::add($this->vars, 'updated_at', $mod_head->updated_at);
        $this->vars = Arr::add($this->vars, 'og_url', $canonicalUrl);
        $this->vars = Arr::add($this->vars, 'og_image', $ogImageUrl);
        $this->vars = Arr::add($this->vars, 'image_src', $ogImageUrl);
        $this->vars = Arr::add($this->vars, 'og_type', 'website');
        $this->vars = Arr::add($this->vars, 'structured_data', $structuredData);

        return $this->renderOutput();

    }

}
