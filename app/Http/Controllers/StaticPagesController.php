<?php

namespace App\Http\Controllers;

use App\Mail\SaleFacebook;
use App\Models\User;
use DB;
use App;
use Redirect;
use Throwable;
use Carbon\Carbon;
use App\Models\Stock;
use App\Models\Orders;
use App\Models\AllStyle;
use App\Models\PageFaq;
use App\Models\PageSlug;
use App\Models\StylPage;
use App\Models\CanvasNew;
use App\Models\CanvasRam;
use App\Models\CanvasReq;
use App\Models\OilHeader;
use App\Models\CanvasWhat;
use App\Models\OilWhatReq;
use App\Models\ACollageFaq;
use App\Models\ACollageFon;
use App\Models\CanvasPrice;
use App\Models\GalleryPage;
use App\Models\GalleryItem;
use App\Models\OrderAction;
use App\Models\SharjWorkEx;
use Illuminate\Support\Arr;
use App\Models\ACollageHead;
use App\Models\AllStyleForm;
use App\Models\CanvasGlobal;
use App\Models\CanvasHeader;
use App\Models\PortPrevItem;
use App\Models\StylPageInfo;
use Illuminate\Http\Request;
use App\Models\CanvasDelTime;
use App\Models\CollageHeader;
use App\Models\Locale as Loc;
use App\Models\NewhomeWorkEx;
use App\Models\PortraitsHard;
use App\Models\ACollageSlider;
use App\Models\ACollageStiker;
use App\Models\CanvasInterier;
use App\Models\CanvasWorkServ;
use App\Models\HomepageOption;
use App\Models\NewhomeService;
use App\Models\AProductionTime;
use App\Models\ModularPicsHead;
use App\Models\PortBeforeAfter;
use App\Models\StylPagePortObr;
use App\Models\GraphPorStylPage;
use App\Models\NewhomeTopWorkEx;
use App\Models\SharjWorkGroupEx;
use App\Models\ACollageWhyScreen;
use App\Models\FamilyConstructor;
use App\Models\CollageConstructor;
use App\Models\CollagePopularItem;
use App\Models\ACollageOrderScreen;
use App\Models\StylPagePortGoupObr;
use App\Models\ACollageStickerGroup;
use Illuminate\Support\Facades\Auth;
use App\Models\ACollagePopularScreen;
use App\Models\ACollageGeneratorColor;
use App\Models\ACollageAdvantageScreen;
use App\Models\CanvasCompStepsArtsPack;
use App\Models\ModularPicsWhatSizePrice;
use App\Models\WhyAreYouLeavingQuestion;
use App\Http\Controllers\IndexController;
use App\Models\HomepageOption as HomeData;
use App\Repositories\GalleryBoxRepository;
use Stevebauman\Location\Facades\Location;
use App\Repositories\GallerySizeRepository;
use App\Repositories\GalleryHolstRepository;
use App\Models\NewhomeTopSlider as HomeSlides;
use App\Repositories\GalleryDecorationRepository;
use Mail;
use App\Mail\SendUserRegister;


class StaticPagesController extends Controller
{
    private $galleryBoxRepository;
    private $gallerySizeRepository;
    private $galleryHolstRepository;
    private $galleryDecorationRepository;

    public function __construct(
        GalleryBoxRepository $galleryBoxRepository,
        GallerySizeRepository $gallerySizeRepository,
        GalleryHolstRepository $galleryHolstRepository,
        GalleryDecorationRepository $galleryDecorationRepository
    ) {
        $this->galleryBoxRepository = $galleryBoxRepository;
        $this->gallerySizeRepository = $gallerySizeRepository;
        $this->galleryHolstRepository = $galleryHolstRepository;
        $this->galleryDecorationRepository = $galleryDecorationRepository;
        $this->template = env('THEME_RESOURCES') . '.index';
    }




    // graphical portrait + stylization painting

