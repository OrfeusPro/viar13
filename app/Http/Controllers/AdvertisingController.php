<?php

namespace App\Http\Controllers;

use App;
use App\Models\BlogPost;
use App\Models\CountryTel;
use App\Models\GalleryItem;
use App\Models\GalleryType;
use Illuminate\Http\Request;
use App\Models\GalleryCategory;
use App\Models\BlogCategory;
use Illuminate\Support\Facades\DB;

class AdvertisingController extends Controller
{

    public function __construct() {
        $this->template = env('THEME_RESOURCES') . '.index';
    }

    public function generate_sitemap_html(Request $request)
    {
        $loc = app()->getLocale();

        if ($loc == 'ru') {
            $loc = '';
        } else {
            $loc = '/' . app()->getLocale();
        }

        $g_items = GalleryItem::all()->translate(App::getLocale(), 'ru')->toArray();
        $posts = BlogPost::published()->get()->translate(App::getLocale(), 'ru');

        return view('sitemap')
            ->with('g_items', $g_items)
            ->with('posts', $posts)
            ->with('loc', $loc);
    }


	public function sitemap_products(Request $request)
	{
        $gallery = new GalleryType();
        $module =  GalleryItem::getItems($gallery->getType('module')->id, false, false, true);
        $photo =  GalleryItem::getItems($gallery->getType('photo')->id, false, false, true);
        $reproduction =  GalleryItem::getItems($gallery->getType('reproduction')->id, false, false, true);
        //dd($module);

        $contry_mult = DB::table('country_tels')->where('country_code', App::getLocale())->pluck('price_country_mltpr')->first();

        if (!$contry_mult || $contry_mult == null) {
            $contry_mult = 1;
        }


		$xml = view(env('THEME_RESOURCES').'.pages.advertising.sitemap_products')
        ->with("module", $module)
        ->with("photo", $photo)
        ->with("reproduction", $reproduction)
        ->with("contry_mult", $contry_mult)
        ->render();
		return response($xml, 200)->header('Content-Type', 'application/xml');
	}


	public function google(Request $request)
	{
        $gallery = new GalleryType();
        $module =  GalleryItem::getItems($gallery->getType('module')->id, false, false, true);
        $photo =  GalleryItem::getItems($gallery->getType('photo')->id, false, false, true);
        $reproduction =  GalleryItem::getItems($gallery->getType('reproduction')->id, false, false, true);
        //dd($module);

        $contry_mult = DB::table('country_tels')->where('country_code', App::getLocale())->pluck('price_country_mltpr')->first();

        if (!$contry_mult || $contry_mult == null) {
            $contry_mult = 1;
        }


		$xml = view(env('THEME_RESOURCES').'.pages.advertising.google')
        ->with("module", $module)
        ->with("photo", $photo)
        ->with("reproduction", $reproduction)
        ->with("contry_mult", $contry_mult)
        ->render();
		return response($xml, 200)->header('Content-Type', 'application/xml');
	}


	public function google_lv(Request $request)
	{
        $gallery = new GalleryType();
        $module =  GalleryItem::getItems($gallery->getType('module')->id, false, false, true);
        $photo =  GalleryItem::getItems($gallery->getType('photo')->id, false, false, true);
        $reproduction =  GalleryItem::getItems($gallery->getType('reproduction')->id, false, false, true);
        //dd($module);

        $contry_mult = DB::table('country_tels')->where('country_code', 'lv')->pluck('price_country_mltpr')->first();

        if (!$contry_mult || $contry_mult == null) {
            $contry_mult = 1;
        }

		$xml = view(env('THEME_RESOURCES').'.pages.advertising.google')
        ->with("module", $module)
        ->with("photo", $photo)
        ->with("reproduction", $reproduction)
        ->with("contry_mult", $contry_mult)
        ->render();
		return response($xml, 200)->header('Content-Type', 'application/xml');
	}

	public function kurpirkt(Request $request)
	{
        $gallery = new GalleryType();
        $module =  GalleryItem::getItems($gallery->getType('module')->id, false, false, true);
        $photo =  GalleryItem::getItems($gallery->getType('photo')->id, false, false, true);
        $reproduction =  GalleryItem::getItems($gallery->getType('reproduction')->id, false, false, true);
        //dd($module);

        $contry_mult = DB::table('country_tels')->where('country_code', 'lv')->pluck('price_country_mltpr')->first();

        if (!$contry_mult || $contry_mult == null) {
            $contry_mult = 1;
        }

		$xml = view(env('THEME_RESOURCES').'.pages.advertising.kurpirkt')
        ->with("module", $module)
        ->with("photo", $photo)
        ->with("reproduction", $reproduction)
        ->with("contry_mult", $contry_mult)
        ->render();
		return response($xml, 200)->header('Content-Type', 'application/xml');
	}

	public function salidzini(Request $request)
	{
        $gallery = new GalleryType();
        $module =  GalleryItem::getItems($gallery->getType('module')->id, false, false, true);
        $photo =  GalleryItem::getItems($gallery->getType('photo')->id, false, false, true);
        $reproduction =  GalleryItem::getItems($gallery->getType('reproduction')->id, false, false, true);
        //dd($module);

        $contry_mult = DB::table('country_tels')->where('country_code', 'lv')->pluck('price_country_mltpr')->first();

        if (!$contry_mult || $contry_mult == null) {
            $contry_mult = 1;
        }

		$xml = view(env('THEME_RESOURCES').'.pages.advertising.salidzini')
        ->with("module", $module)
        ->with("photo", $photo)
        ->with("reproduction", $reproduction)
        ->with("contry_mult", $contry_mult)
        ->render();
		return response($xml, 200)->header('Content-Type', 'application/xml');
	}

	public function google_lt(Request $request)
	{
        $gallery = new GalleryType();
        $module =  GalleryItem::getItems($gallery->getType('module')->id, false, false, true);
        $photo =  GalleryItem::getItems($gallery->getType('photo')->id, false, false, true);
        $reproduction =  GalleryItem::getItems($gallery->getType('reproduction')->id, false, false, true);
        //dd($module);

        $contry_mult = DB::table('country_tels')->where('country_code', 'lt')->pluck('price_country_mltpr')->first();

        if (!$contry_mult || $contry_mult == null) {
            $contry_mult = 1;
        }

		$xml = view(env('THEME_RESOURCES').'.pages.advertising.google')
        ->with("module", $module)
        ->with("photo", $photo)
        ->with("reproduction", $reproduction)
        ->with("contry_mult", $contry_mult)
        ->render();
		return response($xml, 200)->header('Content-Type', 'application/xml');
	}

	public function google_ee(Request $request)
	{
        $gallery = new GalleryType();
        $module =  GalleryItem::getItems($gallery->getType('module')->id, false, false, true);
        $photo =  GalleryItem::getItems($gallery->getType('photo')->id, false, false, true);
        $reproduction =  GalleryItem::getItems($gallery->getType('reproduction')->id, false, false, true);
        //dd($module);

        $contry_mult = DB::table('country_tels')->where('country_code', 'ee')->pluck('price_country_mltpr')->first();

        if (!$contry_mult || $contry_mult == null) {
            $contry_mult = 1;
        }

		$xml = view(env('THEME_RESOURCES').'.pages.advertising.google')
        ->with("module", $module)
        ->with("photo", $photo)
        ->with("reproduction", $reproduction)
        ->with("contry_mult", $contry_mult)
        ->render();
		return response($xml, 200)->header('Content-Type', 'application/xml');
	}

	public function translate_item(Request $request)
	{
        $gallery = new GalleryType();
        $items =  GalleryItem::where('active', '=', '1')
        ->where('id_type', $gallery->getType('reproduction')->id)
        ->orWhere('id_type', $gallery->getType('module')->id)
        ->orWhere('id_type', $gallery->getType('photo')->id)
        ->get();

        // $request->lang;
        $this->main_translate($items, 'en');
        $this->main_translate($items, 'lv');
        $this->main_translate($items, 'pl');
        $this->main_translate($items, 'ee');
        $this->main_translate($items, 'de');
        $this->main_translate($items, 'lt');

        echo "<br>Переведено - <b>Ok</b>";
	}


