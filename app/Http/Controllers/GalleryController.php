<?php

namespace App\Http\Controllers;

use App\Repositories\BasketRepository;
use DB;
use App;
use Throwable;
use Carbon\Carbon;
use App\Models\PageFaq;
use App\Models\CanvasRam;
use App\Models\GallGlobs;
use App\Models\SliderMod;
use App\Models\SliderRepr;
use App\Models\AGalleryAge;
use App\Models\GalleryItem;
use App\Models\GalleryPage;
use App\Models\GallerySize;
use App\Models\GalleryType;
use App\Models\ModGallPage;
use App\Models\ModReprPage;
use App\Models\SliderPhoto;
use Illuminate\Support\Arr;
use App\Models\CanvasGlobal;
use App\Models\CanvasHeader;
use App\Models\CreepingLine;
use App\Models\ModPhotoPage;
use Illuminate\Http\Request;
use App\Models\AGalleryGenre;
use App\Models\AGalleryStyle;
use App\Models\CanvasInterier;
use App\Models\AProductionTime;
use App\Models\CanvasRamsColor;
use App\Models\GalleryCategory;
use App\Models\GalleryTagColor;
use App\Models\CanvasRamsMaterial;
use App\Models\GalleryFiltersPage;
use Illuminate\Support\Collection;
use App\Models\AGalleryNationality;
use TCG\Voyager\Models\Translation;
use App\Models\CanvasCompStepsArtsPack;
use App\Http\Controllers\IndexController;
use App\Models\GalleryItemsAGalleryGenre;
use App\Models\GalleryItemsAGalleryStyle;
use App\Repositories\GalleryBoxRepository;
use Stevebauman\Location\Facades\Location;
use App\Models\GalleryItemsGalleryTagSizes;
use App\Repositories\GallerySizeRepository;
use App\Repositories\GalleryHolstRepository;

class GalleryController extends Controller
{
    private $galleryBoxRepository;

    private $gallerySizeRepository;

    private $galleryHolstRepository;
    private $basketRepository;

    public function __construct(GalleryBoxRepository $galleryBoxRepository, GallerySizeRepository $gallerySizeRepository, GalleryHolstRepository $galleryHolstRepository,
    BasketRepository $basketRepository
    ) {
        $this->galleryBoxRepository = $galleryBoxRepository;
        $this->gallerySizeRepository = $gallerySizeRepository;
        $this->galleryHolstRepository = $galleryHolstRepository;
        $this->basketRepository = $basketRepository;
        $this->template = env('THEME_RESOURCES') . '.index';
    }