    public function buy_portrait_page($slug)
    {
        return redirect()->route('photo_portrait');

        try {
            $item = GalleryItem::where('slug', $slug)->get()->translate(App::getLocale(), 'ru')[0];
        } catch (Throwable $th) {
            if (isset(GalleryItem::whereTranslation('slug', $slug)->get()->translate(App::getLocale(), 'ru')[0])) {
                $item = GalleryItem::whereTranslation('slug', $slug)->get()->translate(App::getLocale(), 'ru')[0];
            } else {
                return Redirect::route('home');
            }
        }

        try {
            $views = GalleryItem::where('slug', $slug)->pluck('views')->first();
        } catch (Throwable $th) {
            $views = GalleryItem::whereTranslation('slug', $slug)->pluck('views')->first();
        }

        if ($views == null) {
            GalleryItem::where('slug', $slug)->update(['views' => 1]);
        } else {
            $views = (int) $views += 1;

            GalleryItem::where('slug', $slug)->update(['views' => $views]);
        }

        return view('graphical-portrait-buy', [
            'galleryBoxes' => $this->galleryBoxRepository->getAll(),
            'item' => $item,
            'is_oil' => 1,
            'def_inter_rams' => CanvasRam::all()->translate(App::getLocale(), 'ru'),
            'int_globs' => CanvasGlobal::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'gallerySizes' => $this->gallerySizeRepository->getAll(),
            'galleryHolsts' => $this->galleryHolstRepository->getAll(),
            'galleryDecorations' => $this->galleryDecorationRepository->getAll(),
            'canvas_head' => CanvasHeader::first()->get()->translate(App::getLocale(), 'ru')[0],
        ]);
    }

    public function buy_oil_portrait_page()
    {
        $head = OilHeader::all()->translate(App::getLocale(), 'ru')[0];
        $item = [];
        $item['name'] = $head['meta_title'];
        $item['images'] = $head['images'];
        $item['oil_sizes_calc_form1'] = $head['oil_sizes_calc_form1'];
        $item['oil_sizes_calc_form2'] = $head['oil_sizes_calc_form2'];
        $item['oil_sizes_calc_form3'] = $head['oil_sizes_calc_form3'];
        //
        // $item
        $item['custom_users_prices'] = $head['custom_users_prices'];

        return view('graphical-portrait-buy', [
            'is_oil' => 1,
            'is_oil_page' => 1,
            'galleryBoxes' => $this->galleryBoxRepository->getAll(),
            'item' => $item,
            'def_inter_rams' => CanvasRam::all()->translate(App::getLocale(), 'ru'),
            'int_globs' => CanvasGlobal::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'gallerySizes' => $this->gallerySizeRepository->getAll(),
            'galleryHolsts' => $this->galleryHolstRepository->getAll(),
            'galleryDecorations' => $this->galleryDecorationRepository->getAll(),
            'canvas_head' => CanvasHeader::first()->get()->translate(App::getLocale(), 'ru')[0],
        ]);
    }

    public function render_graphic_portrait()
    {
        return redirect()->route('graphic_portrait.new_page', 'portrait-caricature');

        // $data = GraphPorStylPage::where('type', 'graph_port')->first()->get()->translate(App::getLocale(), 'ru')[0];
        // $graph_items = GalleryItem::where('id_type', 5)->get()->translate(App::getLocale(), 'ru');

        // return view('graphic-portrait')->with('data', $data)->with('graph_items', $graph_items);
    }

