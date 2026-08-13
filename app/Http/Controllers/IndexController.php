<?php

namespace App\Http\Controllers;

use DB;
use App;
use App\Models\PageFaq;
use App\Models\AllStyle;
use App\Models\GalleryItem;
use Illuminate\Support\Arr;
use App\Models\ACollageHead;
use App\Models\AllStyleForm;
use App\Models\CanvasHeader;
use App\Models\CreepingLine;
use App\Models\Locale as Loc;
use App\Models\NewhomeWorkEx;
use App\Models\NewhomeService;
use App\Models\NewhomeTopWorkEx;
use App\Models\WhyAreYouLeavingQuestion;
use App\Models\HomepageOption as HomeData;
use App\Models\NewhomeTopSlider as HomeSlides;
use Illuminate\Http\Request;

class IndexController extends Controller
{

    public function __construct()
    {
        $this->template = env('THEME_RESOURCES') . '.index';
    }


    static public function get_styles_for_quiz($lang)
    {
        $style = GalleryItem::select('id', 'name', "custom_size_prices", "custom_size_prices_form2", "custom_users_prices")->where('id_type', 5)->withTranslation($lang, false)->get();
        $CanvasHeader = CanvasHeader::select('id', "c_right_top as name", "sizes_30x40 as custom_size_prices", "sizes_38x38 as custom_size_prices_form2")->withTranslation($lang, false)->get();
        $CanvasHeader[0]->id = 1;
        $CanvasHeader[0]->custom_users_prices = '';
        $ACollageHead = ACollageHead::select('id', 'name', "sizes_30x40 as custom_size_prices", "sizes_38x38 as custom_size_prices_form2")->withTranslation($lang, false)->get();
        $ACollageHead[0]->id = 2;
        $ACollageHead[0]->custom_users_prices = '';

        $style = $style->merge($CanvasHeader);
        $style = $style->merge($ACollageHead);

        return $style;
    }

    public function get_styles_for_quiz_by_id($id, $lang)
    {
        $style = $this->get_styles_for_quiz($lang);
        $style = $style->whereIn('id', $id)->first();
        return $style;
    }

    public function render_new()
    {
        $home = HomeData::withTranslation(App::getLocale(), false)->select('page_title', 'meta_desc', 'all_styles', 'all_sizes')->first()->get();

        $style = $this->get_styles_for_quiz(App::getLocale());
        $work_ex = NewhomeWorkEx::withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get();

        foreach ($work_ex as $work) {
            //$work->catid
            if ($work->catid != 1 && $work->catid != 2 && $work->catid) {
                $work->caturl = GalleryItem::getItemById($work->catid)->first()->meta_url."#generator";
            } else if ($work->catid == 1) {
                $work->caturl = "https://viarcanvas.com/new/canvas#generator";
            } else if ($work->catid == 2) {
                $work->caturl = "https://viarcanvas.com/collage#generator";
            } else {
                $work->caturl = "#";
            }
        }

        return view('index_new')->with(
            [
                'locales' => Loc::all(),
                'why_are_you_leaving_questions' => WhyAreYouLeavingQuestion::All()->where('is_show', 1)->translate(App::getLocale(), 'ru'),
                'home' => $home[0],
                'style' => $style,
                'current_quiz_style_id' => 1,
                'faqs' => PageFaq::where('page->main', 'main')->withTranslation(App::getLocale(), false)->orderBy('sort', 'asc')->get(),
                'revs' => DB::table('our_works')->where('active', 1)->where('orig_locale', App::getLocale())->get(),
                'work_ex' => $work_ex,
                'top_work_ex' => NewhomeTopWorkEx::withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
                'home_slides' => HomeSlides::all(),
                'services' => NewhomeService::where('is_show', 1)->withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
                'top_form' => AllStyle::withTranslation(App::getLocale(), false)->where('id', 1)->get()[0],
                'bot_form' => AllStyleForm::where('id', 1)->get()->translate(App::getLocale(), 'lv')[0],
                'cur_loc' => App::getLocale(),
            ]
        );
    }

