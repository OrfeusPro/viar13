<?php

namespace App\Http\Controllers\Pages;

use App\Models\PageFaq;
use App\Models\AllStyle;
use App\Models\CanvasRam;
use App\Models\GalleryItem;
use Illuminate\Support\Arr;
use App\Models\AllStyleForm;
use App\Models\CreepingLine;
use Illuminate\Http\Request;
use App\Models\NewhomeWorkEx;
use App\Models\NewhomeService;
use App\Models\PortraitSlider;
use App\Models\AProductionTime;
use App\Models\CanvasRamsColor;
use App\Models\CanvasRamsMaterial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use App\Repositories\BasketRepository;
use App\Http\Controllers\IndexController;
use App\Models\HomepageOption as HomeData;
use App\Repositories\GalleryBoxRepository;
use App\Repositories\GalleryHolstRepository;
use App\Repositories\GalleryDecorationRepository;

class PagePortraitOilController extends Controller
{
    const TYPE_ID = 1068;
    private $basketRepository;

    public function __construct()
    {
        $this->template = (config('theme.resource') ?: 'theme.viar.') . '.index';
        $this->basketRepository = resolve(BasketRepository::class);
    }
    /**
     * Displays new page
     *
     * @return void
     */
    public function index(
        Request $request,
        GalleryHolstRepository $galleryHolstRepository,
        GalleryDecorationRepository $galleryDecorationRepository,
        GalleryBoxRepository $galleryBoxRepository,
        IndexController $IndexController
    ) {
        $slug = "portrait-oil";
        $item = GalleryItem::withTranslation(App::getLocale(), false)->whereTranslation('slug', $slug)
            ->firstOrFail();
        $locale = App::getLocale();
        $h2_titles = $item->translate($locale);

        $works_ids = \DB::table('gallery_items_newhome_top_work_ex')->where('gallery_item_id', $item->id)
            ->pluck('newhome_top_work_ex_id')->toArray();

        $style = $IndexController->get_styles_for_quiz(App::getLocale());

        $current_quiz_style_id = GalleryItem::withTranslation(App::getLocale(), false)
            ->whereTranslation('slug', '=', $slug)->where('id_type', 5)->first();
        if ($current_quiz_style_id) {
            $current_quiz_style_id = $current_quiz_style_id->id;
        }

        $AProductionTime = AProductionTime::where('category', 'portrait')->first()->translate(App::getLocale(), 'ru');

        $canonicalUrl = url()->current();
        $normalizeUrl = function (string $path): string {
            if (preg_match('~^https?://~i', $path)) {
                return $path;
            }

            return url(ltrim($path, '/'));
        };

        $ogImageUrl = null;
        $sliderPreview = PortraitSlider::where('cat_id', $item->id)
            ->where('is_show', 1)
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'desc')
            ->first() ?: $item->sliderImage;
        $mainMedia = $item->getMedia('our_works_new')->first();
        $backgroundMedia = $item->getMedia('background')->first();
        $heroSlide = $item->getMedia('works_examples_new')->first();