    public function render_graphic_portrait_page($slug)
    {
        try {
            $item = GalleryItem::where('slug', $slug)->get()->translate(App::getLocale(), 'ru')[0];
        } catch (Throwable $th) {
            try {
                $item = GalleryItem::whereTranslation('slug', $slug)->get()->translate(App::getLocale(), 'ru')[0];
            } catch (Throwable $th) {
                return abort(404);
            }
        }

        $data = GraphPorStylPage::where('type', 'graph_port')->first()->get()->translate(
            App::getLocale(),
            'ru'
        )[0];

        $sizes = DB::table('gallery_sizes')->get()->toArray();
        $gall_type_prive_val = DB::table('gallery_types')->where('id', 5)->pluck('price_var')->first();
        $cur_before_after = DB::table('gallery_items_port_before_after')
            ->where('gallery_item_id', $item->id)->pluck('port_before_after_id')->toArray();
        $before_after = PortBeforeAfter::whereIn('id', $cur_before_after)->get();

        if ($item['is_sharj'] == '1') { // шарж
            $is_granj = 1;
            $gr_top_items = SharjWorkEx::all()->translate(App::getLocale(), 'ru');
            $gr_bot_items = SharjWorkGroupEx::all()->translate(App::getLocale(), 'ru');
        } else {
            $is_granj = 0;
            $gr_top_items = null;
            $gr_bot_items = null;
        }

        $graph_items = GalleryItem::where('id_type', 5)->where('slug', '!=', $slug)->get()->translate(
            App::getLocale(),
            'ru'
        );

        $graph_works = $item->our_works;

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
        $new = DB::table('reviews')->where('active', 1)->where('pid',$item->id)->where('orig_locale', App::getLocale())->get()->map($process);
        $revs = $old->merge($new)->sortByDesc('created_at')->values();

        return view('graphic-portrait-child', [
            'data_id' => 5,
            'data' => $data,
            'sizes' => $sizes,
            'graph_works' => $graph_works,
            'gr_top_items' => $gr_top_items,
            'gr_bot_items' => $gr_bot_items,
            'is_granj' => $is_granj,
            'graph_items' => $graph_items,
            'before_after' => $before_after,
            'serv_qual' => CanvasWorkServ::first()->get()->translate(App::getLocale(), 'ru')[0],
            'canvas_work_serv' => CanvasWorkServ::first()->get()->translate(App::getLocale(), 'ru')[0],
            'gall_type_prive_val' => $gall_type_prive_val,
            'canvas_del_time' => CanvasDelTime::first()->get()->translate(App::getLocale(), 'ru')[0],
            'canvas_what' => CanvasWhat::first()->get()->translate(App::getLocale(), 'ru')[0],
            'reviews' => $revs,
            'canv_bot' => CanvasCompStepsArtsPack::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'collage_header' => CollageHeader::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'item' => $item,
        ]);
    }

    public function render_styl_painting_page($slug)
    {

        return redirect()->route('photo_portrait');

        try {
            $item = GalleryItem::where('slug', $slug)->get()->translate(App::getLocale(), 'ru')[0];
        } catch (Throwable $th) {
            try {
                $item = GalleryItem::whereTranslation('slug', $slug)->get()->translate(App::getLocale(), 'ru')[0];
            } catch (Throwable $th) {
                return abort(404);
            }
        }

        $data = GraphPorStylPage::where('type', 'graph_port')->first()->get()->translate(
            App::getLocale(),
            'ru'
        )[0];

        $sizes = DB::table('gallery_sizes')->get()->toArray();
        $gall_type_prive_val = DB::table('gallery_types')->where('id', 5)->pluck('price_var')->first();
        $cur_before_after = DB::table('gallery_items_port_before_after')
            ->where('gallery_item_id', $item->id)->pluck('port_before_after_id')->toArray();
        $before_after = PortBeforeAfter::whereIn('id', $cur_before_after)->get();

        $graph_items = GalleryItem::where('id_type', 6)->where('slug', '!=', $slug)->get()->translate(
            App::getLocale(),
            'ru'
        );

        $is_obr = 0;
        if ($item->id == 20) { // шарж
            $is_obr = 1;
        }

        $obr_top_items = null;
        $obt_bot_items = null;
        $styl_page_port_obr_items = StylPagePortObr::all()->translate(App::getLocale(), 'ru');
        $styl_page_group_obr_items = StylPagePortGoupObr::all()->translate(App::getLocale(), 'ru');
        $port_info = StylPageInfo::first()->translate(App::getLocale(), 'ru');
        $graph_works = $item->our_works;

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
        $new = DB::table('reviews')->where('active', 1)->where('pid',$item->id)->where('orig_locale', App::getLocale())->get()->map($process);
        $revs = $old->merge($new)->sortByDesc('created_at')->values();

        return view('styl-painting-child', [
            'is_obr' => $is_obr,
            'is_style_painting' => 1,
            'data_id' => 6,
            'graph_works' => $graph_works,
            'port_info' => $port_info,
            'styl_page_port_obr_items' => $styl_page_port_obr_items,
            'styl_page_group_obr_items' => $styl_page_group_obr_items,
            'data' => $data,
            'sizes' => $sizes,
            'gr_top_items' => $obr_top_items,
            'gr_bot_items' => $obt_bot_items,
            'graph_items' => $graph_items,
            'before_after' => $before_after,
            'serv_qual' => CanvasWorkServ::first()->get()->translate(App::getLocale(), 'ru')[0],
            'canvas_work_serv' => CanvasWorkServ::first()->get()->translate(App::getLocale(), 'ru')[0],
            'gall_type_prive_val' => $gall_type_prive_val,
            'canvas_del_time' => CanvasDelTime::first()->get()->translate(App::getLocale(), 'ru')[0],
            'canvas_what' => CanvasWhat::first()->get()->translate(App::getLocale(), 'ru')[0],
            'reviews' => $revs,
            'canv_bot' => CanvasCompStepsArtsPack::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'collage_header' => CollageHeader::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'item' => $item,
        ]);
    }

