<?php

namespace App\Http\Controllers;

use App;
use DB;
use App\Models\ADeliveryTown;
use App\Models\DeliveryPickupAtViarWorkshop;
use App\Models\Page;
use App\Models\Address;
use App\Models\OurTeam;
use App\Models\PageFaq;
use App\Models\AllStyle;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\GalleryType;
use App\Models\GalleryCategory;
use App\Models\GiftCard;
use App\Models\CountryTel;
use App\Models\GalleryItem;
use App\Models\GiftCardNom;
use App\Models\PageFaqDesc;
use Illuminate\Support\Arr;
use App\Models\AllStyleForm;
use Illuminate\Http\Request;
use App\Models\AllStylesPage;
use App\Models\Locale as Loc;
use App\Models\NewhomeWorkEx;
use App\Models\NewhomeService;
use App\Models\PageDelivery;
use App\Models\PagePartnership;
use App\Models\OurWorkingProcess;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Location\Facades\Location;

class PageController extends Controller
{

    public function __construct() {
        $this->template = env('THEME_RESOURCES') . '.index';
    }

    private function get_citys($country)
    {
        $arr = array();
        $arr['country'] = $country;

        $get_pickup_points_list = 'https://go.venipak.lt/ws/get_pickup_points?' . http_build_query($arr);
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $get_pickup_points_list,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
        ]);

        $response = curl_exec($curl);
        $r_data = json_decode($response, true);

        curl_close($curl);

        $response = collect($r_data);

        $response = $response->filter(function ($value, $key) {
            if ($value['type'] == '1') {
                return $value;
            }
        });
        $unique = $response->unique('city');

        return $unique;
    }

    private function get_warehouse($country)
    {
        $arr = array();
        $arr['country'] = $country;

        $get_pickup_points_list = 'https://go.venipak.lt/ws/get_pickup_points?' . http_build_query($arr);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $get_pickup_points_list,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
        ]);

        $response = curl_exec($curl);
        $r_data = json_decode($response, true);
        curl_close($curl);

        $response = collect($r_data);

        $filtered = $response->filter(function ($value, $key) {
            if ($value['type'] == '1') {
                return $value;
            }
        });

        return $filtered;
    }

    public function render_gift()
    {
        return redirect()->route('gift_card_new');
        // $noms = GiftCardNom::all();
        // $data = GiftCard::first()->get()->translate(App::getLocale(), 'ru')[0];
        // return view('gift_card')->with('data', $data)->with('noms', $noms);
    }

    public function render_gift_new()
    {
        $noms = GiftCardNom::all();
        $data = GiftCard::first()->get()->translate(App::getLocale(), 'ru')[0];
        $content = view('gift_card.gift_card')
            ->with([
                'data' => $data,
                'noms' => $noms,
                'faqs' => PageFaq::where('page->gift_card', 'gift_card')
                    ->orderBy('sort', 'asc')
                    ->withTranslation(App::getLocale(), false)
                    ->get(),
            ]);

        $modals = view(env('THEME_RESOURCES') . 'modals.popup_add_to_cart');

        $this->vars = Arr::add($this->vars, 'title', $data->meta_title);
        $this->vars = Arr::add($this->vars, 'modals', $modals);
        $this->vars = Arr::add($this->vars, 'content', $content);
        return $this->renderOutput();
    }

    public function render_about()
    {
        $about = Page::where('url', '/about')->first();
        $page = $about->translate(App::getLocale(), 'ru');

        return view('about')->with([
            'page' => $page,
            'faqs' => PageFaq::where('page->about', 'about')
                ->orderBy('sort', 'asc')
                ->withTranslation(App::getLocale(), false)
                ->get(),
        ]);
    }

    public function hb_render_about()
    {
        $about = Page::where('url', '/about')->first();
        $page = $about->translate(App::getLocale(), 'ru');
        $work_ex = NewhomeWorkEx::withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get();

        $content = view(env('THEME_RESOURCES') . 'pages.about.index')
        ->with([
            'page' => $page,
            'locales' => Loc::all(),
            'work_ex' => $work_ex,
            'services' => NewhomeService::where('is_show', 1)->withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
            'OurWorkingProcess' => OurWorkingProcess::where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru'),
            'OurTeam' => OurTeam::where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru'),
            'faqs' => PageFaq::where('page->about', 'about')
                ->orderBy('sort', 'asc')
                ->withTranslation(App::getLocale(), false)
                ->get(),
        ]);

        $this->vars = Arr::add($this->vars, 'title', $page->meta_title);
        $this->vars = Arr::add($this->vars, 'meta_desc', $page->meta_description);
        $this->vars = Arr::add($this->vars, 'content', $content);
        return $this->renderOutput();
    }

    public function hb_render_contacts()
    {
        $contacts = Page::where('url', '/contacts')->first();
        $page = $contacts->translate(App::getLocale(), 'ru');

        $content = view(env('THEME_RESOURCES') . 'pages.contacts.index')
        ->with([
            'page' => $page,
            'locales' => Loc::all(),
            'address' => Address::where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru'),
            'c_tels' => CountryTel::orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru'),
            'faqs' => PageFaq::where('page->contacts', 'contacts')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),
        ]);

        $this->vars = Arr::add($this->vars, 'title', $page->meta_title);
        $this->vars = Arr::add($this->vars, 'meta_desc', $page->meta_description);
        $this->vars = Arr::add($this->vars, 'content', $content);
        return $this->renderOutput();
    }

    public function render_partnership(Request $request)
    {
        $page = PagePartnership::first()->translate(App::getLocale(), 'ru');

        return view('partnership')->with('page', $page)->with('bodyclass', 'partnership');
    }

    public function render_delivery(Request $request)
    {
        $user = Auth::user();
        if ($user) { $country = $user['country']; }
        else
        {
            if (isset($_SERVER['REQUEST_URI'])) {
                $user_ip=request()->ip();
                if ($user_ip=='127.0.0.1') { $user_ip='128.0.175.22'; }
                if ($position = Location::get($user_ip)) {
                    $country_code = $position->countryCode;
                    if ($country_code == 'FI') {
                        $country_code = 'FIN';
                    }
                    $country = $country_code;
                }
            }
        }
        $citys = $this->get_citys(strtolower($country));
        $warehouses = $this->get_warehouse(strtolower($country));

        $current_locale = app()->getLocale();

        $sale_towns= ADeliveryTown::with(['translations' => function ($query) use ($current_locale) {
            $query->where('locale', $current_locale);
        }])->get();

        $page = PageDelivery::first()->translate(App::getLocale(), 'ru');

        $services = NewhomeService::where('is_show', 1)
            ->withTranslation(App::getLocale(), false)
            ->orderBy('order', 'asc')
            ->get();

        $DeliveryPickupAtViarWorkshop = DeliveryPickupAtViarWorkshop::where("is_show",1)->orderBy('sort', 'asc')->get()->translate(strtolower(App::getLocale()), 'ru');
        $friend_sale_count = DB::table('stocks')->where('id', 1)->pluck('friend_sale')->first();

        $meta_item = Page::where('url', 'delivery')->first();
        $meta_item = $meta_item->translate(App::getLocale(), 'ru');
        $pageUrl = route('delivery_page');
        $structured_data = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $meta_item->meta_title,
                'description' => $meta_item->meta_description,
                'url' => $pageUrl,
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
                        'name' => trans('pages.delivery_title'),
                        'item' => $pageUrl,
                    ],
                ],
            ],
        ];

        return view('delivery')
            ->with('meta_item', $meta_item)
            ->with('page', $page)
            ->with('country', $country)
            ->with('warehouses', $warehouses)
            ->with('bodyclass', 'vz-art')
            ->with('services', $services)
            ->with('sale_towns', $sale_towns)
            ->with('citys', $citys)
            ->with('DeliveryPickupAtViarWorkshop', $DeliveryPickupAtViarWorkshop)
            ->with('faqs', PageFaq::where('page->delivery', 'delivery')
                ->orderBy('sort', 'asc')
                ->withTranslation(App::getLocale(), false)
                ->get())
            ->with( 'c_tels' , CountryTel::orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru'))
            ->with('structured_data', $structured_data);
    }

    public function render_all_styles()
    {
        return view('all_styles')->with([
            'locales' => Loc::all(),
            'top_items' => AllStylesPage::where('is_show', 1)->take(4)->orderBy('order', 'asc')->get()->translate(App::getLocale(), 'ru'),
            'center_items' => AllStylesPage::where('is_show', 1)->take(4)->orderBy(
                'order',
                'asc'
            )->skip(4)->get()->translate(App::getLocale(), 'ru'),
            'bot_items' => AllStylesPage::where('is_show', 1)->take(100)->orderBy(
                'order',
                'asc'
            )->skip(8)->get()->translate(App::getLocale(), 'ru'),
			'top_form' => AllStyle::withTranslation(App::getLocale(), false)->where('id', 1)->get()[0],
            'data' => AllStyle::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'bot_form' => AllStyleForm::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
        ]);
    }

    public function photo_portrait()
    {
        return view('all_styles')->with([
            'locales' => Loc::all(),
            'top_items' => AllStylesPage::where('is_show', 1)->take(4)->where('is_photo', '1')->orderBy('order', 'asc')->get()->translate(App::getLocale(), 'ru'),
            'center_items' => AllStylesPage::where('is_show', 1)->take(4)->where('is_photo', '1')->orderBy('order','asc')->skip(4)->get()->translate(App::getLocale(), 'ru'),
            'bot_items' => AllStylesPage::where('is_show', 1)->take(100)->where('is_photo', '1')->orderBy('order','asc')->skip(8)->get()->translate(App::getLocale(), 'ru'),
            'data' => AllStyle::where('id', 2)->get()->translate(App::getLocale(), 'ru')[0],
			'top_form' => AllStyle::withTranslation(App::getLocale(), false)->where('id', 1)->get()[0],
            'bot_form' => AllStyleForm::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
        ]);


    }

    public function render(Request $request, $url)
    {
        $page = new Page();
        $content = $page->getOne('/' . $url);
        $bodyclass = $url;

        if ($url != 'faq') {
            if ($content == null) {
                return abort(404);
            }

            $page = $content->translate(App::getLocale(), 'ru');
        } else {
            $faqs = PageFaq::where('page->main', 'main')->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');
            $faq_desc = PageFaqDesc::first()->translate(App::getLocale(), 'ru');

            return view('faq')->with('faqs', $faqs)->with('faq_desc', $faq_desc)->with('bodyclass', $bodyclass);
        }

        if (count((array) $content) > 0) {
            if ($url == 'about') {
                $bodyclass = 'about';

                return view('about')->with('uri', $url)->with('page', $page)->with('bodyclass', $bodyclass);
            }

            $all_alternate = Page::where('id', $content->id)->first();
            $langs = ['en', 'ee', 'lt', 'lv', 'pl', 'de', 'ru'];
            $translated_slugs = [];
            $i = -1;

            foreach ($langs as $lang) {
                $i++;
                $cur_slug = $all_alternate->getTranslatedAttribute('url', $lang);
                $translated_slugs[$i]['url'] = $cur_slug;
                $translated_slugs[$i]['lang'] = $lang;
            }

            return view('page', ['page' => $content])->with('uri', $url)->with('page', $page)->with(
                'bodyclass',
                $bodyclass
            )->with('template', $page->template)->with(
                'bodyclass',
                $page->template
            )->with('translated_slugs', $translated_slugs);
        }

        return abort(404);
    }

    public function generate_sitemap_html(Request $request)
    {
        $loc = app()->getLocale();

        if ($loc == 'ru') {
            $loc = '';
        } else {
            $loc = '/' . app()->getLocale();
        }

        $gc = new GalleryCategory();
        // $g_items = GalleryItem::all()->translate(App::getLocale(), 'ru')->toArray();
        // $posts = BlogPost::all()->translate(App::getLocale(), 'ru');
        $g_types = GalleryType::with('items')->get();
        $static_types = [
            ['name' => trans('canvas.canvas'), 'url' => route('canvas')],
            ['name' => trans('collage_new._breads'), 'url' => route('collage')],
            ['name' => trans('pages.modular-generator.bread_title'), 'url' => route('modular-generator')],
            ['name' => trans('google_reviews.reviews'), 'url' => route('review')],
        ];
        $blog_categories = BlogCategory::with('posts')->get();
        $categories = [];
        $subcategories = [];

        foreach (['module', 'photo', 'reproduction'] as $type) {
            $categories[$type] = $gc->getAll($type);
        }

        foreach ($g_types->whereIn('url', ['graphical-portrait', 'Sharj']) as $type) {
            $subcategories[$type->url] = GalleryItem::where('id_type', $type->id)
                ->where('active', 1)
                ->get()
                ->translate(App::getLocale(), 'ru')
                ->toArray();
        }

        return view('sitemap')
            // ->with('g_items', $g_items)
            // ->with('posts', $posts)
            ->with('g_types', $g_types)
            ->with('static_types', $static_types)
            ->with('blog_categories', $blog_categories)
            ->with('categories', $categories)
            ->with('subcategories', $subcategories)
            ->with('loc', $loc);
    }
}