	public function set_meta(Request $request)
	{
        $gallery = new GalleryType();
        // $reproduction =  GalleryItem::where('active', '=', '1')
        // ->where('id_type', $gallery->getType('reproduction')->id)
        // ->get();
        // $gc = GalleryCategory::where("is_painter", 1)->get();
        // $posts = BlogPost::all();
        // $categories = BlogCategory::all();
        $gallery_items = GalleryItem::where('active', '=', '1')
            ->whereNotNull('id_type')
            ->get();

        // foreach($gc as $c) {
        //     $reproduction = GalleryItem::where('active', '=', '1')
        //         ->where('id_type', $gallery->getType('reproduction')->id)
        //         ->whereHas('cats', function ($q) use ($c) {
        //             $q->where('gallery_category_id', $c->id);
        //         })
        //         ->get();

        //     $price_from = $reproduction->min('price_from');

        //     $set['en']['meta_title'][0] = $c->getTranslatedAttribute('name', 'en').' Art Reproductions – Buy from '.$price_from.'€ | ViarCanvas';
        //     $set['en']['meta_desc'][0] = $c->getTranslatedAttribute('name', 'en').' art reproductions on canvas starting at '.$price_from.'€. High-quality printing, various sizes. Order from ViarCanvas with fast delivery! Choose from popular masterpieces and shop at ViarCanvas.';

        //     $set['ru']['meta_title'][0] = 'Репродукции картин '.$c->getTranslatedAttribute('name', 'ru').' – купить от '.$price_from.'€ | ViarCanvas';
        //     $set['ru']['meta_desc'][0] = 'Репродукции картин '.$c->getTranslatedAttribute('name', 'ru').' на холсте по цене от '.$price_from.'€. Высокое качество печати, разные размеры. Закажите в ViarCanvas с быстрой доставкой! Выбирайте среди популярных произведений и заказывайте в ViarCanvas.';

        //     $set['lv']['meta_title'][0] = $c->getTranslatedAttribute('name', 'lv').' gleznu reprodukcijas – iegādājieties no '.$price_from.'€ | ViarCanvas';
        //     $set['lv']['meta_desc'][0] = $c->getTranslatedAttribute('name', 'lv').' gleznu reprodukcijas uz kanvas no '.$price_from.'€. Augstas kvalitātes druka, dažādi izmēri. Pasūtiet ViarCanvas ar ātru piegādi! Izvēlieties no populāriem darbiem un pasūtiet ViarCanvas.';

        //     $set['ee']['meta_title'][0] = $c->getTranslatedAttribute('name', 'ee').' kunstireproduktsioonid – alates '.$price_from.'€ | ViarCanvas';
        //     $set['ee']['meta_desc'][0] = $c->getTranslatedAttribute('name', 'ee').' lõuendile trükitud kunstireproduktsioonid alates '.$price_from.'€. Kvaliteetne trükk, erinevad suurused. Telli ViarCanvasist kiire kohaletoimetamisega! Vali tuntud teoste hulgast ja telli ViarCanvasist.';

        //     $set['lt']['meta_title'][0] = $c->getTranslatedAttribute('name', 'lt').' paveikslų reprodukcijos – nuo '.$price_from.'€ | ViarCanvas';
        //     $set['lt']['meta_desc'][0] = $c->getTranslatedAttribute('name', 'lt').' paveikslų reprodukcijos ant drobės nuo '.$price_from.'€. Aukštos kokybės spauda, įvairūs dydžiai. Užsisakykite iš ViarCanvas su greitu pristatymu! Rinkitės iš populiarių kūrinių ir užsisakykite ViarCanvas.';

        //     $set['de']['meta_title'][0] = $c->getTranslatedAttribute('name', 'de').' Kunstreproduktionen – ab '.$price_from.'€ kaufen | ViarCanvas';
        //     $set['de']['meta_desc'][0] = $c->getTranslatedAttribute('name', 'de').' Kunstreproduktionen auf Leinwand ab '.$price_from.'€. Hochwertiger Druck, verschiedene Größen. Jetzt bei ViarCanvas bestellen mit schneller Lieferung! Wählen Sie aus beliebten Werken und bestellen Sie bei ViarCanvas.';

        //     $set['pl']['meta_title'][0] = 'Reprodukcje obrazów '.$c->getTranslatedAttribute('name', 'pl').' – kup od '.$price_from.'€ | ViarCanvas';
        //     $set['pl']['meta_desc'][0] = 'Reprodukcje obrazów '.$c->getTranslatedAttribute('name', 'pl').' na płótnie już od '.$price_from.'€. Wysoka jakość druku, różne rozmiary. Zamów w ViarCanvas z szybką dostawą! Wybierz spośród popularnych dzieł i zamów w ViarCanvas.';

        //     $this->set_meta_for_one_item($c, 'lt', $set);
        //     $this->set_meta_for_one_item($c, 'de', $set);
        //     $this->set_meta_for_one_item($c, 'ee', $set);
        //     $this->set_meta_for_one_item($c, 'ru', $set);
        //     $this->set_meta_for_one_item($c, 'pl', $set);
        //     $this->set_meta_for_one_item($c, 'lv', $set);
        //     $this->set_meta_for_one_item($c, 'en', $set);
        // }

        // foreach ($posts as $post) {
        //     $set['en']['meta_title'][0] = $post->getTranslatedAttribute('title', 'en').' – tips, ideas, and inspiration | ViarCanvas';
        //     $set['en']['meta_desc'][0] = $post->getTranslatedAttribute('title', 'en').' – Learn everything on ViarCanvas. In the article '.$post->getTranslatedAttribute('title', 'en').', we share helpful tips, ideas, and examples for your interior. Read it on ViarCanvas and find inspiration for your space!';

        //     $set['ru']['meta_title'][0] = $post->getTranslatedAttribute('title', 'ru').' – советы, идеи и вдохновение | ViarCanvas';
        //     $set['ru']['meta_desc'][0] = $post->getTranslatedAttribute('title', 'ru').' – Узнайте всё на ViarCanvas. В статье '.$post->getTranslatedAttribute('title', 'ru').' мы делимся полезными советами, идеями и примерами для вашего интерьера. Читайте на ViarCanvas и находите вдохновение для оформления пространства!';

        //     $set['lv']['meta_title'][0] = $post->getTranslatedAttribute('title', 'lv').' – padomi, idejas un iedvesma | ViarCanvas';
        //     $set['lv']['meta_desc'][0] = $post->getTranslatedAttribute('title', 'lv').' – Uzziniet visu ViarCanvas vietnē. Rakstā '.$post->getTranslatedAttribute('title', 'lv').' mēs dalāmies ar noderīgiem padomiem, idejām un piemēriem jūsu interjeram. Lasiet ViarCanvas un smelieties iedvesmu telpas iekārtošanai!';

        //     $set['ee']['meta_title'][0] = $post->getTranslatedAttribute('title', 'ee').' – nõuanded, ideed ja inspiratsioon | ViarCanvas';
        //     $set['ee']['meta_desc'][0] = $post->getTranslatedAttribute('title', 'ee').' – Saa teada kõik ViarCanvasist. Artiklis '.$post->getTranslatedAttribute('title', 'ee').' jagame kasulikke nõuandeid, ideid ja näiteid sinu interjööri jaoks. Loe ViarCanvasist ja leia inspiratsiooni ruumi kujundamiseks!';

        //     $set['lt']['meta_title'][0] = $post->getTranslatedAttribute('title', 'lt').' – patarimai, idėjos ir įkvėpimas | ViarCanvas';
        //     $set['lt']['meta_desc'][0] = $post->getTranslatedAttribute('title', 'lt').' – Sužinokite viską ViarCanvas svetainėje. Straipsnyje '.$post->getTranslatedAttribute('title', 'lt').' dalinamės naudingais patarimais, idėjomis ir pavyzdžiais jūsų interjerui. Skaitykite ViarCanvas ir raskite įkvėpimo savo erdvei!';

        //     $set['de']['meta_title'][0] = $post->getTranslatedAttribute('title', 'de').' – Tipps, Ideen und Inspiration | ViarCanvas';
        //     $set['de']['meta_desc'][0] = $post->getTranslatedAttribute('title', 'de').' – Erfahren Sie alles bei ViarCanvas. Im Artikel '.$post->getTranslatedAttribute('title', 'de').' teilen wir hilfreiche Tipps, Ideen und Beispiele für Ihre Einrichtung. Lesen Sie bei ViarCanvas und lassen Sie sich inspirieren!';

        //     $set['pl']['meta_title'][0] = $post->getTranslatedAttribute('title', 'pl').' – porady, pomysły i inspiracje | ViarCanvas';
        //     $set['pl']['meta_desc'][0] = $post->getTranslatedAttribute('title', 'pl').' – Dowiedz się wszystkiego na ViarCanvas. W artykule '.$post->getTranslatedAttribute('title', 'pl').' dzielimy się przydatnymi poradami, pomysłami i przykładami dla twojego wnętrza. Czytaj na ViarCanvas i znajdź inspirację do aranżacji przestrzeni!';

        //     $this->set_meta_for_one_item($post, 'lt', $set, true);
        //     $this->set_meta_for_one_item($post, 'de', $set, true);
        //     $this->set_meta_for_one_item($post, 'ee', $set, true);
        //     $this->set_meta_for_one_item($post, 'ru', $set, true);
        //     $this->set_meta_for_one_item($post, 'pl', $set, true);
        //     $this->set_meta_for_one_item($post, 'lv', $set, true);
        //     $this->set_meta_for_one_item($post, 'en', $set, true);
        // }

        // foreach ($categories as $category) {
        //     $set['en']['meta_title'][0] = $category->getTranslatedAttribute('title', 'en').' – useful articles, tips, and ideas | ViarCanvas';
        //     $set['en']['meta_desc'][0] = 'Read the best articles from the '.$category->getTranslatedAttribute('title', 'en').' category on ViarCanvas. Learn more about '.$category->getTranslatedAttribute('title', 'en').'. Useful tips, inspiration, and ideas for your interior. Stay updated on trends and find solutions for decorating your space!';

        //     $set['ru']['meta_title'][0] = $category->getTranslatedAttribute('title', 'ru').' – полезные статьи, советы и идеи | ViarCanvas';
        //     $set['ru']['meta_desc'][0] = 'Читайте лучшие статьи из категории '.$category->getTranslatedAttribute('title', 'ru').' на ViarCanvas. Узнайте больше о '.$category->getTranslatedAttribute('title', 'ru').'. Полезные советы, вдохновение и идеи для вашего интерьера. Оставайтесь в курсе трендов и находите решения для оформления пространства!';

        //     $set['lv']['meta_title'][0] = $category->getTranslatedAttribute('title', 'lv').' – noderīgi raksti, padomi un idejas | ViarCanvas';
        //     $set['lv']['meta_desc'][0] = 'Lasiet labākos rakstus no kategorijas '.$category->getTranslatedAttribute('title', 'lv').' ViarCanvas vietnē. Uzziniet vairāk par '.$category->getTranslatedAttribute('title', 'lv').'. Noderīgi padomi, iedvesma un idejas jūsu interjeram. Sekojiet tendencēm un atrodiet risinājumus telpas noformēšanai!';

        //     $set['ee']['meta_title'][0] = $category->getTranslatedAttribute('title', 'ee').' – kasulikud artiklid, nõuanded ja ideed | ViarCanvas';
        //     $set['ee']['meta_desc'][0] = 'Loe parimaid artikleid kategooriast '.$category->getTranslatedAttribute('title', 'ee').' ViarCanvas lehelt. Saa rohkem teada teemal '.$category->getTranslatedAttribute('title', 'ee').'. Kasulikud nõuanded, inspiratsioon ja ideed sinu interjöörile. Ole kursis trendidega ja leia lahendusi ruumide kujundamiseks!';

        //     $set['lt']['meta_title'][0] = $category->getTranslatedAttribute('title', 'lt').' – naudingi straipsniai, patarimai ir idėjos | ViarCanvas';
        //     $set['lt']['meta_desc'][0] = 'Skaitykite geriausius straipsnius iš kategorijos '.$category->getTranslatedAttribute('title', 'lt').' ViarCanvas svetainėje. Sužinokite daugiau apie '.$category->getTranslatedAttribute('title', 'lt').'. Naudingi patarimai, įkvėpimas ir idėjos jūsų interjerui. Sekite tendencijas ir raskite sprendimus savo erdvės dekoravimui!';

        //     $set['de']['meta_title'][0] = $category->getTranslatedAttribute('title', 'de').' – nützliche Artikel, Tipps und Ideen | ViarCanvas';
        //     $set['de']['meta_desc'][0] = 'Lesen Sie die besten Artikel aus der Kategorie '.$category->getTranslatedAttribute('title', 'de').' bei ViarCanvas. Erfahren Sie mehr über '.$category->getTranslatedAttribute('title', 'de').'. Nützliche Tipps, Inspiration und Ideen für Ihre Inneneinrichtung. Bleiben Sie über Trends informiert und finden Sie Lösungen für Ihre Raumgestaltung!';

        //     $set['pl']['meta_title'][0] = $category->getTranslatedAttribute('title', 'pl').' – przydatne artykuły, porady i pomysły | ViarCanvas';
        //     $set['pl']['meta_desc'][0] = 'Przeczytaj najlepsze artykuły z kategorii '.$category->getTranslatedAttribute('title', 'pl').' na ViarCanvas. Dowiedz się więcej o '.$category->getTranslatedAttribute('title', 'pl').'. Przydatne porady, inspiracje i pomysły do twojego wnętrza. Bądź na bieżąco z trendami i znajdź rozwiązania do aranżacji swojej przestrzeni!';

        //     $this->set_meta_for_one_item($category, 'lt', $set, true);
        //     $this->set_meta_for_one_item($category, 'de', $set, true);
        //     $this->set_meta_for_one_item($category, 'ee', $set, true);
        //     $this->set_meta_for_one_item($category, 'ru', $set, true);
        //     $this->set_meta_for_one_item($category, 'pl', $set, true);
        //     $this->set_meta_for_one_item($category, 'lv', $set, true);
        //     $this->set_meta_for_one_item($category, 'en', $set, true);
        // }

        foreach ($gallery_items as $gi) {
            $gi_name_en = !empty($gi->getTranslatedAttribute('shortname', 'en')) ? $gi->getTranslatedAttribute('shortname', 'en') : $gi->getTranslatedAttribute('name', 'en');
            $gi_name_ru = !empty($gi->getTranslatedAttribute('shortname', 'ru')) ? $gi->getTranslatedAttribute('shortname', 'ru') : $gi->getTranslatedAttribute('name', 'ru');
            $gi_name_lv = !empty($gi->getTranslatedAttribute('shortname', 'lv')) ? $gi->getTranslatedAttribute('shortname', 'lv') : $gi->getTranslatedAttribute('name', 'lv');
            $gi_name_lt = !empty($gi->getTranslatedAttribute('shortname', 'lt')) ? $gi->getTranslatedAttribute('shortname', 'lt') : $gi->getTranslatedAttribute('name', 'lt');
            $gi_name_ee = !empty($gi->getTranslatedAttribute('shortname', 'ee')) ? $gi->getTranslatedAttribute('shortname', 'ee') : $gi->getTranslatedAttribute('name', 'ee');
            $gi_name_de = !empty($gi->getTranslatedAttribute('shortname', 'de')) ? $gi->getTranslatedAttribute('shortname', 'de') : $gi->getTranslatedAttribute('name', 'de');
            $gi_name_pl = !empty($gi->getTranslatedAttribute('shortname', 'pl')) ? $gi->getTranslatedAttribute('shortname', 'pl') : $gi->getTranslatedAttribute('name', 'pl');

            $name_en = trim('Canvas '.mb_strtolower($gi_name_en));
            $name_ru = trim('Картина '.mb_strtolower($gi_name_ru));
            $name_lv = trim('Foto Glezna '.mb_strtolower($gi_name_lv));
            $name_lt = trim('Paveikslas '.mb_strtolower($gi_name_lt));
            $name_ee = trim('Pilt '.mb_strtolower($gi_name_ee));
            $name_de = trim('Malerei Bilder '.mb_strtolower($gi_name_de));
            $name_pl = trim('Obrazy '.mb_strtolower($gi_name_pl));

            $set['en']['meta_title'][0] = $name_en.' – buy for '.$gi->price_from.'€ - ViarCanvas '.$gi->id;
            $set['en']['meta_desc'][0] = $name_en.' on canvas for '.$gi->price_from.'€. High quality, various sizes. Order in the online store ViarCanvas with fast delivery! '.$name_en.' '.$gi->id.' order online with shipping!';

            $set['ru']['meta_title'][0] = $name_ru.' – купить за '.$gi->price_from.'€ - ViarCanvas '.$gi->id;
            $set['ru']['meta_desc'][0] = $name_ru.' на холсте по цене '.$gi->price_from.'€. Высокое качество, различные размеры. Закажите в онлайн магазине ViarCanvas с быстрой доставкой! '.$name_ru.' '.$gi->id.' заказывайте онлайн с доставкой!';

            $set['lv']['meta_title'][0] = $name_lv.' – pirkt par '.$gi->price_from.'€ - ViarCanvas '.$gi->id;
            $set['lv']['meta_desc'][0] = $name_lv.' uz audekla par cenu '.$gi->price_from.'€. Augsta kvalitāte, dažādi izmēri. Pasūtiet ViarCanvas interneta veikalā ar ātru piegādi! '.$name_lv.' '.$gi->id.' pasūtiet tiešsaistē ar piegādi!';

            $set['lt']['meta_title'][0] = $name_lt.' – pirkti už '.$gi->price_from.'€ - ViarCanvas '.$gi->id;
            $set['lt']['meta_desc'][0] = $name_lt.' ant drobės už '.$gi->price_from.'€. Aukšta kokybė, įvairūs dydžiai. Užsisakykite internetinėje parduotuvėje ViarCanvas su greitu pristatymu! '.$name_lt.' '.$gi->id.' užsakykite internetu su pristatymu!';

            $set['ee']['meta_title'][0] = $name_ee.' – osta hinnaga '.$gi->price_from.'€ - ViarCanvas '.$gi->id;
            $set['ee']['meta_desc'][0] = $name_ee.' lõuendil hinnaga '.$gi->price_from.'€. Kõrge kvaliteet, erinevad suurused. Telli veebipoest ViarCanvas kiire kohaletoimetamisega! '.$name_ee.' '.$gi->id.' telli internetist koos tarnega!';

            $set['de']['meta_title'][0] = $name_de.' – kaufen für '.$gi->price_from.'€ - ViarCanvas '.$gi->id;
            $set['de']['meta_desc'][0] = $name_de.' auf Leinwand zum Preis von '.$gi->price_from.'€. Hohe Qualität, verschiedene Größen. Bestellen Sie im Online-Shop ViarCanvas mit schneller Lieferung! '.$name_de.' '.$gi->id.' jetzt online mit Versand bestellen!';

            $set['pl']['meta_title'][0] = $name_pl.' – kup za '.$gi->price_from.'€ - ViarCanvas '.$gi->id;
            $set['pl']['meta_desc'][0] = $name_pl.' na płótnie w cenie '.$gi->price_from.'€. Wysoka jakość, różne rozmiary. Zamów w sklepie internetowym ViarCanvas z szybką dostawą! '.$name_pl.' '.$gi->id.' zamów online z dostawą!';

            $this->set_meta_for_one_item($gi, 'lt', $set, true);
            $this->set_meta_for_one_item($gi, 'de', $set, true);
            $this->set_meta_for_one_item($gi, 'ee', $set, true);
            $this->set_meta_for_one_item($gi, 'ru', $set, true);
            $this->set_meta_for_one_item($gi, 'pl', $set, true);
            $this->set_meta_for_one_item($gi, 'lv', $set, true);
            $this->set_meta_for_one_item($gi, 'en', $set, true);
        }

        // $set['en']['meta_title'][0] = "Canvas Print";
        // $set['en']['meta_title'][1] = "Reproduction";
        // $set['en']['meta_desc'][0] = "High-quality canvas prints and reproduction canvases. Elevate your decor with stunning paintings on the wall. Enjoy fast delivery.";
        // $set['en']['meta_desc'][1] = "High-quality canvas prints and reproduction canvases. Paintings. Elevate your decor with stunning paintings on the wall. Enjoy fast delivery.";
        // $set['en']['info'][0] = 'Canvas prints have become a popular choice for art enthusiasts seeking to adorn their living or working spaces with exquisite pieces of art. Whether you\'re looking for oil painting reproductions of renowned masterpieces or replica paintings that capture the essence of original works, canvas prints offer a versatile and affordable solution. These high-quality reproductions allow you to enjoy the beauty of art without the premium price tag. By displaying these paintings on the wall, you can transform any room into a captivating art gallery. Enhance your surroundings with the timeless charm of oil painting reproductions and let your walls tell a story of creativity and sophistication. <br><br> <a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/stocks">Discounts and Promotions</a> <br> <a class="hb_info_link" target="_blank" href="https://viarcanvas.com/page/contacts">Our contacts</a> <br> <a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/page/delivery">Delivery and production time</a> <br><br> Our paintings are created on cotton canvas with a wooden stretcher, ensuring durability and quality. If you wish, you can install decorative frame to complete the look. We guarantee reliable and fast delivery, as well as high quality of each work.';
        // $set['en']['info'][1] = 'Discover exquisite paintings and artist reproductions that capture the essence of fine art. Our canvas prints exemplify unparalleled quality, meticulously crafted to adorn your space with elegance and sophistication. Whether you seek timeless classics or contemporary masterpieces, our collection offers a diverse range of styles to suit every taste. Elevate your decor with our premium-quality canvas prints and experience the beauty of art in your home or office.<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/stocks">Discounts and Promotions</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/page/contacts">Our contacts</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/page/delivery">Delivery and production time</a><br><br>Our paintings are created on cotton canvas with a wooden stretcher, ensuring durability and quality. If you wish, you can install decorative frame to complete the look. We guarantee reliable and fast delivery, as well as high quality of each work.';
        // $set['ru']['meta_title'][0] = "Картина";
        // $set['ru']['meta_title'][1] = "Репродукция Картины";
        // $set['ru']['meta_desc'][0] = "Картины на заказ в нашем магазине. Широкий выбор качественных картина на холсте для уюта и стиля вашего интерьера. Закажите картины по вашему вкусу";
        // $set['ru']['meta_desc'][1] = "Изготовление качественных репродукций картины на заказ. Широкий выбор картин различных стилей и эпох. Персонализированные картины на заказ по вашим предпочтениям.";
        // $set['ru']['info'][0] = 'При выборе картины для вашего интерьера обратите внимание на качество и оригинальность! Наши фото на холсте - это не просто изображения, а настоящие произведения искусства. Каждая картина на стену из нашей коллекции призвана подчеркнуть вашу индивидуальность и создать неповторимую атмосферу в помещении. Интерьерные картины, выполненные на высококачественном холсте, будут радовать вас своим видом на протяжении многих лет. Мы предлагаем широкий выбор работ различных стилей и направлений, чтобы каждый мог найти именно то, что ищет. Приобретайте картины у нас и создайте уют и гармонию в своем доме!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/stocks">Скидки и  Акциии</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/page/contacts">Наши контакты</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/page/delivery">Доставка и сроки изготовления</a><br><br>Наши картины создаются на хлопковом холсте с деревянным подрамником, обеспечивая долговечность и качество. При желании вы можете установить декоративную раму для завершения образа. Мы гарантируем надежную и быструю доставку, а также высокое качество каждой работы.';
        // $set['ru']['info'][1] = 'Репродукции произведения искусства: заказать уникальные картины для вашего интерьера можно у нас в мастерской. Хотите добавить нотку элегантности в свой дом? Закажите качественные репродукции и оригинальные картины для интерьера. Наша быстрая доставка и изготовление позволят вам быстро получить искусство у себя дома. Мы обеспечиваем защиту лаком, добавление художественных мазков и уникальный выбор картин. Подчеркните красоту вашего пространства с нашей коллекцией!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/stocks">Скидки и  Акциии</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/page/contacts">Наши контакты</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/page/delivery">Доставка и сроки изготовления</a><br><br>Наши картины создаются на хлопковом холсте с деревянным подрамником, обеспечивая долговечность и качество. При желании вы можете установить декоративную раму для завершения образа. Мы гарантируем надежную и быструю доставку, а также высокое качество каждой работы.';
        // $set['lv']['meta_title'][0] = "Foto Gleznas";
        // $set['lv']['meta_title'][1] = "Gleznu Reprodukcijas";
        // $set['lv']['meta_desc'][0] = "Foto gleznas - lielisks veids, kā padarīt savas sienas izteiksmīgas un personiskas. Pasūtiet gleznas uz kanvas, lai radītu unikālu mākslas darbu savā mājā vai birojā.";
        // $set['lv']['meta_desc'][1] = "Uzlabo savu interjeru ar mūsu foto gleznām un kanvas reprodukcijām. Iegādājies gleznas un piešķir mājai unikālu un māksliniecisku pieskārienu. Augsta kvalitāte un ātra piegāde";
        // $set['lv']['info'][0] = 'Modernas Kanvas un Augstas Kvalitātes Foto Gleznas.<br>Mūsu uzņēmums piedāvā modernas kanvas un augstas kvalitātes foto gleznas, lai jūsu telpas kļūtu izteiksmīgākas un pievilcīgākas.<br>Izcili pakalpojumi:<br>Modernas kanvas: Izvēlieties modernu dizainu, kas atbilst jūsu gaumei.<br>Kvalitatīvas foto gleznas: Augstas kvalitātes gleznas, kas iederēsies jebkurā interjerā.<br>Sienas gleznas: Pasūtiet unikālas sienas gleznas, lai radītu vizuālu piesitienu jūsu telpām.<br>Nodrošiniet savai telpai jaunu dzīvību ar mūsu modernajām kanvas un foto gleznām. <br>Pasūtiet tagad, lai piešķirtu savām sienām jaunu un stilīgu izskatu!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/stocks">Atlaides un akcijas</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/page/contacts">Mūsu kontakti</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/page/delivery">Piegādes un izgatavošanas laiks</a><br><br>Mūsu gleznas ir veidotas uz kokvilnas audekla ar koka rāmi, nodrošinot izturību un kvalitāti. Ja vēlaties, varat instalēt dekoratīvs rāmis, lai pabeigtu izskatu. Garantējam uzticamu un ātru piegāde, kā arī katra darba augsta kvalitāte.';
        // $set['lv']['info'][1] = 'Mūsu veikalā atradīsi plašu foto gleznu un gleznu reprodukciju klāstu. Sienas gleznas dažādos izmēros un stilos, pieejamas kanvas materiālā. Katrs darbs rūpīgi izgatavots, garantējot augstu kvalitāti un estētisko baudu. Izvēlies savu favorītu un iegādājies to, baudot ātro piegādi. Mūsu gleznas papildinās tavu mājokli ar māksliniecisko vērtību un personīgumu.<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/stocks">Atlaides un akcijas</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/page/contacts">Mūsu kontakti</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/page/delivery">Piegādes un izgatavošanas laiks</a><br><br>Mūsu gleznas ir veidotas uz kokvilnas audekla ar koka rāmi, nodrošinot izturību un kvalitāti. Ja vēlaties, varat instalēt dekoratīvs rāmis, lai pabeigtu izskatu. Garantējam uzticamu un ātru piegāde, kā arī katra darba augsta kvalitāte.';
        // $set['ee']['meta_title'][0] = "Fotolõuendid";
        // $set['ee']['meta_title'][1] = "Maalide Tellimine";
        // $set['ee']['meta_desc'][0] = "Fotolõuendid - parim viis muuta oma seinad kauniks ja isikupäraseks. Tellige maalid lõuendile, et luua unikaalne kunstiteos oma koju või kontorisse.";
        // $set['ee']['meta_desc'][1] = "Avasta meie lai valik maali reproduktsioone ning telli oma lemmikmaalid. Pakume kvaliteetset printimist lõuendile ja professionaalset reprodutseerimist.";
        // $set['ee']['info'][0] = 'Kõrge kvaliteediga maalid lõuendil lisavad teie interjöörile ainulaadset stiili ja elegantsi. Meie pilt lõuendile kollektsioon pakub mitmekesiseid valikuid, mis sobivad ideaalselt igasse ruumi. Tellige maalid, mis peegeldavad teie isikupära ja loovad hubase atmosfääri teie kodus või kontoris. Fotolõuendid seinale on suurepärane viis muuta ruum elavamaks ja huvitavamaks. Tutvuge meie valikuga ja leidke just teile sobivad kunstiteosed, mis rõõmustavad teid igapäevaselt.<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/ee/stocks">Allahindlused ja pakkumised</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/ee/page/contacts">Meie kontaktid</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/ee/page/delivery">Tarne- ja tootmisaeg</a><br><br>Meie maalid on loodud puuvillasele lõuendile puidust kanderaamiga, tagades vastupidavuse ja kvaliteedi. Soovi korral saate installida dekoratiivne raam välimuse täiendamiseks. Garanteerime töökindluse ja kiire tarne, samuti iga töö kõrge kvaliteet.';
        // $set['ee']['info'][1] = 'Maali reproduktsioon Soovite oma kodu või kontori sisustust värskendada? Pakume kvaliteetseid maali reproduktsioone, mis on täiuslikuks lahenduseks. Võimalus maale tellida ja need kvaliteetselt lõuendile printida annab teile võimaluse luua unikaalseid kunstiteoseid oma seinale. Olgu see siis klassikaline maal, kaasaegne kunstiteos või isikupärane pilt lõuendile, meie teenused aitavad teil oma ruumi kaunistada ja isikupärastada. Valige meie laiast valikust sobiv maal ja saage sellest unikaalne reproduktsioon, mis rõõmustab teie silma igapäevaselt. Võtke ühendust ja looge oma ruumis kunstiline atmosfäär tänu meie kvaliteetsetele teenustele.<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/ee/stocks">Allahindlused ja pakkumised</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/ee/page/contacts">Meie kontaktid</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/ee/page/delivery">Tarne- ja tootmisaeg</a><br><br>Meie maalid on loodud puuvillasele lõuendile puidust kanderaamiga, tagades vastupidavuse ja kvaliteedi. Soovi korral saate installida dekoratiivne raam välimuse täiendamiseks. Garanteerime töökindluse ja kiire tarne, samuti iga töö kõrge kvaliteet.';
        // $set['lt']['meta_title'][0] = "Fotodrobės";
        // $set['lt']['meta_title'][1] = "Paveikslų Reprodukcijos";
        // $set['lt']['meta_desc'][0] = "Aukštos kokybės paveikslai ir fotodrobės jūsų interjerui. Tapyba, kuri atneš grožio jūsų namams. Įsigykite unikalius meno kūrinius dabar!";
        // $set['lt']['meta_desc'][1] = "Paveikslai tapyba. Mūsų parduotuvėje įsigysite įvairių paveikslų reprodukcijų ir foto drobių, puikiai tiksiančių jūsų interjerui. Raskite drobes paveikslus, kurie pridės jūsų namams unikalumo ir grožio.";
        // $set['lt']['info'][0] = 'Aukštos kokybės tapyba ant drobių - tai puikus pasirinkimas jūsų interjerui. Mūsų paveikslai suteiks jūsų namams unikalumo ir grožio. Įsigykite paveikslus ir fotodrobės, kurie atspindi jūsų stilių ir asmenybę. Mūsų aukštos kokybės darbai praturtins jūsų erdvę ir pridės šilumos bei atmosferos. Atraskite įdomius tapybos kūrinius ir sukūrėjų darbus, kurie bus puiki dovana ar papuošimas jūsų namams.<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lt/stocks">Nuolaidos ir akcijos</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lt/page/contacts">Mūsų kontaktai</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lt/page/delivery">Pristatymo ir gamybos laikas</a><br><br>Mūsų paveikslai sukurti ant medvilninės drobės su mediniu rėmu, užtikrina ilgaamžiškumą ir kokybę. Jei norite, galite įdiegti dekoratyvinis rėmas, papildantis išvaizdą. Garantuojame patikimai ir greitai pristatymas, taip pat aukšta kiekvieno darbo kokybė.';
        // $set['lt']['info'][1] = 'Mūsų parduotuvėje rasite platų foto drobių ir paveikslų pasirinkimą, kurie puikiai tiks jūsų namų ar biuro interjerui. Taip pat galite užsakyti paveikslus pagal individualius pageidavimus, kad jie atitiktų jūsų skonį ir poreikius. Foto drobės yra puiki galimybė papuošti jūsų sienas ir suteikti erdvei jaukumo bei elegancijos. Mūsų drobes paveikslai ir reprodukcijos yra aukštos kokybės ir pridės unikalumo jūsų namams. Apsilankykite mūsų parduotuvėje ir atraskite puikių paveikslų ir foto drobių pasaulį!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lt/stocks">Nuolaidos ir akcijos</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lt/page/contacts">Mūsų kontaktai</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lt/page/delivery">Pristatymo ir gamybos laikas</a><br><br>Mūsų paveikslai sukurti ant medvilninės drobės su mediniu rėmu, užtikrina ilgaamžiškumą ir kokybę. Jei norite, galite įdiegti dekoratyvinis rėmas, papildantis išvaizdą. Garantuojame patikimai ir greitai pristatymas, taip pat aukšta kiekvieno darbo kokybė.';
        // $set['de']['meta_title'][0] = "Fotoleinwände";
        // $set['de']['meta_title'][1] = "Leinwand Malerei";
        // $set['de']['meta_desc'][0] = "Hochwertige Leinwandbilder & Fotoleinwände. Entdecken Sie unseren Leinwanddruck-Service für individuelle Wandgestaltung. Bestellen Sie jetzt online!";
        // $set['de']['meta_desc'][1] = "Entdecken Sie hochwertige Leinwand Malerei und Ölgemälde mit einer großen Auswahl an Reproduktionen. Bestellen Sie Ihren Druck auf Leinwand oder Fotoleinwand und verschönern Sie Ihr Zuhause mit einzigartiger Kunst.";
        // $set['de']['info'][0] = 'Leinwandbilder. Hochwertige Fotoleinwände mit schneller Lieferung! Möchten Sie ein einzigartiges Kunstwerk für Ihr Zuhause? Unsere Fotoleinwände sind die perfekte Lösung! Dank schneller Lieferung können Sie bald schon einzigartige Bilder genießen. Wir legen großen Wert auf hohe Qualität, was Langlebigkeit und ein exzellentes Aussehen garantiert. Entdecken Sie unsere Sammlung von Leinwandbildern und schaffen Sie eine besondere Atmosphäre in Ihrem Zuhause!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/de/stocks">Rabatte und Sonderangebote</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/de/page/contacts">Unsere Kontakte</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/de/page/delivery">Lieferung Produktionszeit</a><br><br>Unsere Gemälde werden auf Baumwollleinwand mit Holzrahmen erstellt. Gewährleistung von Haltbarkeit und Qualität. Wenn Sie möchten, können Sie installieren dekorativer Rahmen zur Vervollständigung des Looks. Wir garantieren zuverlässig und schnell Lieferung sowie hohe Qualität jeder Arbeit.';
        // $set['de']['info'][1] = 'Auf unserer Website finden Sie eine große Auswahl an Reproduktionen von Leinwand Malerei und Ölgemälde. Wir sind spezialisiert auf den Druck auf Leinwand und die Herstellung von hochwertigen Fotoleinwand-Kunstwerken. Jedes Leinwanddruck wird von unseren Künstlern mit großer Sorgfalt und Liebe zum Detail gefertigt. Lassen Sie Kunst Ihr Zuhause schmücken und eine gemütliche Atmosphäre schaffen mit unseren einzigartigen Werken.<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/de/stocks">Rabatte und Sonderangebote</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/de/page/contacts">Unsere Kontakte</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/de/page/delivery">Lieferung Produktionszeit</a><br><br>Unsere Gemälde werden auf Baumwollleinwand mit Holzrahmen erstellt. Gewährleistung von Haltbarkeit und Qualität. Wenn Sie möchten, können Sie installieren dekorativer Rahmen zur Vervollständigung des Looks. Wir garantieren zuverlässig und schnell Lieferung sowie hohe Qualität jeder Arbeit.';
        // $set['pl']['meta_title'][0] = "Zdjęcia na płótnie";
        // $set['pl']['meta_title'][1] = "Reprodukcje obrazów";
        // $set['pl']['meta_desc'][0] = "Odkryj wyjątkowe zdjęcia na płótnie i stwórz niepowtarzalny klimat w swoim wnętrzu dzięki fotoobrazom canvas. Daj się zainspirować naszymi obrazami na płótnie!";
        // $set['pl']['meta_desc'][1] = "Reprodukcje obrazów to doskonały sposób na wprowadzenie sztuki do swojego domu. Zamówienie obrazu na płótnie lub zdjęcia na płótnie może stworzyć wyjątkową atmosferę w Twoim wnętrzu.";
        // $set['pl']['info'][0] = 'Fotoobrazy na Płótnie wysokiej jakości dostępne natychmiast! Chcesz otrzymać niepowtarzalny kawałek sztuki do swojego wnętrza? Nasze fotoobrazy na płótnie to idealne rozwiązanie! Dzięki szybkiej wysyłce już wkrótce możesz cieszyć się unikalnymi obrazami. Nasza firma dba o wysoką jakość wykonania, co gwarantuje trwałość i doskonały wygląd. Odkryj naszą kolekcję obrazów na płótnie i stwórz wyjątkową atmosferę w swoim domu!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/pl/stocks">Rabaty i promocje</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/pl/page/contacts">Nasze kontakty</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/pl/page/delivery">Czas dostawy i produkcji</a><br><br>Nasze obrazy powstają na płótnie bawełnianym w drewnianej ramie, gwarantując trwałość i jakość. Jeśli chcesz, możesz zainstalować ozdobna ramka dopełniająca całości. Gwarantujemy niezawodność i szybkość dostawę, a także wysoką jakość każdego dzieła.';
        // $set['pl']['info'][1] = 'W dzisiejszych czasach coraz popularniejsze stają się reprodukcje obrazów, które pozwalają cieszyć się sztuką w naszych domach. Obraz na płótnie to doskonały sposób na dodanie charakteru i wyjątkowości do wnętrza. Dzięki zdjęciom na płótnie możemy przenieść ulubione fotografie na trwałą i elegancką formę. Posiadanie obrazów canvas w domu daje niepowtarzalny klimat i wyjątkowy design. Natomiast obrazy olejne są symbolem tradycji i piękna, dodając elegancji i klasy każdemu pomieszczeniu. Dzięki nim możemy stworzyć unikalną atmosferę, która odzwierciedla nasz gust i styl.<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/pl/stocks">Rabaty i promocje</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/pl/page/contacts">Nasze kontakty</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/pl/page/delivery">Czas dostawy i produkcji</a><br><br>Nasze obrazy powstają na płótnie bawełnianym w drewnianej ramie, gwarantując trwałość i jakość. Jeśli chcesz, możesz zainstalować ozdobna ramka dopełniająca całości. Gwarantujemy niezawodność i szybkość dostawę, a także wysoką jakość każdego dzieła.';

        //     $this->set_meta_item($reproduction, 'lt', $set);
        //     $this->set_meta_item($reproduction, 'de', $set);
        //     $this->set_meta_item($reproduction, 'ee', $set);
        //     $this->set_meta_item($reproduction, 'ru', $set);
        //     $this->set_meta_item($reproduction, 'pl', $set);
        //     $this->set_meta_item($reproduction, 'lv', $set);
        //     $this->set_meta_item($reproduction, 'en', $set);

        // $module =  GalleryItem::where('active', '=', '1')
        // ->where('id_type', $gallery->getType('module')->id)
        // ->get();

        // $set['en']['meta_title'][0] = "Canvas Print";
        // $set['en']['meta_title'][1] = "Canvas Print";
        // $set['en']['meta_desc'][0] = "High-quality canvas prints and reproduction canvases. Elevate your decor with stunning paintings on the wall. Enjoy fast delivery.";
        // $set['en']['meta_desc'][1] = "High-quality canvas prints and reproduction canvases. Elevate your decor with stunning paintings on the wall. Enjoy fast delivery.";
        // $set['en']['info'][0] = 'Canvas prints have become a popular choice for art enthusiasts seeking to adorn their living or working spaces with exquisite pieces of art. Whether you\'re looking for oil painting reproductions of renowned masterpieces or replica paintings that capture the essence of original works, canvas prints offer a versatile and affordable solution. These high-quality reproductions allow you to enjoy the beauty of art without the premium price tag. By displaying these paintings on the wall, you can transform any room into a captivating art gallery. Enhance your surroundings with the timeless charm of oil painting reproductions and let your walls tell a story of creativity and sophistication. <br><br> <a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/stocks">Discounts and Promotions</a> <br> <a class="hb_info_link" target="_blank" href="https://viarcanvas.com/page/contacts">Our contacts</a> <br> <a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/page/delivery">Delivery and production time</a> <br><br> Our paintings are created on cotton canvas with a wooden stretcher, ensuring durability and quality. If you wish, you can install decorative frame to complete the look. We guarantee reliable and fast delivery, as well as high quality of each work.';
        // $set['en']['info'][1] = 'Canvas prints have become a popular choice for art enthusiasts seeking to adorn their living or working spaces with exquisite pieces of art. Whether you\'re looking for oil painting reproductions of renowned masterpieces or replica paintings that capture the essence of original works, canvas prints offer a versatile and affordable solution. These high-quality reproductions allow you to enjoy the beauty of art without the premium price tag. By displaying these paintings on the wall, you can transform any room into a captivating art gallery. Enhance your surroundings with the timeless charm of oil painting reproductions and let your walls tell a story of creativity and sophistication. <br><br> <a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/stocks">Discounts and Promotions</a> <br> <a class="hb_info_link" target="_blank" href="https://viarcanvas.com/page/contacts">Our contacts</a> <br> <a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/page/delivery">Delivery and production time</a> <br><br> Our paintings are created on cotton canvas with a wooden stretcher, ensuring durability and quality. If you wish, you can install decorative frame to complete the look. We guarantee reliable and fast delivery, as well as high quality of each work.';
        // $set['ru']['meta_title'][0] = "Картина";
        // $set['ru']['meta_title'][1] = "Картина";
        // $set['ru']['meta_desc'][0] = "Картины на заказ в нашем магазине. Широкий выбор качественных картина на холсте для уюта и стиля вашего интерьера. Закажите картины по вашему вкусу";
        // $set['ru']['meta_desc'][1] = "Картины на заказ в нашем магазине. Широкий выбор качественных картина на холсте для уюта и стиля вашего интерьера. Закажите картины по вашему вкусу";
        // $set['ru']['info'][0] = 'При выборе картины для вашего интерьера обратите внимание на качество и оригинальность! Наши фото на холсте - это не просто изображения, а настоящие произведения искусства. Каждая картина на стену из нашей коллекции призвана подчеркнуть вашу индивидуальность и создать неповторимую атмосферу в помещении. Интерьерные картины, выполненные на высококачественном холсте, будут радовать вас своим видом на протяжении многих лет. Мы предлагаем широкий выбор работ различных стилей и направлений, чтобы каждый мог найти именно то, что ищет. Приобретайте картины у нас и создайте уют и гармонию в своем доме!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/stocks">Скидки и  Акциии</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/page/contacts">Наши контакты</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/page/delivery">Доставка и сроки изготовления</a><br><br>Наши картины создаются на хлопковом холсте с деревянным подрамником, обеспечивая долговечность и качество. При желании вы можете установить декоративную раму для завершения образа. Мы гарантируем надежную и быструю доставку, а также высокое качество каждой работы.';
        // $set['ru']['info'][1] = 'При выборе картины для вашего интерьера обратите внимание на качество и оригинальность! Наши фото на холсте - это не просто изображения, а настоящие произведения искусства. Каждая картина на стену из нашей коллекции призвана подчеркнуть вашу индивидуальность и создать неповторимую атмосферу в помещении. Интерьерные картины, выполненные на высококачественном холсте, будут радовать вас своим видом на протяжении многих лет. Мы предлагаем широкий выбор работ различных стилей и направлений, чтобы каждый мог найти именно то, что ищет. Приобретайте картины у нас и создайте уют и гармонию в своем доме!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/stocks">Скидки и  Акциии</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/page/contacts">Наши контакты</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/page/delivery">Доставка и сроки изготовления</a><br><br>Наши картины создаются на хлопковом холсте с деревянным подрамником, обеспечивая долговечность и качество. При желании вы можете установить декоративную раму для завершения образа. Мы гарантируем надежную и быструю доставку, а также высокое качество каждой работы.';
        // $set['lv']['meta_title'][0] = "Foto Gleznas";
        // $set['lv']['meta_title'][1] = "Foto Gleznas";
        // $set['lv']['meta_desc'][0] = "Foto gleznas - lielisks veids, kā padarīt savas sienas izteiksmīgas un personiskas. Pasūtiet gleznas uz kanvas, lai radītu unikālu mākslas darbu savā mājā vai birojā.";
        // $set['lv']['meta_desc'][1] = "Foto gleznas - lielisks veids, kā padarīt savas sienas izteiksmīgas un personiskas. Pasūtiet gleznas uz kanvas, lai radītu unikālu mākslas darbu savā mājā vai birojā.";
        // $set['lv']['info'][0] = 'Modernas Kanvas un Augstas Kvalitātes Foto Gleznas.<br>Mūsu uzņēmums piedāvā modernas kanvas un augstas kvalitātes foto gleznas, lai jūsu telpas kļūtu izteiksmīgākas un pievilcīgākas.<br>Izcili pakalpojumi:<br>Modernas kanvas: Izvēlieties modernu dizainu, kas atbilst jūsu gaumei.<br>Kvalitatīvas foto gleznas: Augstas kvalitātes gleznas, kas iederēsies jebkurā interjerā.<br>Sienas gleznas: Pasūtiet unikālas sienas gleznas, lai radītu vizuālu piesitienu jūsu telpām.<br>Nodrošiniet savai telpai jaunu dzīvību ar mūsu modernajām kanvas un foto gleznām. <br>Pasūtiet tagad, lai piešķirtu savām sienām jaunu un stilīgu izskatu!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/stocks">Atlaides un akcijas</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/page/contacts">Mūsu kontakti</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/page/delivery">Piegādes un izgatavošanas laiks</a><br><br>Mūsu gleznas ir veidotas uz kokvilnas audekla ar koka rāmi, nodrošinot izturību un kvalitāti. Ja vēlaties, varat instalēt dekoratīvs rāmis, lai pabeigtu izskatu. Garantējam uzticamu un ātru piegāde, kā arī katra darba augsta kvalitāte.';
        // $set['lv']['info'][1] = 'Modernas Kanvas un Augstas Kvalitātes Foto Gleznas.<br>Mūsu uzņēmums piedāvā modernas kanvas un augstas kvalitātes foto gleznas, lai jūsu telpas kļūtu izteiksmīgākas un pievilcīgākas.<br>Izcili pakalpojumi:<br>Modernas kanvas: Izvēlieties modernu dizainu, kas atbilst jūsu gaumei.<br>Kvalitatīvas foto gleznas: Augstas kvalitātes gleznas, kas iederēsies jebkurā interjerā.<br>Sienas gleznas: Pasūtiet unikālas sienas gleznas, lai radītu vizuālu piesitienu jūsu telpām.<br>Nodrošiniet savai telpai jaunu dzīvību ar mūsu modernajām kanvas un foto gleznām. <br>Pasūtiet tagad, lai piešķirtu savām sienām jaunu un stilīgu izskatu!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/stocks">Atlaides un akcijas</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/page/contacts">Mūsu kontakti</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lv/page/delivery">Piegādes un izgatavošanas laiks</a><br><br>Mūsu gleznas ir veidotas uz kokvilnas audekla ar koka rāmi, nodrošinot izturību un kvalitāti. Ja vēlaties, varat instalēt dekoratīvs rāmis, lai pabeigtu izskatu. Garantējam uzticamu un ātru piegāde, kā arī katra darba augsta kvalitāte.';
        // $set['ee']['meta_title'][0] = "Fotolõuendid";
        // $set['ee']['meta_title'][1] = "Fotolõuendid";
        // $set['ee']['meta_desc'][0] = "Fotolõuendid - parim viis muuta oma seinad kauniks ja isikupäraseks. Tellige maalid lõuendile, et luua unikaalne kunstiteos oma koju või kontorisse.";
        // $set['ee']['meta_desc'][1] = "Fotolõuendid - parim viis muuta oma seinad kauniks ja isikupäraseks. Tellige maalid lõuendile, et luua unikaalne kunstiteos oma koju või kontorisse.";
        // $set['ee']['info'][0] = 'Kõrge kvaliteediga maalid lõuendil lisavad teie interjöörile ainulaadset stiili ja elegantsi. Meie pilt lõuendile kollektsioon pakub mitmekesiseid valikuid, mis sobivad ideaalselt igasse ruumi. Tellige maalid, mis peegeldavad teie isikupära ja loovad hubase atmosfääri teie kodus või kontoris. Fotolõuendid seinale on suurepärane viis muuta ruum elavamaks ja huvitavamaks. Tutvuge meie valikuga ja leidke just teile sobivad kunstiteosed, mis rõõmustavad teid igapäevaselt.<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/ee/stocks">Allahindlused ja pakkumised</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/ee/page/contacts">Meie kontaktid</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/ee/page/delivery">Tarne- ja tootmisaeg</a><br><br>Meie maalid on loodud puuvillasele lõuendile puidust kanderaamiga, tagades vastupidavuse ja kvaliteedi. Soovi korral saate installida dekoratiivne raam välimuse täiendamiseks. Garanteerime töökindluse ja kiire tarne, samuti iga töö kõrge kvaliteet.';
        // $set['ee']['info'][1] = 'Kõrge kvaliteediga maalid lõuendil lisavad teie interjöörile ainulaadset stiili ja elegantsi. Meie pilt lõuendile kollektsioon pakub mitmekesiseid valikuid, mis sobivad ideaalselt igasse ruumi. Tellige maalid, mis peegeldavad teie isikupära ja loovad hubase atmosfääri teie kodus või kontoris. Fotolõuendid seinale on suurepärane viis muuta ruum elavamaks ja huvitavamaks. Tutvuge meie valikuga ja leidke just teile sobivad kunstiteosed, mis rõõmustavad teid igapäevaselt.<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/ee/stocks">Allahindlused ja pakkumised</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/ee/page/contacts">Meie kontaktid</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/ee/page/delivery">Tarne- ja tootmisaeg</a><br><br>Meie maalid on loodud puuvillasele lõuendile puidust kanderaamiga, tagades vastupidavuse ja kvaliteedi. Soovi korral saate installida dekoratiivne raam välimuse täiendamiseks. Garanteerime töökindluse ja kiire tarne, samuti iga töö kõrge kvaliteet.';
        // $set['lt']['meta_title'][0] = "Fotodrobės";
        // $set['lt']['meta_title'][1] = "Fotodrobės";
        // $set['lt']['meta_desc'][0] = "Aukštos kokybės paveikslai ir fotodrobės jūsų interjerui. Tapyba, kuri atneš grožio jūsų namams. Įsigykite unikalius meno kūrinius dabar!";
        // $set['lt']['meta_desc'][1] = "Aukštos kokybės paveikslai ir fotodrobės jūsų interjerui. Tapyba, kuri atneš grožio jūsų namams. Įsigykite unikalius meno kūrinius dabar!";
        // $set['lt']['info'][0] = 'Aukštos kokybės tapyba ant drobių - tai puikus pasirinkimas jūsų interjerui. Mūsų paveikslai suteiks jūsų namams unikalumo ir grožio. Įsigykite paveikslus ir fotodrobės, kurie atspindi jūsų stilių ir asmenybę. Mūsų aukštos kokybės darbai praturtins jūsų erdvę ir pridės šilumos bei atmosferos. Atraskite įdomius tapybos kūrinius ir sukūrėjų darbus, kurie bus puiki dovana ar papuošimas jūsų namams.<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lt/stocks">Nuolaidos ir akcijos</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lt/page/contacts">Mūsų kontaktai</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lt/page/delivery">Pristatymo ir gamybos laikas</a><br><br>Mūsų paveikslai sukurti ant medvilninės drobės su mediniu rėmu, užtikrina ilgaamžiškumą ir kokybę. Jei norite, galite įdiegti dekoratyvinis rėmas, papildantis išvaizdą. Garantuojame patikimai ir greitai pristatymas, taip pat aukšta kiekvieno darbo kokybė.';
        // $set['lt']['info'][1] = 'Aukštos kokybės tapyba ant drobių - tai puikus pasirinkimas jūsų interjerui. Mūsų paveikslai suteiks jūsų namams unikalumo ir grožio. Įsigykite paveikslus ir fotodrobės, kurie atspindi jūsų stilių ir asmenybę. Mūsų aukštos kokybės darbai praturtins jūsų erdvę ir pridės šilumos bei atmosferos. Atraskite įdomius tapybos kūrinius ir sukūrėjų darbus, kurie bus puiki dovana ar papuošimas jūsų namams.<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lt/stocks">Nuolaidos ir akcijos</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lt/page/contacts">Mūsų kontaktai</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/lt/page/delivery">Pristatymo ir gamybos laikas</a><br><br>Mūsų paveikslai sukurti ant medvilninės drobės su mediniu rėmu, užtikrina ilgaamžiškumą ir kokybę. Jei norite, galite įdiegti dekoratyvinis rėmas, papildantis išvaizdą. Garantuojame patikimai ir greitai pristatymas, taip pat aukšta kiekvieno darbo kokybė.';
        // $set['de']['meta_title'][0] = "Fotoleinwände";
        // $set['de']['meta_title'][1] = "Fotoleinwände";
        // $set['de']['meta_desc'][0] = "Hochwertige Leinwandbilder & Fotoleinwände. Entdecken Sie unseren Leinwanddruck-Service für individuelle Wandgestaltung. Bestellen Sie jetzt online!";
        // $set['de']['meta_desc'][1] = "Hochwertige Leinwandbilder & Fotoleinwände. Entdecken Sie unseren Leinwanddruck-Service für individuelle Wandgestaltung. Bestellen Sie jetzt online!";
        // $set['de']['info'][0] = 'Leinwandbilder. Hochwertige Fotoleinwände mit schneller Lieferung! Möchten Sie ein einzigartiges Kunstwerk für Ihr Zuhause? Unsere Fotoleinwände sind die perfekte Lösung! Dank schneller Lieferung können Sie bald schon einzigartige Bilder genießen. Wir legen großen Wert auf hohe Qualität, was Langlebigkeit und ein exzellentes Aussehen garantiert. Entdecken Sie unsere Sammlung von Leinwandbildern und schaffen Sie eine besondere Atmosphäre in Ihrem Zuhause!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/de/stocks">Rabatte und Sonderangebote</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/de/page/contacts">Unsere Kontakte</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/de/page/delivery">Lieferung Produktionszeit</a><br><br>Unsere Gemälde werden auf Baumwollleinwand mit Holzrahmen erstellt. Gewährleistung von Haltbarkeit und Qualität. Wenn Sie möchten, können Sie installieren dekorativer Rahmen zur Vervollständigung des Looks. Wir garantieren zuverlässig und schnell Lieferung sowie hohe Qualität jeder Arbeit.';
        // $set['de']['info'][1] = 'Leinwandbilder. Hochwertige Fotoleinwände mit schneller Lieferung! Möchten Sie ein einzigartiges Kunstwerk für Ihr Zuhause? Unsere Fotoleinwände sind die perfekte Lösung! Dank schneller Lieferung können Sie bald schon einzigartige Bilder genießen. Wir legen großen Wert auf hohe Qualität, was Langlebigkeit und ein exzellentes Aussehen garantiert. Entdecken Sie unsere Sammlung von Leinwandbildern und schaffen Sie eine besondere Atmosphäre in Ihrem Zuhause!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/de/stocks">Rabatte und Sonderangebote</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/de/page/contacts">Unsere Kontakte</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/de/page/delivery">Lieferung Produktionszeit</a><br><br>Unsere Gemälde werden auf Baumwollleinwand mit Holzrahmen erstellt. Gewährleistung von Haltbarkeit und Qualität. Wenn Sie möchten, können Sie installieren dekorativer Rahmen zur Vervollständigung des Looks. Wir garantieren zuverlässig und schnell Lieferung sowie hohe Qualität jeder Arbeit.';
        // $set['pl']['meta_title'][0] = "Zdjęcia na płótnie";
        // $set['pl']['meta_title'][1] = "Zdjęcia na płótnie";
        // $set['pl']['meta_desc'][0] = "Odkryj wyjątkowe zdjęcia na płótnie i stwórz niepowtarzalny klimat w swoim wnętrzu dzięki fotoobrazom canvas. Daj się zainspirować naszymi obrazami na płótnie!";
        // $set['pl']['meta_desc'][1] = "Odkryj wyjątkowe zdjęcia na płótnie i stwórz niepowtarzalny klimat w swoim wnętrzu dzięki fotoobrazom canvas. Daj się zainspirować naszymi obrazami na płótnie!";
        // $set['pl']['info'][0] = 'Fotoobrazy na Płótnie wysokiej jakości dostępne natychmiast! Chcesz otrzymać niepowtarzalny kawałek sztuki do swojego wnętrza? Nasze fotoobrazy na płótnie to idealne rozwiązanie! Dzięki szybkiej wysyłce już wkrótce możesz cieszyć się unikalnymi obrazami. Nasza firma dba o wysoką jakość wykonania, co gwarantuje trwałość i doskonały wygląd. Odkryj naszą kolekcję obrazów na płótnie i stwórz wyjątkową atmosferę w swoim domu!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/pl/stocks">Rabaty i promocje</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/pl/page/contacts">Nasze kontakty</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/pl/page/delivery">Czas dostawy i produkcji</a><br><br>Nasze obrazy powstają na płótnie bawełnianym w drewnianej ramie, gwarantując trwałość i jakość. Jeśli chcesz, możesz zainstalować ozdobna ramka dopełniająca całości. Gwarantujemy niezawodność i szybkość dostawę, a także wysoką jakość każdego dzieła.';
        // $set['pl']['info'][1] = 'Fotoobrazy na Płótnie wysokiej jakości dostępne natychmiast! Chcesz otrzymać niepowtarzalny kawałek sztuki do swojego wnętrza? Nasze fotoobrazy na płótnie to idealne rozwiązanie! Dzięki szybkiej wysyłce już wkrótce możesz cieszyć się unikalnymi obrazami. Nasza firma dba o wysoką jakość wykonania, co gwarantuje trwałość i doskonały wygląd. Odkryj naszą kolekcję obrazów na płótnie i stwórz wyjątkową atmosferę w swoim domu!<br><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/pl/stocks">Rabaty i promocje</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/pl/page/contacts">Nasze kontakty</a><br><a class="hb_info_link" target="_blank" href="https://viarcanvas.com/pl/page/delivery">Czas dostawy i produkcji</a><br><br>Nasze obrazy powstają na płótnie bawełnianym w drewnianej ramie, gwarantując trwałość i jakość. Jeśli chcesz, możesz zainstalować ozdobna ramka dopełniająca całości. Gwarantujemy niezawodność i szybkość dostawę, a także wysoką jakość każdego dzieła.';

        // $this->set_meta_item($module, 'en', $set);
        // $this->set_meta_item($module, 'lv', $set);
        // $this->set_meta_item($module, 'pl', $set);
        // $this->set_meta_item($module, 'ee', $set);
        // $this->set_meta_item($module, 'de', $set);
        // $this->set_meta_item($module, 'lt', $set);
        // $this->set_meta_item($module, 'ru', $set);

        // $photo =  GalleryItem::where('active', '=', '1')
        // ->where('id_type', $gallery->getType('photo')->id)
        // ->get();

        // $this->set_meta_item($photo, 'en', $set);
        // $this->set_meta_item($photo, 'lv', $set);
        // $this->set_meta_item($photo, 'pl', $set);
        // $this->set_meta_item($photo, 'ee', $set);
        // $this->set_meta_item($photo, 'de', $set);
        // $this->set_meta_item($photo, 'lt', $set);
        // $this->set_meta_item($photo, 'ru', $set);

        echo "<br>Переведено - <b>Ok</b>";
	}