    public function render_stylization_paintings()
    {
        return redirect()->route('photo_portrait');

        return view('stylization-paintings')->with([
            'data' => StylPage::first()->get()->translate(App::getLocale(), 'ru')[0],
            'items' => GalleryItem::where('id_type', 6)->get()->translate(App::getLocale(), 'ru'),
            'meta' => StylPageInfo::first()->translate(App::getLocale(), 'ru'),
        ]);
    }

    public function render_oil_portrait()
    {
        return redirect()->route('graphic_portrait.new_page', 'kartiny');

        $head = OilHeader::all()->translate(App::getLocale(), 'ru')[0];
        $gall_type_prive_val = DB::table('gallery_types')->where('id', 7)->pluck('price_var')->first();
        $item = GalleryItem::where('id_type', 7)->get()->translate(App::getLocale(), 'ru')[0];
        $data = OilWhatReq::first()->translate(App::getLocale(), 'ru');
        $graph_items = $head['our_works'];
        $ph_items = PortraitsHard::all()->translate(App::getLocale(), 'ru');
        $ps_items = PortPrevItem::all()->translate(App::getLocale(), 'ru');
        $item['sizes_cals'] = $head['sizes_cals'];

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
        $new = DB::table('reviews')->where('active', 1)->where('pid',$item->id)->where('orig_locale', App::getLocale())->get()->map($process);
        $revs = $old->merge($new)->sortByDesc('created_at')->values();

        return view('oil-portrait')->with([
            'head' => $head,
            'item' => $item,
            'is_oil' => 1,
            'graph_items' => $graph_items,
            'ph_items' => $ph_items,
            'ps_items' => $ps_items,
            'sizes' => DB::table('gallery_sizes')->get()->toArray(),
            'data2' => $data,
            'data' => GraphPorStylPage::where('type', 'graph_port')->first()->get()->translate(
                App::getLocale(),
                'ru'
            )[0],
            'canvas_req' => CanvasReq::first()->get()->translate(App::getLocale(), 'ru')[0],
            'serv_qual' => CanvasWorkServ::first()->get()->translate(App::getLocale(), 'ru')[0],
            'canvas_work_serv' => CanvasWorkServ::first()->get()->translate(App::getLocale(), 'ru')[0],
            'gall_type_prive_val' => $gall_type_prive_val,
            'canvas_del_time' => CanvasDelTime::first()->get()->translate(App::getLocale(), 'ru')[0],
            'canvas_what' => CanvasWhat::first()->get()->translate(App::getLocale(), 'ru')[0],
            'reviews' => $revs,
            'canv_bot' => CanvasCompStepsArtsPack::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'collage_header' => CollageHeader::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
        ]);
    }





    public function render_stocks()
    {
        $user_data = null;

        if (Auth::check()) {
            $user_id = Auth::id();
            $user_code = Auth::user()->inv_sale_code;

              $user_data = DB::table('users')
             ->where('id', $user_id)
              ->first();

            if ($user_code == null || $user_code == 'regenerate') {
                $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
                $user_code = mb_substr(str_shuffle($rand_code), 0, 8);
                DB::table('users')->where('id', $user_id)->update([
                    'inv_sale_code' => $user_code,
                ]);
            }
        }

        // sales

        $module_sale = GalleryItem::where('id_type', 2)->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 0)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');