    public function hb_render()
    {
        $gallery = GalleryPage::first()->get()->translate(App::getLocale(), 'ru')[0];
        $SliderPhoto = SliderPhoto::all()->translate(App::getLocale(), 'ru');
        $SliderMod = SliderMod::all()->translate(App::getLocale(), 'ru');
        $SliderRepr = SliderRepr::all()->translate(App::getLocale(), 'ru');

        // sales
        $module_sale = GalleryItem::where('id_type', 2)->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 0)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $foto_sale = GalleryItem::where('id_type', 3)->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 0)->where('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $repr_sale = GalleryItem::where('id_type', 4)->where('custom_size_prices_sale', '!=',null)->where('is_big_sale', 0)->whereDate('sale_end','>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $bestseller = GalleryItem::where('is_bestseller', 1)->where('active', 1)->inRandomOrder()->limit(6)->get()->translate(App::getLocale(), 'ru');


        $all = collect();
        $top_slides = $all->merge($SliderPhoto)->merge($SliderMod)->merge($SliderRepr);

        $gc = new GalleryCategory();

        $categories_photo = $gc->getAll("photo");
        $categories_module = $gc->getAll("module");
        $categories_reproduction = $gc->getAll("reproduction");

        $creepingLine = CreepingLine::where('page->gallery', 'gallery')->where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');

        $localized = function ($item, string $key, $fallback = '') {
            $value = data_get($item, $key);
            if (is_string($value)) {
                $value = trim($value);
            }
            return $value !== null && $value !== '' ? $value : $fallback;
        };

        $breadcrumbItems = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => trans('account.index1'),
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => trans('breadcrumbs.gallery'),
                'item' => url()->current(),
            ],
        ];

        $structuredData = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => trim(strip_tags((string) $localized($gallery, 'meta_title', $localized($gallery, 'name', '')))),
                'description' => trim(strip_tags((string) $localized($gallery, 'meta_description', $localized($gallery, 'name', '')))),
                'url' => url()->current(),
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $breadcrumbItems,
            ],
        ];

        $content = view(env('THEME_RESOURCES') . 'pages.gallery.gallery')
        ->with('top_slides', $top_slides)
        ->with('creepingLine', $creepingLine)
        ->with('bestseller', $bestseller)
        ->with('module_sale', $module_sale)
        ->with('foto_sale', $foto_sale)
        ->with('repr_sale', $repr_sale)
        ->with('categories_photo', $categories_photo)
        ->with('categories_module', $categories_module)
        ->with('categories_reproduction', $categories_reproduction)
        ->with('faqs', PageFaq::where('page->gallery_main', 'gallery_main')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get())
        ->with('gallery', $gallery);

        $this->vars = Arr::add($this->vars, 'creepingLine', $creepingLine);
        $this->vars = Arr::add($this->vars, 'title', $gallery->meta_title);
        $this->vars = Arr::add($this->vars, 'meta_desc', $gallery->meta_description);
        $this->vars = Arr::add($this->vars, 'og_url', url()->current());
        $this->vars = Arr::add($this->vars, 'structured_data', $structuredData);
        $this->vars = Arr::add($this->vars, 'content', $content);
        // $this->vars = Arr::add($this->vars, 'modals', $modals);
        // $this->vars = Arr::add($this->vars, 'seo', $gallery->getTranslatedAttribute('seo'));
        return $this->renderOutput();
    }



    /* set gallery_sizes from custom_size_prices
        и так же в каждом товаре `gallery_items_ gallery_tag_sizes`
    */
    public function hb_set_sizes(Request $request, $type, $category = false)
    {
        $dis = GalleryItem::where('id_type', 2)
        ->oRwhere('id_type', 3)
        ->oRwhere('id_type', 4)
        ->with('sizes')
        ->get();

        $all_array_sizes = [];

        foreach($dis as $item)
        {

            $sizes = explode(",",$item->custom_size_prices);

            $array_sizes = [];
            foreach($sizes as $size)
            {
                $price = $this->get_string_between($size, '[', ']');
                $size_clear = substr($size, 0, strpos($size, '['));
                $size_clear_vals = explode('x', $size_clear);

                if(isset($size_clear_vals[1]))
                {
                    $size_full_name = $size_clear_vals[0] . 'x' . $size_clear_vals[1];
                    $size_full_name = str_replace(" ","", $size_full_name);
                    $array_sizes[] = $size_full_name;
                    $all_array_sizes[] = $size_full_name;

                    $is_item_size = $item->sizes()->where("size", $size_full_name)->first();

                    if(!$is_item_size)
                    {
                        $find_size = GallerySize::where("size", $size_full_name)->first();
                        if(!$find_size)
                        {
                            $sitem = new GallerySize;
                            $sitem->size = $size_full_name;
                            $sitem->price = 0;
                            $size_clear_vals = explode('x', $size_full_name);
                            $sitem->height = (int)$size_clear_vals[0];
                            $sitem->length = (int)$size_clear_vals[1];
                            $sitem->save();
                        }
                        else
                        {
                            $size_clear_vals = explode('x', $size_full_name);
                            $find_size->height = (int)$size_clear_vals[0];
                            $find_size->length = (int)$size_clear_vals[1];
                            $find_size->save();
                        }

                        $find_size = GallerySize::where("size", $size_full_name)->first();

                        $s = new GalleryItemsGalleryTagSizes;
                        $s->gallery_item_id = $item->id;
                        $s->gallery_size_id = $find_size->id;
                        $s->save();
                    }
                }
            }

        }

    }


    public function hb_set_genre(Request $request)
    {
        $dis = GalleryItem::where('id_type', 2)
        ->oRwhere('id_type', 3)
        ->oRwhere('id_type', 4)
        ->with('get_genre')
        ->get();

        foreach($dis as $item)
        {
            if($item->getTranslatedAttribute('genre'))
            {
                $genre = $item->getTranslatedAttribute('genre');
                $genre = str_replace("Портрет1","Портрет",$genre);
                $genre = str_replace("Бытовой жанр","Бытовой",$genre);
                $find_size = AGalleryGenre::where("name", $genre)->first();

                if($find_size)
                {
                    $search = GalleryItemsAGalleryGenre::where("gallery_item_id", $item->id)->where("a_gallery_genre_id", $find_size->id)->first();
                    if(!$search)
                    {
                        $s = new GalleryItemsAGalleryGenre;
                        $s->gallery_item_id = $item->id;
                        $s->a_gallery_genre_id = $find_size->id;
                        $s->save();
                    }
                }
                else
                {
                    if($item->getTranslatedAttribute('genre') != "Жанр"
                    && $item->getTranslatedAttribute('genre') != "Жанр-3"
                    )
                    dd($item->id,$item->getTranslatedAttribute('genre'));
                }
            }

        }
    }

    public function hb_set_style(Request $request)
    {
        $dis = GalleryItem::where('id_type', 2)
        ->oRwhere('id_type', 3)
        ->oRwhere('id_type', 4)
        ->with('get_style')
        ->get();

        foreach($dis as $item)
        {
            if($item->getTranslatedAttribute('style'))
            {
                $style = $item->getTranslatedAttribute('style');
                $style = str_replace("Kanvas","Канвас",$style);
                $style = str_replace("Романтизм, символизм","символизм",$style);
                $style = str_replace("Акадеизм","Академизм",$style);
                $style = str_replace("Бароко","Барокко",$style);
                $style = str_replace("Реализм1","Реализм",$style);

                $find_size = AGalleryStyle::where("name", $style)->first();

                if($find_size)
                {
                    $search = GalleryItemsAGalleryStyle::where("gallery_item_id", $item->id)->where("a_gallery_style_id", $find_size->id)->first();
                    if(!$search)
                    {
                        $s = new GalleryItemsAGalleryStyle;
                        $s->gallery_item_id = $item->id;
                        $s->a_gallery_style_id = $find_size->id;
                        $s->save();
                    }
                }
                else
                {
                    if($item->getTranslatedAttribute('style') != "Стиль"
                    && $item->getTranslatedAttribute('style') != "Стиль-4"
                    )
                    dd($item->id,$item->getTranslatedAttribute('style'));
                }
            }

        }
    }


    function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    public function hb_type_render(Request $request, $type, $category = false)
    {
        $page = $request->get('page');
        $tag = $request->get('tag');
        $color = $request->get('color');
        $size = $request->get('size');
        $order = $request->get('order');
        $search = $request->get('search');
        $shape = $request->get('shape');

        $params = $request->query();
        unset($params["_url"]);
        //dd($params);
        $filter = [
            'page' => $page,
            'tag' => $tag,
            'color' => $color,
            'size' => $size,
            'order' => $order,
            'search' => $search,
            'shape' => $shape,
        ];

        $gallery = new GalleryType();
        $gc = new GalleryCategory();

        $gallery = $gallery->getType($type);

        if($category)
        {
            $category = $gc->getBy($category);

            try {
                $categories = $category->getAll($type);
            } catch (Throwable $th) {
                return abort(404);
            }

            $items = $gc->getItems($gallery->id, $category->id, $filter);
            $items->appends($params);

            $tag_ids = explode(',', $category->id_tag_room);
            $tags = $gc->getTags($tag_ids);
            $tag_color_ids = explode(',', $category->id_tag_room);
            $color_tags = $gc->getColors($tag_color_ids);
        }
        else
        {
            $items = $gc->getItems($gallery->id, $category, $filter);
            $items->appends($params);

            $categories = $gc->getAll($type);

            // dd($categories);
            $tags = false;
            $tag_color_ids = false;
            $color_tags = $gc->getColors("");
        }


        $gallerypage = GalleryPage::first()->get()->translate(App::getLocale(), 'ru')[0];
        $categories_photo = $gc->getAll("photo");
        $categories_module = $gc->getAll("module");
        $categories_reproduction = $gc->getAll("reproduction");
        $bestseller = GalleryItem::where('is_bestseller', 1)->where('active', 1)->inRandomOrder()->limit(6)->get()->translate(App::getLocale(), 'ru');

        $canonicalUrl = url()->current();
        $breadcrumbItems = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => trans('account.index1'),
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => trans('breadcrumbs.gallery'),
                'item' => route('hb.gallery.index'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => trim((string) translated_value($gallery, 'name', '')),
                'item' => route('hb.gallery.module', $gallery->url),
            ],
        ];

        if ($category) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => 4,
                'name' => trim((string) data_get($category, 'name')),
                'item' => $canonicalUrl,
            ];
        }

        $pageSchemaName = $category
            ? trim((string) translated_value($category, 'meta_title', translated_value($category, 'name', '')))
            : trim((string) translated_value($gallery, 'meta_title', translated_value($gallery, 'name', '')));
        $pageSchemaDescription = $category
            ? trim((string) translated_value($category, 'meta_description', translated_value($category, 'name', '')))
            : trim((string) translated_value($gallery, 'meta_description', translated_value($gallery, 'name', '')));

        $structuredData = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => trim(strip_tags((string) $pageSchemaName)),
                'description' => trim(strip_tags((string) $pageSchemaDescription)),
                'url' => $canonicalUrl,
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $breadcrumbItems,
            ],
        ];


        if (count((array) $gallery) > 0 || count((array) $categories) > 0) {
            $top_slides = null;
            $pop_arts = null;
            $globs = null;
            $rec_arts = null;

            $all_globs = GallGlobs::first()->get()->translate(App::getLocale(), 'ru')[0];
            $all_arts = GalleryItem::inRandomOrder()->limit(10)->get()->translate(App::getLocale(), 'ru')->toArray();
            $glob = GalleryFiltersPage::first()->get()->translate(App::getLocale(), 'ru')[0];

            // sales
            $module_sale = GalleryItem::where('id_type', 2)->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 0)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
            $foto_sale = GalleryItem::where('id_type', 3)->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 0)->where('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
            $repr_sale = GalleryItem::where('id_type', 4)->where('custom_size_prices_sale', '!=',null)->where('is_big_sale', 0)->whereDate('sale_end','>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');

            // big salse
            $module_big_sale = GalleryItem::where('id_type', 2)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->whereDate('sale_end', '>=',Carbon::now())->get()->translate(App::getLocale(), 'ru');
            $foto_big_sale = GalleryItem::where('id_type', 3)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->where('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
            $repr_big_sale = GalleryItem::where('id_type', 4)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');

            $creepingLine = CreepingLine::where('page->gallery', 'gallery')->where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');

            if ($type == 'module') {
                $top_slides = SliderMod::all()->translate(App::getLocale(), 'ru');
                $globs = ModGallPage::first()->get()->translate(App::getLocale(), 'ru')[0];
                $pop_arts = GalleryItem::where('id_type', 2)->inRandomOrder()->limit(10)->get()
                    ->translate(App::getLocale(), 'ru')->toArray();
                $rec_arts_ids = DB::table('gallery_items_mod_gall_page')->pluck('gallery_item_id')->toArray();
                $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');



                $content = view(env('THEME_RESOURCES') . 'pages.gallery.module', [
                    'page' => $gallery,
                    'gallery' => $gallerypage,
                    'creepingLine' => $creepingLine,
                    'bestseller' => $bestseller,
                    'categories_photo' => $categories_photo,
                    'categories_module' => $categories_module,
                    'categories_reproduction' => $categories_reproduction,
                    'type' => $type,
                    'top_slides' => $top_slides,
                    'categories' => $categories,
                    'category' => $category,
                    'pop_arts' => $pop_arts,
                    'all_arts' => $all_arts,
                    'globs' => $globs,
                    'glob' => $glob,
                    'all_globs' => $all_globs,
                    'rec_arts' => $rec_arts,
                    'tags' => $tags,
                    'colors' => $color_tags,
                    'items' => $items,
                    'module_sale' => $module_sale,
                    'foto_sale' => $foto_sale,
                    'repr_sale' => $repr_sale,
                    'module_big_sale' => $module_big_sale,
                    'foto_big_sale' => $foto_big_sale,
                    'repr_big_sale' => $repr_big_sale,
                    'faqs' => PageFaq::where('page->gallery_subcategories', 'gallery_subcategories')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),
                ]);
            }

            if ($type == 'photo') {
                $top_slides = SliderPhoto::all()->translate(App::getLocale(), 'ru');
                $globs = ModPhotoPage::first()->get()->translate(App::getLocale(), 'ru')[0];
                $pop_arts = GalleryItem::where('id_type', 3)->inRandomOrder()->limit(10)->get()
                    ->translate(App::getLocale(), 'ru')->toArray();
                $rec_arts_ids = DB::table('gallery_items_mod_photo_page')->pluck('gallery_item_id')->toArray();
                $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');

                $content = view(env('THEME_RESOURCES') . 'pages.gallery.photo', [
                    'page' => $gallery,
                    'gallery' => $gallerypage,
                    'creepingLine' => $creepingLine,
                    'bestseller' => $bestseller,
                    'categories_photo' => $categories_photo,
                    'categories_module' => $categories_module,
                    'categories_reproduction' => $categories_reproduction,
                    'type' => $type,
                    'top_slides' => $top_slides,
                    'categories' => $categories,
                    'category' => $category,
                    'pop_arts' => $pop_arts,
                    'all_arts' => $all_arts,
                    'globs' => $globs,
                    'glob' => $glob,
                    'all_globs' => $all_globs,
                    'rec_arts' => $rec_arts,
                    'tags' => $tags,
                    'colors' => $color_tags,
                    'items' => $items,
                    'module_sale' => $module_sale,
                    'foto_sale' => $foto_sale,
                    'repr_sale' => $repr_sale,
                    'module_big_sale' => $module_big_sale,
                    'foto_big_sale' => $foto_big_sale,
                    'repr_big_sale' => $repr_big_sale,
                    'faqs' => PageFaq::where('page->gallery_subcategories', 'gallery_subcategories')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),
                ]);
            }

            if ($type == 'reproduction') {
                $top_slides = SliderRepr::all()->translate(App::getLocale(), 'ru');
                $globs = ModReprPage::first()->get()->translate(App::getLocale(), 'ru')[0];
                $pop_arts = GalleryItem::where('id_type', 4)->inRandomOrder()->limit(10)->get()
                    ->translate(App::getLocale(), 'ru')->toArray();
                $rec_arts_ids = DB::table('gallery_items_mod_repr_page')->pluck('gallery_item_id')->toArray();
                $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');

                $painters = GalleryCategory::where("is_painter", 1)->inRandomOrder()->limit(19)->get()->translate(App::getLocale(), 'ru');
                $gallery_age = AGalleryAge::orderBy('sort')->limit(19)->get()->translate(App::getLocale(), 'ru');
                $gallery_genre = AGalleryGenre::orderBy('sort')->limit(19)->limit(19)->get()->translate(App::getLocale(), 'ru');
                $gallery_style = AGalleryStyle::orderBy('sort')->limit(19)->limit(19)->get()->translate(App::getLocale(), 'ru');
                $gallery_nationality = AGalleryNationality::orderBy('sort')->limit(19)->get()->translate(App::getLocale(), 'ru');

                $rep_last_items = GalleryItem::rep_last_items();

                $content = view(env('THEME_RESOURCES') . 'pages.gallery.reproduction', [
                    'rep_last_items' => $rep_last_items,
                    'bestseller' => $bestseller,
                    'painters' => $painters,
                    'gallery_age' => $gallery_age,
                    'gallery_genre' => $gallery_genre,
                    'gallery_style' => $gallery_style,
                    'gallery_nationality' => $gallery_nationality,
                    'page' => $gallery,
                    'gallery' => $gallerypage,
                    'creepingLine' => $creepingLine,
                    'categories_photo' => $categories_photo,
                    'categories_module' => $categories_module,
                    'categories_reproduction' => $categories_reproduction,
                    'type' => $type,
                    'top_slides' => $top_slides,
                    'categories' => $categories,
                    'category' => $category,
                    'pop_arts' => $pop_arts,
                    'all_arts' => $all_arts,
                    'globs' => $globs,
                    'glob' => $glob,
                    'all_globs' => $all_globs,
                    'rec_arts' => $rec_arts,
                    'tags' => $tags,
                    'colors' => $color_tags,
                    'items' => $items,
                    'module_sale' => $module_sale,
                    'foto_sale' => $foto_sale,
                    'repr_sale' => $repr_sale,
                    'module_big_sale' => $module_big_sale,
                    'foto_big_sale' => $foto_big_sale,
                    'repr_big_sale' => $repr_big_sale,
                    'faqs' => PageFaq::where('page->gallery_subcategories', 'gallery_subcategories')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),
                ]);
            }

            if(!$globs){
                $globs = ModReprPage::first()->get()->translate(App::getLocale(), 'ru')[0];
            }

            $this->vars = Arr::add($this->vars, 'creepingLine', $creepingLine);
            $this->vars = Arr::add($this->vars, 'content', $content);
            if($category && $type == 'reproduction') {
                $this->vars = Arr::add($this->vars, 'title', translated_value($category, 'meta_title'));
                $this->vars = Arr::add($this->vars, 'meta_desc', translated_value($category, 'meta_description'));
            } else {
                if($category && $type)
                {
                    $this->vars = Arr::add($this->vars, 'title', $globs['meta_title'].' | '.translated_value($category, 'name'));
                    $this->vars = Arr::add($this->vars, 'meta_desc', $globs['meta_desc']);
                }
                else{
                    $this->vars = Arr::add($this->vars, 'title', $globs['meta_title']);
                    $this->vars = Arr::add($this->vars, 'meta_desc', $globs['meta_desc']);
                }
            }

            $currentTitle = trim(strip_tags((string) data_get($this->vars, 'title', '')));
            $currentMetaDesc = trim(strip_tags((string) data_get($this->vars, 'meta_desc', $currentTitle)));
            if ($currentTitle !== '') {
                $pageSchemaName = $currentTitle;
                $pageSchemaDescription = $currentMetaDesc !== '' ? $currentMetaDesc : $currentTitle;
                $structuredData[0]['name'] = $pageSchemaName;
                $structuredData[0]['description'] = $pageSchemaDescription;
                if ($category) {
                    $structuredData[1]['itemListElement'][3]['name'] = trim((string) translated_value($category, 'name', data_get($category, 'name', '')));
                }
                $this->vars = Arr::add($this->vars, 'page_schema_name', $pageSchemaName);
                $this->vars = Arr::add($this->vars, 'page_schema_description', $pageSchemaDescription);
            }
            $this->vars = Arr::add($this->vars, 'og_url', $canonicalUrl);
            $this->vars = Arr::add($this->vars, 'structured_data', $structuredData);
            return $this->renderOutput();
        } else {
            return abort(404);
        }
    }



    public function modern_handmade_paintings(Request $request, $type, $category = false)
    {
        $type = 'reproduction';
        $gallery = new GalleryType();
        $gallery = $gallery->getType($type);

        $content = view(env('THEME_RESOURCES') . 'pages.gallery.modern_handmade_paintings', [
            'page' => $gallery,
            'linkroute' => route('hb.gallery.modern_painters'),
            'linktext' => __('gallery.rep_f1'),
        ]);

        $this->vars = Arr::add($this->vars, 'title', __("gallery.rep_f1"));
        $this->vars = Arr::add($this->vars, 'meta_desc', __('gallery.rep_f1'));
        $this->vars = Arr::add($this->vars, 'content', $content);
        return $this->renderOutput();
    }

    public function modern_painters(Request $request)
    {
        $items = GalleryCategory::where('painter_is_modern', 1)->where("is_painter", 1)->orderBy('name')->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery = new GalleryType();
        $type = 'reproduction';
        $gallery = $gallery->getType($type);
        $painters = GalleryCategory::where("is_painter", 1)->inRandomOrder()->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_age = AGalleryAge::orderBy('sort')->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_genre = AGalleryGenre::orderBy('sort')->limit(19)->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_style = AGalleryStyle::orderBy('sort')->limit(19)->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_nationality = AGalleryNationality::orderBy('sort')->limit(19)->get()->translate(App::getLocale(), 'ru');
        $color_tags = GalleryTagColor::get();

        $content = view(env('THEME_RESOURCES') . 'pages.gallery.painters', [
            'type' => "reproduction",
            'linkroute' => route('hb.gallery.modern_painters'),
            'linktext' => __('gallery.rep_f3'),
            'page' => $gallery,
            'items' => $items,
            'painters' => $painters,
            'gallery_age' => $gallery_age,
            'gallery_genre' => $gallery_genre,
            'gallery_style' => $gallery_style,
            'gallery_nationality' => $gallery_nationality,
            'colors' => $color_tags,
        ]);


        $this->vars = Arr::add($this->vars, 'title', __("gallery.page_info_modern_painters_title"));
        $this->vars = Arr::add($this->vars, 'meta_desc', __('gallery.page_info_modern_painters_descr'));
        $this->vars = Arr::add($this->vars, 'content', $content);
        return $this->renderOutput();
    }




    public function painters(Request $request, $letter = false)
    {
        $search = $request->get('search') ?? null;

        $items = GalleryCategory::where("is_painter", 1);

        if ($search) {
            $search = "%".$search."%";
            $locales = App::getLocale();
            if($locales != 'en')
            {
                $ids = Translation::where('table_name', 'gallery_categories')
                ->where('column_name', 'name')
                ->where('value', 'like', $search)
                ->where('locale', $locales)
                ->pluck('foreign_key');
                $items = $items->whereIn('id', $ids);
            }
            else {
                $items = $items->where('name', 'like', $search);

            }
        }

        if($letter) {
            $search = $letter."%";

            $locales = App::getLocale();
            if($locales != 'en')
            {
                $ids = Translation::where('table_name', 'gallery_categories')
                ->where('column_name', 'name')
                ->where('value', 'like', $search)
                ->pluck('foreign_key');

                if($ids->isEmpty())
                {
                    $items = $items->where('name', 'like', $search);
                }
                else
                {
                    $items = $items->whereIn('id', $ids);
                }
            }
            else {
                $items = $items->where('name', 'like', $search);
            }
        }

        $items = $items->orderBy('painter_character', 'asc')->get()->translate(App::getLocale(), 'ru');

        $gallery = new GalleryType();
        $type = 'reproduction';
        $gallery = $gallery->getType($type);

        $painters = GalleryCategory::where("is_painter", 1)->inRandomOrder()->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_age = AGalleryAge::orderBy('sort')->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_genre = AGalleryGenre::orderBy('sort')->limit(19)->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_style = AGalleryStyle::orderBy('sort')->limit(19)->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_nationality = AGalleryNationality::orderBy('sort')->limit(19)->get()->translate(App::getLocale(), 'ru');

        $color_tags = GalleryTagColor::get();
        $content = view(env('THEME_RESOURCES') . 'pages.gallery.painters', [
            'type' => "reproduction",
            'linkroute' => route('hb.gallery.painters'),
            'linktext' => __('gallery.rep_painter'),
            'page' => $gallery,
            'items' => $items,
            'painters' => $painters,
            'gallery_age' => $gallery_age,
            'gallery_genre' => $gallery_genre,
            'gallery_style' => $gallery_style,
            'gallery_nationality' => $gallery_nationality,
            'colors' => $color_tags,
        ]);

        $this->vars = Arr::add($this->vars, 'title', __("gallery.page_info_painters_title"));
        $this->vars = Arr::add($this->vars, 'meta_desc', __('gallery.page_info_painters_descr'));
        $this->vars = Arr::add($this->vars, 'content', $content);
        return $this->renderOutput();
    }

    public function paintings_top(Request $request)
    {
        $items = GalleryCategory::where('painter_top20', 1)->where("is_painter", 1)->orderBy('name')->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery = new GalleryType();
        $type = 'reproduction';
        $gallery = $gallery->getType($type);

        $painters = GalleryCategory::where("is_painter", 1)->inRandomOrder()->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_age = AGalleryAge::orderBy('sort')->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_genre = AGalleryGenre::orderBy('sort')->limit(19)->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_style = AGalleryStyle::orderBy('sort')->limit(19)->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_nationality = AGalleryNationality::orderBy('sort')->limit(19)->get()->translate(App::getLocale(), 'ru');

        $color_tags = GalleryTagColor::get();
        $content = view(env('THEME_RESOURCES') . 'pages.gallery.painters', [
            'type' => "reproduction",
            'linkroute' => route('hb.gallery.paintings_top'),
            'linktext' => __('gallery.rep_f2'),
            'page' => $gallery,
            'items' => $items,
            'painters' => $painters,
            'gallery_age' => $gallery_age,
            'gallery_genre' => $gallery_genre,
            'gallery_style' => $gallery_style,
            'gallery_nationality' => $gallery_nationality,
            'colors' => $color_tags,
        ]);


        $this->vars = Arr::add($this->vars, 'title', __("gallery.page_info_paintings_top_title"));
        $this->vars = Arr::add($this->vars, 'meta_desc', __('gallery.page_info_paintings_top_descr'));
        $this->vars = Arr::add($this->vars, 'content', $content);
        return $this->renderOutput();
    }

    public function age(Request $request, $alias)
    {
        $items = AGalleryAge::where('alias', $alias)->first()->translate(App::getLocale(), 'ru');

        $param = [
            "meta_title" => $items->meta_title,
            "meta_desc" => $items->meta_description,
        ];

        return $this->universal($request, $alias, 'age', $param);
    }

    public function list_age(Request $request)
    {
        $curmodel = new AGalleryAge();
        return $this->list_items($request,$curmodel, 'a_gallery_age', 'age', __('gallery.rep_age'));
    }

    public function nationality(Request $request, $alias)
    {
        $items = AGalleryNationality::where('alias', $alias)->first()->translate(App::getLocale(), 'ru');

        $param = [
            "meta_title" => $items->meta_title,
            "meta_desc" => $items->meta_description,
        ];

        return $this->universal($request, $alias, 'style', $param);
    }

    public function list_nationality(Request $request)
    {
        $curmodel = new AGalleryNationality();
        return $this->list_items($request,$curmodel, 'a_gallery_nationality', 'nationality', __('gallery.rep_nationality'));
    }

    public function style(Request $request, $alias)
    {
        $items = AGalleryStyle::where('alias', $alias)->first()->translate(App::getLocale(), 'ru');

        $param = [
            "meta_title" => $items->meta_title,
            "meta_desc" => $items->meta_description,
        ];

        return $this->universal($request, $alias, 'style', $param);
    }

    public function list_styles(Request $request)
    {
        $curmodel = new AGalleryStyle();
        return $this->list_items($request,$curmodel, 'a_gallery_style', 'styles', __('gallery.rep_style'));
    }

    public function genre(Request $request, $alias)
    {
        $items = AGalleryGenre::where('alias', $alias)->first()->translate(App::getLocale(), 'ru');

        $param = [
            "meta_title" => $items->meta_title,
            "meta_desc" => $items->meta_description,
        ];

        return $this->universal($request, $alias, 'genre', $param);
    }

    public function list_genre(Request $request)
    {
        $curmodel = new AGalleryGenre();
        return $this->list_items($request, $curmodel, 'a_gallery_genre', 'genre', __('gallery.rep_genre'));
    }

    public function list_items(Request $request, $curmodel, $table_name, $route_name, $link_text)
    {
        $search = $request->get('search') ?? null;

        if ($search) {
            $search = "%".$search."%";
            $locales = App::getLocale();
            if($locales != 'en')
            {
                $ids = Translation::where('table_name', $table_name)
                ->where('column_name', 'name')
                ->where('value', 'like', $search)
                ->pluck('foreign_key');

                if($ids->isEmpty())
                {
                    $items = $curmodel->where('name', 'like', $search);
                }
                else
                {
                    $items = $curmodel->whereIn('id', $ids);
                }
            }
            else {
                $items = $curmodel->where('name', 'like', $search);
            }
        }
        else
        {
            $items = $curmodel;
        }

        $items = $items->orderBy('name', 'asc')->get()->translate(App::getLocale(), 'ru');

        $gallery = new GalleryType();
        $type = 'reproduction';
        $gallery = $gallery->getType($type);

        $painters = GalleryCategory::where("is_painter", 1)->inRandomOrder()->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_age = AGalleryAge::orderBy('sort')->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_genre = AGalleryGenre::orderBy('sort')->limit(19)->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_style = AGalleryStyle::orderBy('sort')->limit(19)->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_nationality = AGalleryNationality::orderBy('sort')->limit(19)->get()->translate(App::getLocale(), 'ru');

        $color_tags = GalleryTagColor::get();
        $content = view(env('THEME_RESOURCES') . 'pages.gallery.styles', [
            'type' => "reproduction",
            'rout' => "hb.gallery.list_{$route_name}",
            'linkroute' => route("hb.gallery.list_{$route_name}"),
            'linktext' => $link_text,
            'page' => $gallery,
            'items' => $items,
            'painters' => $painters,
            'gallery_age' => $gallery_age,
            'gallery_genre' => $gallery_genre,
            'gallery_style' => $gallery_style,
            'gallery_nationality' => $gallery_nationality,
            'colors' => $color_tags,
        ]);

        $this->vars = Arr::add($this->vars, 'title', __("gallery.page_info_{$route_name}_title"));
        $this->vars = Arr::add($this->vars, 'meta_desc', __("gallery.page_info_{$route_name}_descr"));
        $this->vars = Arr::add($this->vars, 'content', $content);
        return $this->renderOutput();
    }

    public function universal(Request $request, $alias, $filter_type, $param)
    {
        $page = $request->get('page') ?? null;
        $tag = $request->get('tag') ?? null;
        $color = $request->get('color') ?? null;
        $size = $request->get('size') ?? null;
        $order = $request->get('order') ?? null;
        $search = $request->get('search') ?? null;

        $params = $request->query();
        unset($params["_url"]);
        //dd($params);
        $filter = [
            'page' => $page,
            'tag' => $tag,
            'color' => $color,
            'size' => $size,
            'order' => $order,
            'search' => $search,
            "$filter_type" => $alias,
        ];

        $gallery = new GalleryType();
        $gc = new GalleryCategory();

        $type = 'reproduction';
        $category = false;


        $gallery = $gallery->getType($type);
        $items = $gc->getItems($gallery->id, $category, $filter);
        $items->appends($params);

        $categories = $gc->getAll($type);

        // dd($categories);
        $tags = false;
        $tag_color_ids = false;
        $color_tags = $gc->getColors("");


        $gallerypage = GalleryPage::first()->get()->translate(App::getLocale(), 'ru')[0];
        $categories_photo = $gc->getAll("photo");
        $categories_module = $gc->getAll("module");
        $categories_reproduction = $gc->getAll("reproduction");
        $bestseller = GalleryItem::where('is_bestseller', 1)->where('active', 1)->inRandomOrder()->limit(6)->get()->translate(App::getLocale(), 'ru');


        $top_slides = null;
        $pop_arts = null;
        $globs = null;
        $rec_arts = null;

        $all_globs = GallGlobs::first()->get()->translate(App::getLocale(), 'ru')[0];
        $all_arts = GalleryItem::inRandomOrder()->limit(10)->get()->translate(App::getLocale(), 'ru')->toArray();
        $glob = GalleryFiltersPage::first()->get()->translate(App::getLocale(), 'ru')[0];

        // sales
        $module_sale = GalleryItem::where('id_type', 2)->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 0)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $foto_sale = GalleryItem::where('id_type', 3)->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 0)->where('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $repr_sale = GalleryItem::where('id_type', 4)->where('custom_size_prices_sale', '!=',null)->where('is_big_sale', 0)->whereDate('sale_end','>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');

        // big salse
        $module_big_sale = GalleryItem::where('id_type', 2)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->whereDate('sale_end', '>=',Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $foto_big_sale = GalleryItem::where('id_type', 3)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->where('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $repr_big_sale = GalleryItem::where('id_type', 4)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');


        $top_slides = SliderRepr::all()->translate(App::getLocale(), 'ru');
        $globs = ModReprPage::first()->get()->translate(App::getLocale(), 'ru')[0];
        $pop_arts = GalleryItem::where('id_type', 4)->inRandomOrder()->limit(10)->get()
            ->translate(App::getLocale(), 'ru')->toArray();
        $rec_arts_ids = DB::table('gallery_items_mod_repr_page')->pluck('gallery_item_id')->toArray();
        $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');

        $painters = GalleryCategory::where("is_painter", 1)->inRandomOrder()->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_age = AGalleryAge::orderBy('sort')->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_genre = AGalleryGenre::orderBy('sort')->limit(19)->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_style = AGalleryStyle::orderBy('sort')->limit(19)->limit(19)->get()->translate(App::getLocale(), 'ru');
        $gallery_nationality = AGalleryNationality::orderBy('sort')->limit(19)->get()->translate(App::getLocale(), 'ru');

        $rep_last_items = GalleryItem::rep_last_items();

        $content = view(env('THEME_RESOURCES') . 'pages.gallery.reproduction', [
            'rep_last_items' => $rep_last_items,
            'bestseller' => $bestseller,
            'painters' => $painters,
            'gallery_age' => $gallery_age,
            'gallery_genre' => $gallery_genre,
            'gallery_style' => $gallery_style,
            'gallery_nationality' => $gallery_nationality,
            'page' => $gallery,
            'gallery' => $gallerypage,
            'categories_photo' => $categories_photo,
            'categories_module' => $categories_module,
            'categories_reproduction' => $categories_reproduction,
            'type' => $type,
            'top_slides' => $top_slides,
            'categories' => $categories,
            'category' => $category,
            'pop_arts' => $pop_arts,
            'all_arts' => $all_arts,
            'globs' => $globs,
            'glob' => $glob,
            'all_globs' => $all_globs,
            'rec_arts' => $rec_arts,
            'tags' => $tags,
            'colors' => $color_tags,
            'items' => $items,
            'module_sale' => $module_sale,
            'foto_sale' => $foto_sale,
            'repr_sale' => $repr_sale,
            'module_big_sale' => $module_big_sale,
            'foto_big_sale' => $foto_big_sale,
            'repr_big_sale' => $repr_big_sale,
        ]);

        $this->vars = Arr::add($this->vars, 'title', $param['meta_title']);
        $this->vars = Arr::add($this->vars, 'meta_desc', $param['meta_desc']);
        $this->vars = Arr::add($this->vars, 'content', $content);
        return $this->renderOutput();
    }


    public function hb_category_render(Request $request, $type, $category)
    {
        $page = $request->get('page') ?? null;
        $tag = $request->get('tag') ?? null;
        $color = $request->get('color') ?? null;

        $filter = [
            'page' => $page,
            'tag' => $tag,
            'color' => $color,
        ];

        $gallery = new GalleryType();
        $gc = new GalleryCategory();
        $gallery = $gallery->getType($type);
        $category = $gc->getBy($category);

        try {
            $categories = $category->getAll($type);
        } catch (Throwable $th) {
            return abort(404);
        }

        // sales
        $module_sale = GalleryItem::where('id_type', 2)->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 0)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $foto_sale = GalleryItem::where('id_type', 3)->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 0)->where('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $repr_sale = GalleryItem::where('id_type', 4)->where('custom_size_prices_sale', '!=',null)->where('is_big_sale', 0)->whereDate('sale_end','>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $bestseller = GalleryItem::where('is_bestseller', 1)->where('active', 1)->inRandomOrder()->limit(6)->get()->translate(App::getLocale(), 'ru');


        $tag_ids = explode(',', $category->id_tag_room);
        $tags = $gc->getTags($tag_ids);
        $tag_color_ids = explode(',', $category->id_tag_room);
        $color_tags = $gc->getColors($tag_color_ids);
        $items = $gc->getItems($gallery->id, $category->id, $filter);

        if (count((array) $category) > 0 || count((array) $category) > 0) {
            $glob = GalleryFiltersPage::first()->get()->translate(App::getLocale(), 'ru')[0];
            if ($type == 'module') {
                $pop_arts = GalleryItem::where('id_type', 2)->inRandomOrder()->limit(10)->get()
                ->translate(App::getLocale(), 'ru')->toArray();
                $rec_arts_ids = DB::table('gallery_items_mod_gall_page')->pluck('gallery_item_id')->toArray();
                $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');
            }

            if ($type == 'photo') {
                $pop_arts = GalleryItem::where('id_type', 3)->inRandomOrder()->limit(10)->get()
                ->translate(App::getLocale(), 'ru')->toArray();
                $rec_arts_ids = DB::table('gallery_items_mod_photo_page')->pluck('gallery_item_id')->toArray();
                $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');
            }

            if ($type == 'reproduction') {
                $pop_arts = GalleryItem::where('id_type', 4)->inRandomOrder()->limit(10)->get()
                ->translate(App::getLocale(), 'ru')->toArray();
                $rec_arts_ids = DB::table('gallery_items_mod_repr_page')->pluck('gallery_item_id')->toArray();
                $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');
            }

            $all_arts = GalleryItem::inRandomOrder()->limit(10)->get()->translate(
                App::getLocale(),
                'ru'
            )->toArray();

            $content = view(env('THEME_RESOURCES') . 'pages.gallery.gallery_category', [
                'module_sale' => $module_sale,
                'bestseller' => $bestseller,
                'foto_sale' => $foto_sale,
                'repr_sale' => $repr_sale,
                'page' => $gallery,
                'glob' => $glob,
                'category' => $category,
                'categories' => $categories,
                'tags' => $tags,
                'colors' => $color_tags,
                'items' => $items,
                'pop_arts' => $pop_arts,
                'rec_arts' => $rec_arts,
                'all_arts' => $all_arts,
            ]);

            $this->vars = Arr::add($this->vars, 'title', $category['name']);
            //$this->vars = Arr::add($this->vars, 'meta_desc', $gallery['meta_desc']);
            $this->vars = Arr::add($this->vars, 'content', $content);
            return $this->renderOutput();

        } else {
            return abort(404);
        }
    }


    public function ram_search(Request $request)
    {
        $search_id = $request->get('search_id') ?? null;
        $rammaterialid = $request->get('rammaterialid') ?? null;
        $ramcolorid = $request->get('ramcolorid') ?? null;
        $def_inter_rams = CanvasRam::where("id",">", 16);

        if((int)$search_id)
        {
            $def_inter_rams = $def_inter_rams->where("id", $search_id);
        }

        if((int)$ramcolorid)
        {
        	$def_inter_rams = $def_inter_rams->whereHas('color', function ($query) use ($ramcolorid) {
        		$query->where('id', $ramcolorid);
        	});
        }

        if((int)$rammaterialid)
        {
        	$def_inter_rams = $def_inter_rams->whereHas('material', function ($query) use ($rammaterialid) {
        		$query->where('id', $rammaterialid);
        	});
        }


        $def_inter_rams = $def_inter_rams->with("color")->with("material")->get();


        // if(!$def_inter_rams)
        // {
        //     $def_inter_rams = CanvasRam::where("id",">", 16)->with("color")->with("material")->get();
        //     return $this->rams_parse($def_inter_rams);
        // }


        return $this->rams_parse($def_inter_rams);

    }


    public function rams_parse($items){
        $def_inter_rams = $items;
        if($def_inter_rams)
        {
            $i = 0;
            $rams_array = [];
            foreach($def_inter_rams as $rams)
            {
                $id = $rams->id;
                $imgPath = "/storage/".$rams->img;
                $img = asset($imgPath);
                $imgWebp = function_exists('image_webp_url') ? (image_webp_url($imgPath) ?: $img) : $img;
                if(isset($rams->material->first()->name)) {
                    $material = translated_value($rams->material->first(), 'name');
                }
                else {
                    $material = false;
                }

                if(isset($rams->color->first()->name)){
                    $color = translated_value($rams->color->first(), 'name');
                }
                else {
                    $color = false;
                }

                $width = "$rams->width cm";
                $height = "$rams->height cm";
                $price = "$rams->price €";

                $rams_array[$i]['id'] = $id;
                $rams_array[$i]['img'] = $img;
                $rams_array[$i]['img_webp'] = $imgWebp;
                $rams_array[$i]['material'] = $material;
                $rams_array[$i]['color'] = $color;
                $rams_array[$i]['width'] = $width;
                $rams_array[$i]['height'] = $height;
                $rams_array[$i]['price'] = $price;
                $rams_array[$i]['lng_id'] = __("gallery.code").":";
                $rams_array[$i]['lng_material'] = __("gallery.material").":";
                $rams_array[$i]['lng_color'] = __("gallery.shade").":";
                $rams_array[$i]['lng_width'] = __("modular_pictures.index28").":";
                $rams_array[$i]['lng_height'] = __("modular_pictures.index30").":";
                $rams_array[$i]['lng_price'] = __("gallery.ram_total_price").":";
                $rams_array[$i]['lng_stock'] = __("cart_new.in_stock");
                $i++;
            }

            return response()->json($rams_array);
        }
        else {
            return response()->json([]);
        }
    }

    public function hb_item_render(Request $request, $type, $item)
    {
        $themeResource = config('theme.resource') ?: env('THEME_RESOURCES') ?: 'theme.viar.';

        // --- Канонизация типа товара по ID ---
        $row = GalleryItem::select('id', 'id_type')->find($item);
        if (!$row) {
            abort(404);
        }

        // Получаем правильный slug типа из gallery_types.url
        $actualType = GalleryType::where('id', $row->id_type)->value('url');
        if (!$actualType) {
            abort(404);
        }

        // Если тип в URL неверный — редиректим на правильный URL (301), сохраняя query-параметры
        if (mb_strtolower($type) !== mb_strtolower($actualType)) {
            return redirect()->route(
                'hb.gallery.item.single',
                array_merge(['type' => $actualType, 'item' => $row->id], $request->query()),
                301
            );
        }

        $category = false;

        $filter = [
            'one' => $item,
        ];
        $gallery = new GalleryType();
        $gallery = $gallery->getType($type);
        $gc = new GalleryCategory();

        $category = $gc->getBy($category);
        if ($category == null) {
            return abort(404);
        }

        if ($gallery == null) {
            return abort(404);
        }

        $page = $gallery;

        $items = $gc->getItems($gallery->id, $category->id, $filter);
        $item_id = $item;
        $item = GalleryItem::where('id', $item_id)
        ->with('get_age')
        ->with('get_style')
        ->with('get_genre')
        ->with('cats')
        ->firstOrFail()
        ->translate(App::getLocale(), 'ru');

        // sizes
        $size_ids = DB::table('gallery_items_ gallery_tag_sizes')->where('gallery_item_id', $item_id)->pluck('gallery_size_id');

        $sizes = [];

        foreach ($size_ids as $id) {
            $sizes[] = (array) DB::table('gallery_sizes')->where('id', $id)->first();
        }

        // holsts
        $holst_ids = DB::table('gallery_items_gallery_holsts')->where('gallery_item_id', $item_id)->pluck('gallery_holst_id');

        $holsts = [];

        foreach ($holst_ids as $id) {
            $holsts[] = (array) DB::table('gallery_holsts')->where('id', $id)->first();
        }
        // boxes

        $boxes_ids = DB::table('gallery_items_ gallery_boxes')->where('gallery_item_id', $item_id)->pluck('gallery_box_id');
        $boxes = [];

        foreach ($boxes_ids as $id) {
            $boxes[] = (array) DB::table('gallery_boxes')->where('id', $id)->first();
        }
        //

        $items_big_sale = GalleryItem::where('id_type', $item['id_type'])->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 1)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $cat_info = GalleryType::where('id', $item['id_type'])->get()->translate(App::getLocale(), 'ru')[0];

        // cur lang
        $segments = \Request::segments();
        $first = array_shift($segments);
        $langs = ['en', 'ee', 'lt', 'lv', 'pl', 'de', 'ru'];
        if (!in_array($first, $langs)) {
            $first = '';
        }


        // sales
        $module_sale = GalleryItem::where('id_type', 2)->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 0)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $foto_sale = GalleryItem::where('id_type', 3)->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 0)->where('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $repr_sale = GalleryItem::where('id_type', 4)->where('custom_size_prices_sale', '!=',null)->where('is_big_sale', 0)->whereDate('sale_end','>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');

        // big salse
        $module_big_sale = GalleryItem::where('id_type', 2)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->whereDate('sale_end', '>=',Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $foto_big_sale = GalleryItem::where('id_type', 3)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->where('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $repr_big_sale = GalleryItem::where('id_type', 4)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');


        /// TODO: определение наценки по ip
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

        $AProductionTime = AProductionTime::where('category', 'canvas')->first()->translate(App::getLocale(), 'ru');

        $canonicalUrl = url()->current();
        $normalizeUrl = function (string $path): string {
            if (preg_match('~^https?://~i', $path)) {
                return $path;
            }

            return url(ltrim($path, '/'));
        };

        $galleryImages = json_decode((string) $item->images, true);
        $ogImageUrl = url('/img/logo.png');

        if (is_array($galleryImages) && !empty($galleryImages[0])) {
            $ogImageUrl = $normalizeUrl('/storage/' . ltrim($galleryImages[0], '/'));
        } elseif (!empty($item->new_main_image)) {
            $ogImageUrl = $normalizeUrl(\Voyager::image($item->new_main_image));
        }
        $ogImageUrl = single_image_url('gallery-item-' . $item->id, seo_image_version($item->updated_at ?? null));

        $breadcrumbItems = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => trans('account.index1'),
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => trans('breadcrumbs.gallery'),
                'item' => route('hb.gallery.index'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $page->name,
                'item' => route('hb.gallery.module', $page->url),
            ],
            [
                '@type' => 'ListItem',
                'position' => 4,
                'name' => $item->name,
                'item' => $canonicalUrl,
            ],
        ];

        $structuredData = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => trim(strip_tags((string) $item->name)),
                'description' => trim(strip_tags((string) $item->name)),
                'url' => $canonicalUrl,
                'image' => $ogImageUrl,
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $breadcrumbItems,
            ],
            seo_product_schema(
                (string) $item->name,
                (string) ($item->meta_desc ?? $item->name),
                $canonicalUrl,
                $ogImageUrl,
                $item->minSumPrice ?? null
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
        $new = DB::table('reviews')->where('active', 1)->where('pid',$item_id)->where('orig_locale', App::getLocale())->get()->map($process);
        $revs = $old->merge($new)->sortByDesc('created_at')->values();

        $gallerypage = GalleryPage::first()->get()->translate(App::getLocale(), 'ru')[0];

        // $similar_paintings = GalleryItem::where('id_type', $gallery->id)->where('active',1)->inRandomOrder()->limit(8)->get()->translate(App::getLocale(), 'ru');



        $color_items_ids = DB::table('gallery_category_gallery_item')->where('gallery_item_id', $item->id)->first();
        if(isset($color_items_ids->gallery_category_id) && $color_items_ids->gallery_category_id)
        {
            $color_items_ids = DB::table('gallery_category_gallery_item')->where('gallery_category_id', $color_items_ids->gallery_category_id)->inRandomOrder()->limit(8)->pluck('gallery_item_id')->toArray();


            if(count($color_items_ids) > 0 ) {
                $similar_paintings = GalleryItem::whereIn('id', $color_items_ids)->where('active',1)->inRandomOrder()->limit(8)->get()->translate(App::getLocale(), 'ru');
            }
            else {
                $similar_paintings = false;
            }
        }
        else {
            $similar_paintings = false;
        }


        //$def_inter_rams = CanvasRam::where("id",">",16)->with("color")->with("material")->get();
        $def_inter_rams = CanvasRam::where("id",">",16)->with("color")->with("material")->get();
        $def_inter_rams_color = CanvasRamsColor::get()->translate(App::getLocale(), 'ru');
        $def_inter_rams_material = CanvasRamsMaterial::get()->translate(App::getLocale(), 'ru');


        $locale = \Illuminate\Support\Facades\App::getLocale();
        $h2_titles = $item->translate($locale);

        if (strpos($item->name, ' - ') !== false) {
            $separator = " - ";
            $parts = explode($separator, $item->name);

            $artist_name= $parts[0];
            $painting_name =$parts[1];

        } else {
            $artist_name= $item->name;
            $painting_name =$item->name;
        }

        $supportsWebp = $request->attributes->get('supportsWebp');


        $creepingLine = CreepingLine::where('page->gallery', 'gallery')->where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');

        $recommendationDiscount = $request->query('recommendation_discount');

        if ($recommendationDiscount && is_numeric($recommendationDiscount)) {
            $basket = $request->session()->get('basket', []);
            $basket = $this->basketRepository->normalizeBasket($basket);
            $hasRecommendedProduct = false;

            if (is_array($basket)) {
                foreach ($basket as $item_l) {
                    if (isset($item_l['is_recommendation']) && $item_l['is_recommendation'] === true) {
                        $hasRecommendedProduct = true;
                        break;
                    }
                }
            }

            if (!$hasRecommendedProduct) {
                $recommendationDiscount = (int) /*$recommendationDiscount*/30;
                $sessionKey = 'recommendation_discount_' . $item_id;
                $request->session()->put($sessionKey, $recommendationDiscount);
            } else {
                $recommendationDiscount = null;
            }
        } else {
            $recommendationDiscount = null;
        }

        if($item->id_type == 4)
        {

            $glob = GalleryFiltersPage::first()->get()->translate(App::getLocale(), 'ru')[0];

            $viewed_products = $this->show_viewed($request, 'viewed_products_rep', $item_id);
            $viewed_products = GalleryItem::viewed_products($viewed_products);



            $content = view($themeResource . 'pages.gallery.item-card-reproduction', [
                'supportsWebp' => $supportsWebp,
                'type' => $item->id_type,
                'creepingLine' => $creepingLine,
                'artist_name' => $artist_name,
                'painting_name' => $painting_name,
                'h2_titles' => $h2_titles,
                'gallery' => $gallerypage,
                'glob' => $glob,
                'viewedProducts' => $viewed_products,
                'similar_paintings' => $similar_paintings,
                'revs' => $revs,
                'AProductionTime' => $AProductionTime,
                'page' => $gallery,
                'cur_loc' => $first,
                'category' => $category,
                'main_pages' => \App\Models\PageSlug::first()->get()->translate(App::getLocale(), 'lv')[0],
                'items_big_sale' => $items_big_sale,
                'def_inter_images' => CanvasInterier::all(),
                'def_inter_rams' => $def_inter_rams,
                'def_inter_rams_color' => $def_inter_rams_color,
                'def_inter_rams_material' => $def_inter_rams_material,
                'int_globs' => CanvasGlobal::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
                'canv_bot' => CanvasCompStepsArtsPack::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
                'cat_name' => $cat_info['name'],
                'reviews' => $revs,
                'galleryBoxes' => $this->galleryBoxRepository->getAll(),
                'gallerySizes' => $this->gallerySizeRepository->getAll(),
                'galleryHolsts' => $this->galleryHolstRepository->getAll(),
                'canvas_head' => CanvasHeader::first()->get()->translate(App::getLocale(), 'ru')[0],
                'item' => $item,
                'sizes' => $sizes,
                'holsts' => $holsts,
                'boxes' => $boxes,
                'module_sale' => $module_sale,
                'foto_sale' => $foto_sale,
                'repr_sale' => $repr_sale,
                'module_big_sale' => $module_big_sale,
                'foto_big_sale' => $foto_big_sale,
                'repr_big_sale' => $repr_big_sale,
                'contry_mult' => $contry_mult,
                'recommendationDiscount' => $recommendationDiscount,
                'faqs' => PageFaq::where('page->gallery_product_card', 'gallery_product_card')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),
            ]);
        }
        else if($item->id_type == 3 || $item->id_type == 2)
        {
            $viewed_products = $this->show_viewed($request, 'viewed_products', $item_id);
            $viewed_products = GalleryItem::viewed_products($viewed_products);

            $content = view($themeResource . 'pages.gallery.item-card-module', [
                'supportsWebp' => $supportsWebp,
                'type' => $item->id_type,
                'creepingLine' => $creepingLine,
                'h2_titles' => $h2_titles,
                'gallery' => $gallerypage,
                'viewedProducts' => $viewed_products,
                'similar_paintings' => $similar_paintings,
                'revs' => $revs,
                'AProductionTime' => $AProductionTime,
                'page' => $gallery,
                'cur_loc' => $first,
                'category' => $category,
                'main_pages' => \App\Models\PageSlug::first()->get()->translate(App::getLocale(), 'lv')[0],
                'items_big_sale' => $items_big_sale,
                'def_inter_images' => CanvasInterier::all(),
                'def_inter_rams' => CanvasRam::where("id",">",16)->get(),
                'int_globs' => CanvasGlobal::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
                'canv_bot' => CanvasCompStepsArtsPack::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
                'cat_name' => $cat_info['name'],
                'reviews' => $revs,
                'galleryBoxes' => $this->galleryBoxRepository->getAll(),
                'gallerySizes' => $this->gallerySizeRepository->getAll(),
                'galleryHolsts' => $this->galleryHolstRepository->getAll(),
                'canvas_head' => CanvasHeader::first()->get()->translate(App::getLocale(), 'ru')[0],
                'item' => $item,
                'sizes' => $sizes,
                'holsts' => $holsts,
                'boxes' => $boxes,
                'module_sale' => $module_sale,
                'foto_sale' => $foto_sale,
                'repr_sale' => $repr_sale,
                'module_big_sale' => $module_big_sale,
                'foto_big_sale' => $foto_big_sale,
                'repr_big_sale' => $repr_big_sale,
                'contry_mult' => $contry_mult,
                'recommendationDiscount' => $recommendationDiscount,
                'faqs' => PageFaq::where('page->gallery_product_card', 'gallery_product_card')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),
            ]);
        }
        else
        {
            return abort(404);
        }

        $style = IndexController::get_styles_for_quiz(App::getLocale());
        $modals = view($themeResource . 'pages.gallery.zpart_modals')->render();
        $all = view($themeResource . 'pages.index.modals')
        ->with([
            'style' => $style,
            'current_quiz_style_id' => 1,
            ])
            ->render();

        $this->vars = Arr::add($this->vars, 'creepingLine', $creepingLine);
        $this->vars = Arr::add($this->vars, 'title', $item->meta_title);
        $this->vars = Arr::add($this->vars, 'meta_desc', $item->meta_desc);
        $this->vars = Arr::add($this->vars, 'modals', $modals.$all);
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'updated_at', $item->updated_at);
        $this->vars = Arr::add($this->vars, 'og_url', $canonicalUrl);
        $this->vars = Arr::add($this->vars, 'og_image', $ogImageUrl);
        $this->vars = Arr::add($this->vars, 'image_src', $ogImageUrl);
        $this->vars = Arr::add($this->vars, 'og_type', 'website');
        $this->vars = Arr::add($this->vars, 'structured_data', $structuredData);
        return $this->renderOutput();
    }