    function set_meta_for_one_item($item, $lang, $set, $isDesc = false) {
        if($lang == "en") {
            $item_lang = $item;
        } else {
            $item_lang = $item->translate($lang);
        }

        $item_lang->meta_title = $set[$lang]['meta_title'][0];
        if ($isDesc) {
            // $item_lang->meta_desc = $this->trimToLastSentence($set[$lang]['meta_desc'][0]);
            $item_lang->meta_desc = $set[$lang]['meta_desc'][0];
        } else {
            $item_lang->meta_description = $set[$lang]['meta_desc'][0];
        }

        $item_lang->save();
    }

    function trimToLastSentence(string $text, int $maxLength = 255): string
    {
        // Якщо текст уже в межах ліміту — повертаємо як є
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        // Обрізаємо текст до максимальної довжини
        $shortened = mb_substr($text, 0, $maxLength);

        // Шукаємо останню позицію будь-якого кінця речення
        $lastDot = mb_strrpos($shortened, '.');
        $lastExclamation = mb_strrpos($shortened, '!');
        $lastQuestion = mb_strrpos($shortened, '?');

        // Визначаємо найпізнішу з позицій
        $lastPunctuation = max($lastDot, $lastExclamation, $lastQuestion);

        // Якщо знайдено якийсь кінець речення — обрізаємо по ньому
        if ($lastPunctuation !== false) {
            return trim(mb_substr($shortened, 0, $lastPunctuation + 1));
        }

        // Якщо не знайдено — обрізаємо просто до ліміту
        return trim($shortened) . '…';
    }