        $foto_sale = GalleryItem::where('id_type', 3)->where('custom_size_prices_sale', '!=', null)->where('is_big_sale', 0)->where('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');

        $repr_sale = GalleryItem::where('id_type', 4)->where(
            'custom_size_prices_sale',
            '!=',
            null
        )->where('is_big_sale', 0)->whereDate(
            'sale_end',
            '>=',
            Carbon::now()
        )->get()->translate(App::getLocale(), 'ru');

        // big salse
        $module_big_sale = GalleryItem::where('id_type', 2)->where(
            'custom_size_prices_sale',
            '!=',
            null
        )->where('is_big_sale', 1)->whereDate(
            'sale_end',
            '>=',
            Carbon::now()
        )->get()->translate(App::getLocale(), 'ru');

        $foto_big_sale = GalleryItem::where('id_type', 3)->where(
            'custom_size_prices_sale',
            '!=',
            null
        )->where('is_big_sale', 1)->where(
            'sale_end',
            '>=',
            Carbon::now()
        )->get()->translate(App::getLocale(), 'ru');

        $repr_big_sale = GalleryItem::where('id_type', 4)->where(
            'custom_size_prices_sale',
            '!=',
            null
        )->where('is_big_sale', 1)->whereDate(
            'sale_end',
            '>=',
            Carbon::now()
        )->get()->translate(App::getLocale(), 'ru');

        $head = Stock::first()->get()->translate(App::getLocale(), 'ru')[0];