    public function hbrender(Request $request)
    {
        // $uri = $request->server('REQUEST_URI'); // например: "/index.php?utm=..."
        // if ($uri && preg_match('#^/(?:[a-z]{2}/)?index\.php(?:\?.*)?$#i', $uri)) {
        //     // Сохраняем query string
        //     $qs = $request->getQueryString();
        //     // Если используете локали, подставьте свой префикс (или LaravelLocalization::localizeURL('/'))
        //     return redirect(($request->routeIs('redirect.indexphp.locale') ? url()->current() : '/').($qs ? "?$qs" : ''), 301);
        // } 

        $home = HomeData::withTranslation(App::getLocale(), false)->select('page_title', 'meta_desc', 'all_styles', 'all_sizes')->first()->get();

        $style = $this->get_styles_for_quiz(App::getLocale());
        $work_ex = NewhomeWorkEx::withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get();

        foreach ($work_ex as $work) {

            if ($work->catid != 1 && $work->catid != 2 && $work->catid) {
                $work->caturl = GalleryItem::getItemById($work->catid)->first()->meta_url."#generator";
            } else if ($work->catid == 1) {
                $work->caturl = "https://viarcanvas.com/new/canvas#generator";
            } else if ($work->catid == 2) {
                $work->caturl = "https://viarcanvas.com/collage#generator";
            } else {
                $work->caturl = "#";
            }
        }

        $creepingLine = CreepingLine::where('page->main', 'main')->where('is_show', 1)->orderBy('sort', 'asc')->get()->translate(App::getLocale(), 'ru');

        $content = view(env('THEME_RESOURCES') . 'pages.index')->with(
            [
                'locales' => Loc::all(),
                'home' => $home[0],
                'creepingLine' => $creepingLine,
                'style' => $style,
                'current_quiz_style_id' => 1,
                'faqs' => PageFaq::where('page->main', 'main')->withTranslation(App::getLocale(), false)->orderBy('sort', 'asc')->get(),
                'revs' => DB::table('our_works')->where('active', 1)->where('orig_locale', App::getLocale())->get(),
                'work_ex' => $work_ex,
                'top_work_ex' => NewhomeTopWorkEx::withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
                'home_slides' => HomeSlides::where('is_show', 1)->orderBy('order', 'asc')->get(),
                'services' => NewhomeService::where('is_show', 1)->withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
                'top_form' => AllStyle::withTranslation(App::getLocale(), false)->where('id', 1)->get()[0],
                'bot_form' => AllStyleForm::where('id', 1)->get()->translate(App::getLocale(), 'lv')[0],
                'cur_loc' => App::getLocale(),
            ]
        );

        $modals = view(env('THEME_RESOURCES') . 'pages.index.modals')
        ->with([
            'style' => $style,
            'current_quiz_style_id' => 1,
            ])
            ->render();

        $this->vars = Arr::add($this->vars, 'creepingLine', $creepingLine);
        $this->vars = Arr::add($this->vars, 'title', $home[0]->getTranslatedAttribute('page_title'));
        $this->vars = Arr::add($this->vars, 'meta_desc', $home[0]->getTranslatedAttribute('meta_desc'));
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);
        $this->vars = Arr::add($this->vars, 'seo', $home[0]->getTranslatedAttribute('seo'));
        $this->vars = Arr::add($this->vars, 'is_cities_on_page', $home[0]->is_cities_on_page);
        $this->vars = Arr::add($this->vars, 'seo_city_title', $home[0]->getTranslatedAttribute('seo_city_title'));
        $this->vars = Arr::add($this->vars, 'seo_city_desc', $home[0]->getTranslatedAttribute('seo_city_desc'));
        return $this->renderOutput();
    }
}