    function set_meta_item($items, $lang, $set)
    {
        foreach($items as $index => $item)
        {
            //if()
            if($lang == "en") {
                $item_lang = $item;
            } else {
                $item_lang = $item->translate($lang);
            }

            if($item_lang->shortname) {
                $name = str_replace("  ", " ", $item_lang->shortname);
                $name = str_replace("Glezna -", "", $name);
                $name = str_replace("Glezna  -", "", $name);
                $name = str_replace("Canvas  -", "", $name);
                $name = str_replace("Canvas -", "", $name);
                $name = str_replace("Glezna -", "", $name);
                $name = str_replace("Glezna  -", "", $name);
                $name = str_replace("Obraz na płótnie -", "", $name);
                $name = str_replace("Obraz na płótnie  -", "", $name);
                $name = str_replace("Maalimine –", "", $name);
                $name = str_replace("Maalimine  –", "", $name);
                $name = str_replace("Gemälde -", "", $name);
                $name = str_replace("Gemälde  -", "", $name);
                $name = str_replace("Tapyba –", "", $name);
                $name = str_replace("Картина –", "", $name);
                $name = str_replace("Картина -", "", $name);
                $name = str_replace("Картина  -", "", $name);
                $name = str_replace("Moduļu attēls -", "", $name);
                $name = str_replace("Moduļu attēls  -", "", $name);
                $name = str_replace("  ", " ", $name);
                $name = str_replace("  ", " ", $name);
                $name = str_replace("  ", " ", $name);
                $meta_title = $set[$lang]['meta_title'][$index % 2]." ". $name;
                $meta_title = str_replace("  ", " ", $meta_title);
            } else {
                $name = str_replace($set[$lang]['meta_title'][$index % 2], "", $item_lang->name);
                $name = str_replace("  ", " ", $name);
                $name = str_replace("Glezna -", "", $name);
                $name = str_replace("Glezna  -", "", $name);
                $name = str_replace("Canvas  -", "", $name);
                $name = str_replace("Canvas -", "", $name);
                $name = str_replace("Glezna -", "", $name);
                $name = str_replace("Glezna  -", "", $name);
                $name = str_replace("Obraz na płótnie -", "", $name);
                $name = str_replace("Obraz na płótnie  -", "", $name);
                $name = str_replace("Maalimine –", "", $name);
                $name = str_replace("Maalimine  –", "", $name);
                $name = str_replace("Gemälde -", "", $name);
                $name = str_replace("Gemälde  -", "", $name);
                $name = str_replace("Tapyba –", "", $name);
                $name = str_replace("Картина –", "", $name);
                $name = str_replace("Картина -", "", $name);
                $name = str_replace("Картина  -", "", $name);
                $name = str_replace("Moduļu attēls -", "", $name);
                $name = str_replace("Moduļu attēls  -", "", $name);
                $name = str_replace("  ", " ", $name);
                $name = str_replace("  ", " ", $name);
                $name = str_replace("  ", " ", $name);
                $meta_title = $set[$lang]['meta_title'][$index % 2]." ". $name;
                $meta_title = str_replace("  ", " ", $meta_title);
            }

            // if(!$item_lang->seo) {
                $seo = $set[$lang]['info'][$index % 2];
            // } else {
                // $seo = $item_lang->seo;
            // }

            $meta_desc = $set[$lang]['meta_desc'][$index % 2];

            //dd($item,$lang, $set[$lang], $item_lang, $item_lang->meta_title, $meta_title, $item_lang->meta_desc, $meta_desc, $item_lang->seo, $seo);

            $item_lang->meta_title = $meta_title;
            $item_lang->meta_desc = $meta_desc;
            $item_lang->seo = $seo;
            $item_lang->save();
        }
    }