        return view('stocks')->with([
            'head' => $head,
            'mod_sale' => $module_sale,
            'foto_sale' => $foto_sale,
            'repr_sale' => $repr_sale,
            'module_big_sale' => $module_big_sale,
            'foto_big_sale' => $foto_big_sale,
            'repr_big_sale' => $repr_big_sale,
            'user_data' => $user_data,
        ]);
    }

    /// TODO: Акция 1 в подарок - проверить или создать купон
    /// TODO: Акция 40_60 - создать купон

    public function create_stock_coupon(Request $request)
{
    $requestData = json_decode($request->getContent(), true);
    $user_id = $requestData['user_id'];
    $coloumn = $requestData['coloumn'];


        $user = DB::table('users')->find($user_id);
        $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
        $user_code = mb_substr(str_shuffle($rand_code), 0, 8);

    if ($coloumn=='is_1free') {
        DB::table('coupons')
            ->where('user_id', $user_id)
            ->where('is_1free', 1)
            ->delete();

        $usr_email= $user->email;
        $data['subject']='viarcanvas.com - 1 free';
        $data['first_name']=$user->first_name;
        $data['promo_code']= $user_code;

        Mail::to($usr_email)->send(new \App\Mail\onefree($data));
    }

    if ($coloumn=='is_40_60') {
        DB::table('coupons')
            ->where('user_id', $user_id)
            ->where('is_40_60', 1)
            ->delete();

        $usr_email= $user->email;
        $data['subject']='viarcanvas.com coupon';
        $data['first_name']=$user->first_name;
        $data['promo_code']= $user_code;

        Mail::to($usr_email)->send(new \App\Mail\sale40_60($data));
    }


        DB::table('coupons')->insert([
            'text' => $user_code,
            $coloumn => 1,
            'is_active' => 1,
            'user_id' => $user_id,
        ]);



   // Mail::to($usr_email)->send(new SaleFacebook($coupon_code,$locale));
     return response()->json(['message' => 'Success']);
}

    public function send_frend_email(Request $request)
{

//    $request->email;
//    $request->promoCode;
    $mymail=Auth::user()->email;
    $myname=Auth::user()->first_name;
    $data['subject']='viarcanvas.com';
    $data['first_name']=$myname;
    $data['promo_code']= $request->promoCode;

    Mail::to($mymail)->send(new \App\Mail\invite_friend_self($data));
    Mail::to($request->email)->send(new \App\Mail\invite_friend($data));
    return response()->json(['message' => 'Success']);
}


    //TODO: Вывод Страницы акций
    public function hb_render_stocks()
    {
        $topmailClass= new \App\Helpers\GalleryTopMail;
        $topmail= $topmailClass->get_topmail_data();
        $images_folder = 'https://viarcanvas.com/storage/';
        $contry_mult=$topmailClass->get_gallery_price();

        if (Auth::check()) {
            $user_id = Auth::id();
            $user_code = Auth::user()->inv_sale_code;

            if ($user_code == null || $user_code == 'regenerate') {
                $rand_code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
                $user_code = mb_substr(str_shuffle($rand_code), 0, 8);
                DB::table('users')->where('id', $user_id)->update([
                    'inv_sale_code' => $user_code,
                ]);
                DB::table('coupons')->insert([
                    [
                        'text' => $user_code,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'value' => 5,
                        'is_active' => 1,
                        'is_multiuse' => 1,
                        'user_id' => $user_id,
                        'is_dates_sale'=> 0,
                    ]
                ]);
            }
            $modals = view(env('THEME_RESOURCES') . 'pages.stocks.modals')   ->with([
                'invite_code' => $user_code,
            ]);
            $this->vars = Arr::add($this->vars, 'modals', $modals);
        }
        else
        { $user_id = null; }

        // sales
        $module_sale = GalleryItem::where('id_type', 2)->where('custom_size_prices_sale', '!=', 'NULL')->where('is_big_sale', 0)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $foto_sale = GalleryItem::where('id_type', 3)->where('custom_size_prices_sale', '!=', 'NULL')->where('is_big_sale', 0)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $repr_sale = GalleryItem::where('id_type', 4)->where('custom_size_prices_sale', '!=','NULL')->where('is_big_sale', 0)->whereDate('sale_end','>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');

//        var_dump($module_sale);
//        var_dump($foto_sale);
//        var_dump($repr_sale);

        // big salse
        $module_big_sale = GalleryItem::where('id_type', 2)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->whereDate('sale_end', '>=',Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $foto_big_sale = GalleryItem::where('id_type', 3)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->where('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');
        $repr_big_sale = GalleryItem::where('id_type', 4)->where('custom_size_prices_sale','!=', null)->where('is_big_sale', 1)->whereDate('sale_end', '>=', Carbon::now())->get()->translate(App::getLocale(), 'ru');

        $page = Stock::first()->get()->translate(App::getLocale(), 'ru')[0];
        $head = $page;

        $gallery = GalleryPage::first()->get()->translate(App::getLocale(), 'ru')[0];

        $content = view(env('THEME_RESOURCES') . 'pages.stocks.stocks')
        ->with([
            'page' => $page,
            'head' => $head,
            'mod_sale' => $module_sale,
            'foto_sale' => $foto_sale,
            'repr_sale' => $repr_sale,
            'module_big_sale' => $module_big_sale,
            'foto_big_sale' => $foto_big_sale,
            'repr_big_sale' => $repr_big_sale,
            'user_id' => $user_id,
            'gallery' => $gallery,
            'images_folder' => $images_folder,
            'top_mail' => $topmail,
            'multiplyer' => $contry_mult,
            'faqs' => PageFaq::where('page->stocks', 'stocks')
                ->orderBy('sort', 'asc')
                ->withTranslation(App::getLocale(), false)
                ->get(),

        ]);

       //  dd($module_sale, $foto_sale, $repr_sale, $module_big_sale, $foto_big_sale, $repr_big_sale);


        $this->vars = Arr::add($this->vars, 'title', $page->meta_title);
        $this->vars = Arr::add($this->vars, 'meta_desc', $page->meta_description);
        $this->vars = Arr::add($this->vars, 'content', $content);


        return $this->renderOutput();
           if (Auth::check()) {
        return $this->renderOutput();
           } else
           {

           return redirect('/account');
           }
    }






    public function render_family()
    {
        return view('family_construtor', [
            'data' => FamilyConstructor::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'mod_head' => ModularPicsHead::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'def_inter_images' => CanvasInterier::all(),
            'def_inter_rams' => CanvasRam::all()->translate(App::getLocale(), 'ru'),
            'galleryBoxes' => $this->galleryBoxRepository->getAll(),
            'gallerySizes' => $this->gallerySizeRepository->getAll(),
            'galleryHolsts' => $this->galleryHolstRepository->getAll(),
            'canvas_head' => CanvasHeader::first()->get()->translate(App::getLocale(), 'ru')[0],
            'canv_bot' => CanvasCompStepsArtsPack::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'int_globs' => CanvasGlobal::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
        ]);
    }

    public function render_collage_constructor()
    {
        return view('collage-constructor', [
            'data' => CollageConstructor::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'canvas_head' => CanvasHeader::first()->get()->translate(App::getLocale(), 'ru')[0],
            'def_inter_rams' => CanvasRam::all()->translate(App::getLocale(), 'ru'),
            'def_inter_images' => CanvasInterier::all(),
            'galleryBoxes' => $this->galleryBoxRepository->getAll(),
            'gallerySizes' => $this->gallerySizeRepository->getAll(),
            'galleryHolsts' => $this->galleryHolstRepository->getAll(),
            'canv_bot' => CanvasCompStepsArtsPack::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            'int_globs' => CanvasGlobal::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
        ]);
    }


    public function why_are_you_leaving_questions(Request $request)
    {
        $quest = WhyAreYouLeavingQuestion::All()->where('id', $request->input('why_id'))->first()->translate('ru', 'ru');
        $phone = $request->input('phone');
        $title = $quest['title'];

        OrderAction::create([
            'user' => "Why_Are_You_Leaving",
            'activity' => "Вопрос: $title<br>\r\n Телефон: $phone",
        ]);

        //dd($quest['title']);
        return redirect()->back();
    }

    public function thanks(IndexController $IndexController)
    {

        $home = HomepageOption::withTranslation(App::getLocale(), false)->select('page_title', 'meta_desc', 'all_styles', 'all_sizes')->first()->get();

        if (isset($_GET['order_id']) && $_GET['order_id']) {
            $order = Orders::getOrderByIdStatic($_GET['order_id']);
            if (isset($order[0])) {
                $order = (array) $order[0];
                $order['delivery'] = json_decode($order['delivery'], 1);
                $order['items'] = json_decode($order['items'], 1);
                $order['price'] = rtrim(rtrim(number_format($order['price'], 2, ',', ' '), '0'), ',') . ' €';
                $order['catid_name'] = $IndexController->get_styles_for_quiz_by_id($order['catid'], App::getLocale());

                if (isset($order['catid_name']['name'])) {
                    $order['catid_name'] = str_replace('<span>', '', $order['catid_name']['name']);
                    $order['catid_name'] = str_replace('</span>', '', $order['catid_name']);
                    $order['catid_name'] = $order['catid_name'];
                }
            } else {
                $order = false;
            }
        } else {
            $order = false;
        }

        if (isset($order['payment_status']) && $order['payment_status']=='payed') {

            Mail::to($order['delivery']['email'])->send(new \App\Mail\Payment_successful($_GET['order_id'], App::getLocale()));

        }

        return view(env("THEME_RESOURCES") . 'thanks')->with(
            [
                'locales' => Loc::all(),
                'order' => $order,
                'cur_loc' => App::getLocale(),
            ]
        );
    }

    public function render_slug_page($slug, GalleryBoxRepository $galleryBoxRepository, IndexController $IndexController)
    {
        $ex_slugs = PageSlug::first()->get()->translate(App::getLocale(), 'ru')[0];
        $langs = ['en', 'ee', 'lt', 'lv', 'pl', 'de', 'ru'];
        $translated_slugs = [];
        $all_alternate = PageSlug::first();

        if ($ex_slugs['canvas_slug'] == $slug) {
            // lang links
            $i = -1;
            foreach ($langs as $lang) {
                $i++;
                $cur_slug = $all_alternate->getTranslatedAttribute('canvas_slug', $lang);
                $translated_slugs[$i]['url'] = $cur_slug;
                $translated_slugs[$i]['usl_pages'] = 1;
                $translated_slugs[$i]['lang'] = $lang;
            }

            return Redirect::route('canvas');
        }

        if ($ex_slugs['collage_slug'] == $slug) {
            // lang links
            $i = -1;
            foreach ($langs as $lang) {
                $i++;
                $cur_slug = $all_alternate->getTranslatedAttribute('collage_slug', $lang);
                $translated_slugs[$i]['url'] = $cur_slug;
                $translated_slugs[$i]['lang'] = $lang;
            }

            $style = $IndexController->get_styles_for_quiz(App::getLocale());


            $collage_head = ACollageHead::get()->translate(App::getLocale(), 'ru')[0];
            $AProductionTime = AProductionTime::where('category', 'collage')->first()->translate(App::getLocale(), 'ru');
            $collage_head['sizes_30x40'] = $this->collage_price_for_country($collage_head['sizes_30x40']);
            $collage_head['sizes_38x38'] = $this->collage_price_for_country($collage_head['sizes_38x38']);
            $collage_head['sizes_40x30'] = $this->collage_price_for_country($collage_head['sizes_40x30']);

            //dd($AProductionTime->standart_text);

            return view('collage')->with(
                [
                    'locales' => Loc::all(),
                    'style' => $style,
                    'AProductionTime' => $AProductionTime,
                    'why_are_you_leaving_questions' => WhyAreYouLeavingQuestion::All()->where('is_show', 1)->translate(App::getLocale(), 'ru'),
                    'current_quiz_style_id' => 2,
                    'home_slides' => ACollageSlider::all(),
                    'item' => CanvasNew::first(),
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

        }

        if ($ex_slugs['modular_slug'] == $slug) {
            // lang links
            $i = -1;
            foreach ($langs as $lang) {
                $i++;
                $cur_slug = $all_alternate->getTranslatedAttribute('modular_slug', $lang);
                $translated_slugs[$i]['url'] = $cur_slug;
                $translated_slugs[$i]['lang'] = $lang;
            }

            $pop_mod_ids = DB::table('gallery_items_mod_head')->pluck('gallery_item_id')->toArray();

            return view('modular-pictures', [
                'usl_pages' => 1,
                'translated_slugs' => $translated_slugs,
                'galleryBoxes' => $this->galleryBoxRepository->getAll(),
                'galleryHolsts' => $this->galleryHolstRepository->getAll(),
                'def_inter_images' => CanvasInterier::all(),
                'int_globs' => CanvasGlobal::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
                'galleryDecorations' => $this->galleryDecorationRepository->getAll(),
                'tab' => CanvasHeader::first()->get()->translate(App::getLocale(), 'ru')[0],
                'mod_head' => ModularPicsHead::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
                'our_works' => ModularPicsHead::where('id', 1)->pluck('our_works')->first(),
                'canvas_del_time' => CanvasDelTime::first()->get()->translate(App::getLocale(), 'ru')[0],
                'what_size' => ModularPicsWhatSizePrice::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
                'canvas_req' => CanvasReq::first()->get()->translate(App::getLocale(), 'ru')[0],
                'serv_qual' => CanvasWorkServ::first()->get()->translate(App::getLocale(), 'ru')[0],
                'def_inter_rams' => CanvasRam::all()->translate(App::getLocale(), 'ru'),
                'pop_mod_items' => GalleryItem::whereIn('id', $pop_mod_ids)->get()->translate(App::getLocale(), 'ru'),
                'canvas_work_serv' => CanvasWorkServ::first()->get()->translate(App::getLocale(), 'ru')[0],
                'del_time' => CanvasDelTime::first()->get()->translate(App::getLocale(), 'ru')[0],
                'canvas_what' => CanvasWhat::first()->get()->translate(App::getLocale(), 'ru')[0],
                'reviews' =>  DB::table('our_works')->where('active', 1)->where('orig_locale', App::getLocale())->get(),
                'canv_bot' => CanvasCompStepsArtsPack::where('id', 1)->get()->translate(App::getLocale(), 'ru')[0],
            ]);
        }

        return abort(404);
    }

    public function collage_price_for_country($srt)
    {
        if (isset($_SERVER['REQUEST_URI']) && !str_contains($_SERVER['REQUEST_URI'], '/admin/')){
            if ($position = Location::get(request()->ip())) {
                $country_code = $position->countryCode;
                if ($country_code == 'FI') {
                    $country_code = 'FIN';
                }
                // LV => ЛАТВИЯ
                // LT => ЛИТВА
                // EE => ЭСТОНИЯ
                // FI => ФИНЛЯНДИЯ
                // DE => ГЕРМАНИЯ
                // PL => ПОЛЬША
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