        if ($sliderPreview && !empty($sliderPreview->size_img)) {
            $ogImageUrl = asset('storage/' . ltrim((string) $sliderPreview->size_img, '/'));
        } elseif ($sliderPreview && !empty($sliderPreview->png)) {
            $ogImageUrl = asset('storage/' . ltrim((string) $sliderPreview->png, '/'));
        } elseif ($mainMedia) {
            $ogImageUrl = $normalizeUrl($mainMedia->getUrl());
        } elseif ($backgroundMedia) {
            $ogImageUrl = $normalizeUrl($backgroundMedia->getUrl());
        } elseif ($heroSlide) {
            $ogImageUrl = $normalizeUrl($heroSlide->getUrl());
        } elseif (!empty($item->new_main_image)) {
            $ogImageUrl = $normalizeUrl(\Voyager::image($item->new_main_image));
        } else {
            $ogImageUrl = url('/img/logo.png');
        }

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
                'name' => $item->getTranslatedAttribute('name'),
                'item' => $canonicalUrl,
            ],
        ];

        $structuredData = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => trim(strip_tags((string) $item->getTranslatedAttribute('meta_title'))),
                'description' => trim(strip_tags((string) $item->getTranslatedAttribute('meta_desc'))),
                'url' => $canonicalUrl,
                'image' => $ogImageUrl,
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $breadcrumbItems,
            ],
            seo_product_schema(
                (string) $item->getTranslatedAttribute('name'),
                (string) $item->getTranslatedAttribute('meta_desc'),
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

        $old = DB::table('our_works')->where('active', 1)->where('orig_locale', App::getLocale())->get()->map($process);
        $new = DB::table('reviews')->where('active', 1)->where('pid', $item->id)->where('orig_locale', App::getLocale())->get()->map($process);
        $revs = $old->merge($new)->sortByDesc('created_at')->values();

        $creepingLine = CreepingLine::where('page->portret', 'portret')->where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');

        $homeSlides = PortraitSlider::where('cat_id', $item->id)->where('is_show', 1)->orderBy('sort', 'asc')->get()
            ->translate(App::getLocale(), 'ru');

        $firstSlide = $homeSlides->first();
        if ($firstSlide && !empty($firstSlide['png'])) {
            $ogImageUrl = $normalizeUrl(\Voyager::image($firstSlide['png']));
        }
        $ogImageUrl = single_image_url('product-oil-' . $item->id, seo_image_version($item->updated_at ?? null));
        $structuredData[0]['image'] = $ogImageUrl;

        $content = view((config('theme.resource') ?: 'theme.viar.') . 'pages.portrait-oil')->with([
            'h2_titles'=>$h2_titles,
            'item' =>  $item,
            'revs' => $revs,
            'services' =>  NewhomeService::where('is_show', 1)->withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
            'creepingLine' => $creepingLine,
            'map_size' => $this->fetchSizes($item),
            'list_person_price' => $this->fetchPersonPrices($item),
            'map_frame' => $this->fetchFrames(),
            'AProductionTime' =>  $AProductionTime,
            'style' => $style,
            'home_slides' => $homeSlides,
            'current_quiz_style_id' => $current_quiz_style_id,
            'is_portrait_page' => 1,
            'is_hide_url_params' => 1,
            'canvas_items' => $galleryHolstRepository->getAll(),
            'list_example' => $this->fetchExamples($item),
            'sets' => $galleryBoxRepository->getAll(),
            'etc_styles_items' => GalleryItem::withTranslation(App::getLocale(), false)
                ->whereTranslation('slug', '!=', $slug)->where('id_type', 5)->get(),
            'art_items' => $galleryDecorationRepository->getAll(),
            'frames' => CanvasRam::withTranslation(App::getLocale(), false)->get(),
            'work_ex' =>  NewhomeWorkEx::withTranslation(App::getLocale(), false)
                ->whereIn('id', $works_ids)->get(),
            'home' => HomeData::withTranslation(App::getLocale(), false)
                ->select('all_styles', 'all_sizes')->first()->get()[0],
            'bot_form' => AllStyleForm::where('id', 1)->get()->translate(App::getLocale(), 'lv')[0],
            'top_form' => AllStyle::withTranslation(App::getLocale(), false)
                ->where('id', 1)->get()[0],
            'faqs' => PageFaq::where('page->royal_style', 'royal_style')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),

        ])->render();

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
        $this->vars = Arr::add($this->vars, 'structured_data', $structuredData);
        return $this->renderOutput();
    }

    public function addToBasket(Request $request)
    {

        $data = [];
        $data['is_port_product'] = 1;
        $data['is_gall_with_img'] = 0;
        $data['pid'] = $request->input('pid');

        if ($request->input('is_gall_with_img') == 1) {
            $data['is_gall_with_img'] = 1;
            $img_base = $request->input('image');

            if (strpos($img_base, 'image/png') !== false) {
                $img_n = str_replace('data:image/png;base64,', '', $img_base);
                $ext = '.png';
            } else {
                $img_n = str_replace('data:image/jpeg;base64,', '', $img_base);
                $ext = '.jpeg';
            }

            $img = str_replace(' ', '+', $img_n);
            $img_data = base64_decode($img);
            $rand_name = Str::random(12);
            file_put_contents(public_path() . '/uploads/' . $rand_name . $ext, $img_data);

            $data['activeImage'] = '/uploads/' . $rand_name . $ext;
        } elseif ($data['pid'] != 'undefined' && $data['pid'] != '') {
            if ($request->input('is_uploaded_img')) {
                $data['is_gall_with_img'] = 1;
                $img_base = $request->input('image');
                if ($request->has('is_png_base')) {
                    $ext = '.png';
                } else {
                    $ext = '.jpeg';
                }

                $img = str_replace(' ', '+', $img_base);
                $img_data = base64_decode($img);
                $rand_name = Str::random(12);
                file_put_contents(public_path() . '/uploads/' . $rand_name . $ext, $img_data);
                $data['activeImage'] = '/uploads/' . $rand_name . $ext;
            } else {
                $images = DB::table('gallery_items')->where('id', $data['pid'])->pluck('images')->first();

                if ($images) {
                    $data['activeImage'] = json_decode($images)[0];
                }
            }
        } else {
            // is oil portrait
            $img_base = $request->input('image');

            if (strpos($img_base, 'image/png') !== false) {
                $img = str_replace('data:image/png;base64,', '', $img_base);
                $ext = '.png';
            } else {
                $img = str_replace('data:image/jpeg;base64,', '', $img_base);
                $ext = '.jpeg';
            }

            $img = str_replace(' ', '+', $img);
            $img_data = base64_decode($img);
            $rand_name = Str::random(12);
            file_put_contents(public_path() . '/uploads/' . $rand_name . $ext, $img_data);
            $data['activeImage'] = '/uploads/' . $rand_name . $ext;
            $data['is_oil_portrait'] = 1;
        }

        $files = $request->orig_images;
        if ($files) {
            $data['orig_images'] = [];
            $i = -1;

            foreach ($files as $file) {
                $i++;
                $file_name = Storage::disk('uploads')->put('uploads', $file);
                $data['orig_images'][$i] = URL::to('/') . '/' . $file_name;
            }
        }

        if (request()->hasFile('photo_ex')) {
            $photo_ex = request()->file('photo_ex');
            $photo_ex_file_name = Storage::disk('uploads')->put('uploads', $photo_ex);
            $photo_ex = URL::to('/') . '/' . $photo_ex_file_name;
        } else {
            $photo_ex = '';
        }

        $data['photo_ex'] = $photo_ex;
        $data['name'] = $request->input('name');

        if ((float)$request->input('terms_price')) {
            $data['price'] = (float)$request->input('price') - (float)$request->input('terms_price');
        } else {
            $data['price'] = $request->input('price');
        }

        $data['is_def_product'] = 1;
        $data['count'] = 1;
        $data['pack'] = $request->input('pack');
        $data['terms'] = $request->input('dost_time');
        $data['forma_id'] = $request->input('forma_id');
        $data['size_name'] = $request->input('size'); // 30x40 etc
        $data['users_count'] = $request->input('users_count');
        $data['type'] = $request->input('type');
        $data['holst_id'] = $request->input('holst_id');
        $data['hud_of'] = $request->input('hud_of');
        $data['decor_id'] = $request->input('decor_id');
        $data['ram_id'] = $request->input('ram_id');
        $data['compl_id'] = $request->input('compl_id');
        $data['userComment'] = $request->input('userComment');
        $data['terms'] = $request->input('terms');
        $data['terms_price'] = (float)$request->input('terms_price');
        $data['basket_type'] = '1';

        if ($request->has('is_canvas_collage')) {
            $data['is_canvas_collage'] = 1;
        }

        $basket = $this->basketRepository->normalizeBasket(request()->session()->get('basket'));
        request()->session()->put('basket', $basket);
        request()->session()->push('basket', $data);
        $this->basketRepository->saveBasketToAbandonedCartModel($data, true);
        $response['count'] = 1;
        $response['success'] = __('basket.succ_add');

        return json_encode($response);
    }


    protected function fetchSizes(GalleryItem $entity): object
    {
        $config = [
            'custom_size_prices'=>'canvas',
            'custom_size_prices_form2'=>'paper'
        ];
        $result = (object) [];
        foreach ($config as $field=>$name) {
            $raw = explode(',', $entity->$field) ?? [];
            $country_price_rate  = View::shared('contry_mult', 1);

            //var_dump($raw);
            $temp = [];
            foreach ($raw as $item) {
                list($size,$price) = explode('[', trim($item, "] "));
                $size_arr = explode('x', $size);
                if (count($size_arr) < 2) {
                    $size_arr  = array_pad($size_arr, 2, '');
                }

                $price = $this->get_string_between($item, '[', ']');
                $price_arr = explode('-', $price);
                if (count($price_arr) == 0) {
                    $price_arr = [0];
                }

                $temp[] = (object) [
                    'size' => $size_arr,
                    'price' => (count($price_arr) > 1 ? $price_arr[1] : $price_arr[0]) *  $country_price_rate,
                    'price_old' => (count($price_arr) > 1 ? $price_arr[0] : 0) * $country_price_rate,
                    'full_size' => $item
                ];
            }

            $result->$name = $temp;
        }

        return $result;
    }

    private function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);

        if ($ini == 0) {
            return '';
        }

        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    protected function fetchPersonPrices(GalleryItem $item): array
    {
        $raw = explode(',', $item->custom_users_prices) ?? [];
        $country_price_rate  = View::shared('contry_mult', 1);
        $result = [];
        foreach ($raw as $item) {
            list($count,$price) = explode('[', trim($item, "] "));

            $result[] = (object) [
                'count' => $count,
                'price' => $price * $country_price_rate
            ];
        }
        return $result;
    }

    protected function fetchExamples(GalleryItem $entity): array
    {
        $list = $entity->getMedia('reason_example');
        $result = [];
        foreach ($list as $item) {
            $result[] = (object) [
                'id' => $item->id,
                'title' => $item->getCustomProperty('title_'.app()->getLocale()),
                'src' => $item->getUrl(),
                'category' => $item->getCustomProperty('category')
            ];
        }

        return [
            'list_category'=>explode("\n", $entity->royal_category),
            'list'=>$result
        ];
        //return $result;
    }

    protected function fetchFrames(): object
    {

        $result = (object) [
            'map'=>(object) [
                CanvasRam::TYPE_BAGUETTE => CanvasRam::where('type', CanvasRam::TYPE_BAGUETTE)->with("color")->with("material")->get(),
                CanvasRam::TYPE_PAPER => CanvasRam::where('type', CanvasRam::TYPE_PAPER)->with("color")->with("material")->get(),
                CanvasRam::TYPE_PAPER_PREMIUM => CanvasRam::where('type', CanvasRam::TYPE_PAPER_PREMIUM)->with("color")->with("material")->get(),
            ],
            'list_material'=>CanvasRamsMaterial::get()->translate(App::getLocale(), 'ru'),
            'list_color'=>CanvasRamsColor::get()->translate(App::getLocale(), 'ru')
        ];
        return $result;
    }
}