    function main_translate($items, $lang)
    {
        foreach($items as $item)
        {
            $item_ru = $item->translate('ru');

            if($item_ru->name && $lang != "en")
            {
                $save_lang = $item->translate($lang);
                if(!$save_lang->name)
                {
                    $tname = self::translate('ru', $lang, $item_ru->name);
                    if($tname)
                    {
                        $save_lang->name = ucfirst($tname);
                        $save_lang->save();
                        echo $item->id." - ".$save_lang->name."<br>";
                    }
                }

                if($item_ru->description)
                {
                    if(!$save_lang->description)
                    {
                        $description = self::translate('ru', $lang, $item_ru->description);
                        if($description)
                        {
                            $save_lang->description = $description;
                            $save_lang->save();
                            echo $item->id." - ".$save_lang->description."<br>";
                        }
                    }
                }
            }
            else if($item_ru->name && $lang == "en")
            {
                if(!$item->name)
                {
                    $tname = self::translate('ru', $lang, $item_ru->name);
                    if($tname)
                    {
                        $item->name = ucfirst($tname);
                        $item->save();
                        echo $item->id." - ".$item->name."<br>";
                    }
                }

                if($item_ru->description)
                {
                    if(!$item->description)
                    {
                        $description = self::translate('ru', $lang, $item_ru->description);
                        if($description)
                        {
                            $item->description = $description;
                            $item->save();
                            echo $item->id." - ".$item->description."<br>";
                        }
                    }
                }

            }
            else
            {

            }
        }
    }


    public static function translate($source, $target, $text)
    {
        $response = self::requestTranslation($source, $target, $text);
        $translation = self::getSentencesFromJSON($response);
        return $translation;
    }

    protected static function getSentencesFromJSON($json)
    {
        $sentencesArray = json_decode($json, true);
        $sentences = "";
        if (empty($sentencesArray)){
            return '';
        }
        foreach ($sentencesArray["sentences"] as $s) {
            $sentences .= isset($s["trans"]) ? $s["trans"] : '';
        }
        return $sentences;
    }

    protected static function requestTranslation($source, $target, $text)
    {
        $url = "https://translate.google.com/translate_a/single?client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dj=1&hl=es-ES&ie=UTF-8&oe=UTF-8&inputm=2&otf=2&iid=1dd3b944-fa62-4b55-b330-74909a99969e";

        $fields = array(
            'sl' => urlencode($source),
            'tl' => urlencode($target),
            'q' => urlencode($text)
        );

        $fields_string = "";
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }

        rtrim($fields_string, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone TRANSLATE_OPM5_TEST_1');
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }





}