/*------------------------------------------------------- */
    public function show_viewed(Request $request,$pram, $id)
    {
        $viewedProducts = $request->session()->get($pram, []);

        $viewedProducts = collect($viewedProducts);
        $viewedProducts = $viewedProducts->unique();

        // Remove the product from the collection if it already exists
        $viewedProducts->forget($id);

        // Add the product to the beginning of the collection
        $viewedProducts->prepend($id);

        // Truncate the collection to 10 items if it has more
        if ($viewedProducts->count() > 10) {
            $viewedProducts = $viewedProducts->slice(0, 10);
        }

        $viewedProducts = $viewedProducts->toArray();

        // Save the updated collection to the session
        $request->session()->put($pram, $viewedProducts);

        return $viewedProducts;
    }

    public function render()
    {
        return redirect()->route('hb.gallery.index');

        $gallery = GalleryPage::first()->get()->translate(App::getLocale(), 'ru')[0];

        return view('gallery')->with('gallery', $gallery);
    }

    public function type_render(Request $request, $type)
    {
        if($type == "stylization-paintings")
        {
            return redirect()->route('photo_portrait');
        }

        return redirect()->route('hb.gallery.module', ["type"=>$type]);

        $gallery = new GalleryType();
        $category = new GalleryCategory();

        try {
            $gallery = $gallery->getType($type);
        } catch (Throwable $th) {
            return abort(404);
        }

        $categories = $category->getAll($type);

        if (count((array) $gallery) > 0 || count((array) $categories) > 0) {
            $top_slides = null;
            $pop_arts = null;
            $globs = null;
            $rec_arts = null;

            if ($type == 'module') {
                $top_slides = SliderMod::all()->translate(App::getLocale(), 'ru');
                $globs = ModGallPage::first()->get()->translate(App::getLocale(), 'ru')[0];
                $pop_arts = GalleryItem::where('id_type', 2)->inRandomOrder()->limit(10)->get()
                    ->translate(App::getLocale(), 'ru')->toArray();
                $rec_arts_ids = DB::table('gallery_items_mod_gall_page')->pluck('gallery_item_id')->toArray();
                $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');
            }

            if ($type == 'photo') {
                $top_slides = SliderPhoto::all()->translate(App::getLocale(), 'ru');
                $globs = ModPhotoPage::first()->get()->translate(App::getLocale(), 'ru')[0];
                $pop_arts = GalleryItem::where('id_type', 3)->inRandomOrder()->limit(10)->get()
                    ->translate(App::getLocale(), 'ru')->toArray();
                $rec_arts_ids = DB::table('gallery_items_mod_photo_page')->pluck('gallery_item_id')->toArray();
                $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');
            }

            if ($type == 'reproduction') {
                $top_slides = SliderRepr::all()->translate(App::getLocale(), 'ru');
                $globs = ModReprPage::first()->get()->translate(App::getLocale(), 'ru')[0];
                $pop_arts = GalleryItem::where('id_type', 4)->inRandomOrder()->limit(10)->get()
                    ->translate(App::getLocale(), 'ru')->toArray();
                $rec_arts_ids = DB::table('gallery_items_mod_repr_page')->pluck('gallery_item_id')->toArray();
                $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');
            }

            if ($type == 'oil-pictures') {
                $top_slides = SliderRepr::all()->translate(App::getLocale(), 'ru');
                $globs = ModReprPage::first()->get()->translate(App::getLocale(), 'ru')[0];
                $pop_arts = GalleryItem::where('id_type', 4)->inRandomOrder()->limit(10)->get()
                    ->translate(App::getLocale(), 'ru')->toArray();
                $rec_arts_ids = DB::table('gallery_items_mod_repr_page')->pluck('gallery_item_id')->toArray();
                $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');
            }

            if(!$globs){
                $globs = ModReprPage::first()->get()->translate(App::getLocale(), 'ru')[0];
            }

            //dd($type, $globs);

            $all_globs = GallGlobs::first()->get()->translate(App::getLocale(), 'ru')[0];
            $all_arts = GalleryItem::inRandomOrder()->limit(10)->get()->translate(App::getLocale(), 'ru')->toArray();

            return view(
                'gallery_block_category',
                [
                    'page' => $gallery,
                    'type' => $type,
                    'top_slides' => $top_slides,
                    'categories' => $categories,
                    'pop_arts' => $pop_arts,
                    'all_arts' => $all_arts,
                    'globs' => $globs,
                    'all_globs' => $all_globs,
                    'rec_arts' => $rec_arts,
                ]
            );
        } else {
            return abort(404);
        }
    }

    public function category_render(Request $request, $type, $category)
    {
        return redirect()->route('hb.gallery.category', ["type"=>$type, "category"=>$category]);

        $page = $request->get('page') ?? null;
        $tag = $request->get('tag') ?? null;
        $color = $request->get('color') ?? null;

        $filter = [
            'page' => $page,
            'tag' => $tag,
            'color' => $color,
        ];

        $gallery = new GalleryType();
        $gc = new GalleryCategory();
        $gallery = $gallery->getType($type);
        $category = $gc->getBy($category);

        try {
            $categories = $category->getAll($type);
        } catch (Throwable $th) {
            return abort(404);
        }

        $tag_ids = explode(',', $category->id_tag_room);
        $tags = $gc->getTags($tag_ids);
        $tag_color_ids = explode(',', $category->id_tag_room);
        $color_tags = $gc->getColors($tag_color_ids);
        $items = $gc->getItems($gallery->id, $category->id, $filter);

        if (count((array) $category) > 0 || count((array) $category) > 0) {
            $glob = GalleryFiltersPage::first()->get()->translate(App::getLocale(), 'ru')[0];
            if ($type == 'module') {
                $pop_arts = GalleryItem::where('id_type', 2)->inRandomOrder()->limit(10)->get()
                ->translate(App::getLocale(), 'ru')->toArray();
                $rec_arts_ids = DB::table('gallery_items_mod_gall_page')->pluck('gallery_item_id')->toArray();
                $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');
            }

            if ($type == 'photo') {
                $pop_arts = GalleryItem::where('id_type', 3)->inRandomOrder()->limit(10)->get()
                ->translate(App::getLocale(), 'ru')->toArray();
                $rec_arts_ids = DB::table('gallery_items_mod_photo_page')->pluck('gallery_item_id')->toArray();
                $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');
            }

            if ($type == 'reproduction') {
                $pop_arts = GalleryItem::where('id_type', 4)->inRandomOrder()->limit(10)->get()
                ->translate(App::getLocale(), 'ru')->toArray();
                $rec_arts_ids = DB::table('gallery_items_mod_repr_page')->pluck('gallery_item_id')->toArray();
                $rec_arts = GalleryItem::whereIn('id', $rec_arts_ids)->get()->translate(App::getLocale(), 'ru');
            }

            $all_arts = GalleryItem::inRandomOrder()->limit(10)->get()->translate(
                App::getLocale(),
                'ru'
            )->toArray();

            return view('gallery_category', [
                'page' => $gallery,
                'glob' => $glob,
                'category' => $category,
                'categories' => $categories,
                'tags' => $tags,
                'colors' => $color_tags,
                'items' => $items,
                'pop_arts' => $pop_arts,
                'rec_arts' => $rec_arts,
                'all_arts' => $all_arts,
            ]);
        } else {
            return abort(404);
        }
    }

    public function item_render(Request $request, $type, $category, $item)
    {
        return redirect()->route('hb.gallery.item.single', ["type"=>$type, "item"=>$item]);

        $filter = [
            'one' => $item,
        ];

        $gallery = new GalleryType();
        $gallery = $gallery->getType($type);
        $gc = new GalleryCategory();

        $category = $gc->getBy($category);
        if ($category == null) {
            return abort(404);
        }

        if ($gallery == null) {
            return abort(404);
        }

        $items = $gc->getItems($gallery->id, $category->id, $filter);
        $item_id = $item;
        $item = GalleryItem::where('id', $item_id)->firstOrFail()->translate(App::getLocale(), 'ru');

        // sizes
        $size_ids = DB::table('gallery_items_ gallery_tag_sizes')->where('gallery_item_id', $item_id)->pluck('gallery_size_id');

        $sizes = [];

        foreach ($size_ids as $id) {
            $sizes[] = (array) DB::table('gallery_sizes')->where('id', $id)->first();
        }

        // holsts
        $holst_ids = DB::table('gallery_items_gallery_holsts')->where('gallery_item_id', $item_id)->pluck('gallery_holst_id');

        $holsts = [];

        foreach ($holst_ids as $id) {
            $holsts[] = (array) DB::table('gallery_holsts')->where('id', $id)->first();
        }
        // boxes

        $boxes_ids = DB::table('gallery_items_ gallery_boxes')->where('gallery_item_id', $item_id)->pluck('gallery_box_id');
        $boxes = [];

        foreach ($boxes_ids as $id) {
            $boxes[] = (array) DB::table('gallery_boxes')->where('id', $id)->first();
        }
        //

        $items_big_sale = GalleryItem::where('id_type', $item['id_type'])->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 1)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $cat_info = GalleryType::where('id', $item['id_type'])->get()->translate(App::getLocale(), 'ru')[0];

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
        $new = DB::table('reviews')->where('active', 1)->where('pid',$item_id)->where('orig_locale', App::getLocale())->get()->map($process);
        $revs = $old->merge($new)->sortByDesc('created_at')->values();

        // cur lang
        $segments = \Request::segments();
        $first = array_shift($segments);
        $langs = ['en', 'ee', 'lt', 'lv', 'pl', 'de', 'ru'];
        if (!in_array($first, $langs)) {
            $first = '';
        }

        return view('gallery_item', [
            'page' => $gallery,
            'cur_loc' => $first,
            'category' => $category,
            'main_pages' => \App\Models\PageSlug::first()->get()->translate(App::getLocale(), 'lv')[0],
            'items_big_sale' => $items_big_sale,
            'def_inter_images' => CanvasInterier::all(),
            'def_inter_rams' => CanvasRam::all()->translate(App::getLocale(), 'ru'),
            'int_globs' => CanvasGlobal::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'canv_bot' => CanvasCompStepsArtsPack::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'cat_name' => $cat_info['name'],
            'reviews' => $revs,
            'galleryBoxes' => $this->galleryBoxRepository->getAll(),
            'gallerySizes' => $this->gallerySizeRepository->getAll(),
            'galleryHolsts' => $this->galleryHolstRepository->getAll(),
            'canvas_head' => CanvasHeader::first()->get()->translate(App::getLocale(), 'ru')[0],
            'item' => $item,
            'sizes' => $sizes,
            'holsts' => $holsts,
            'boxes' => $boxes,
        ]);
    }

}
