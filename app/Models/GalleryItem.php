<?php

namespace App\Models;

use App;
use App\Models\Concerns\HasAltSuggestions;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use TCG\Voyager\Models\Translation;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\GalleryItem
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property int|null $id_type
 * @property int|null $id_category
 * @property string $name
 * @property string $description
 * @property string|null $genre
 * @property string|null $style
 * @property string|null $images
 * @property int $active
 * @property bool $allow_client_photo_upload
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $views
 * @property float|null $price_from
 * @property string|null $add_image1
 * @property string|null $add_image_bg
 * @property string|null $shortname
 * @property string|null $is_sharj
 * @property string|null $sale_end
 * @property string|null $port_in_obr
 * @property string|null $custom_size_prices
 * @property string|null $custom_users_prices
 * @property string|null $custom_size_prices_sale
 * @property int|null $is_big_sale
 * @property string|null $sizes_cals
 * @property string|null $add_image2_inner
 * @property string|null $add_image3_inner
 * @property string|null $our_works
 * @property string|null $backgrounds
 * @property string|null $custom_size_prices_form2
 * @property string|null $custom_size_prices_form3
 * @property string|null $etc_style_image
 * @property int|null $is_left_image
 * @property string|null $slug
 * @property string|null $meta_title
 * @property string|null $meta_desc
 * @property string|null $meta_robots
 * @property string|null $short_desc
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GalleryCategory[] $cats
 * @property-read int|null $cats_count
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|GalleryItem beforeAfter()
 * @method static Builder|GalleryItem collage()
 * @method static Builder|GalleryItem curCat()
 * @method static Builder|GalleryItem module()
 * @method static Builder|GalleryItem newModelQuery()
 * @method static Builder|GalleryItem newQuery()
 * @method static Builder|GalleryItem query()
 * @method static Builder|GalleryItem repr()
 * @method static Builder|GalleryItem whereActive($value)
 * @method static Builder|GalleryItem whereAddImage1($value)
 * @method static Builder|GalleryItem whereAddImage2Inner($value)
 * @method static Builder|GalleryItem whereAddImage3Inner($value)
 * @method static Builder|GalleryItem whereAddImageBg($value)
 * @method static Builder|GalleryItem whereCreatedAt($value)
 * @method static Builder|GalleryItem whereCustomSizePrices($value)
 * @method static Builder|GalleryItem whereCustomSizePricesForm2($value)
 * @method static Builder|GalleryItem whereCustomSizePricesForm3($value)
 * @method static Builder|GalleryItem whereCustomSizePricesSale($value)
 * @method static Builder|GalleryItem whereCustomUsersPrices($value)
 * @method static Builder|GalleryItem whereDescription($value)
 * @method static Builder|GalleryItem whereEtcStyleImage($value)
 * @method static Builder|GalleryItem whereGenre($value)
 * @method static Builder|GalleryItem whereId($value)
 * @method static Builder|GalleryItem whereIdCategory($value)
 * @method static Builder|GalleryItem whereIdType($value)
 * @method static Builder|GalleryItem whereImages($value)
 * @method static Builder|GalleryItem whereIsBigSale($value)
 * @method static Builder|GalleryItem whereIsLeftImage($value)
 * @method static Builder|GalleryItem whereIsSharj($value)
 * @method static Builder|GalleryItem whereMetaDesc($value)
 * @method static Builder|GalleryItem whereMetaRobots($value)
 * @method static Builder|GalleryItem whereMetaTitle($value)
 * @method static Builder|GalleryItem whereName($value)
 * @method static Builder|GalleryItem whereOurWorks($value)
 * @method static Builder|GalleryItem wherePortInObr($value)
 * @method static Builder|GalleryItem wherePriceFrom($value)
 * @method static Builder|GalleryItem whereSaleEnd($value)
 * @method static Builder|GalleryItem whereShortDesc($value)
 * @method static Builder|GalleryItem whereShortname($value)
 * @method static Builder|GalleryItem whereSizesCals($value)
 * @method static Builder|GalleryItem whereSlug($value)
 * @method static Builder|GalleryItem whereStyle($value)
 * @method static Builder|GalleryItem whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|GalleryItem whereUpdatedAt($value)
 * @method static Builder|GalleryItem whereViews($value)
 * @method static Builder|GalleryItem withTranslation($locale = null, $fallback = true)
 * @method static Builder|GalleryItem withTranslations($locales = null, $fallback = true)
 */
class GalleryItem extends Model implements HasMedia
{
    use HasAltSuggestions;
    use Translatable;
    use HasSlug;
    use InteractsWithMedia;

    protected $fillable = [

        'id',

        'id_type',

        'short_desc',

        'name',

        'shortname',

        'genre',

        'style',

        'description',

        'is_sharj',

        'is_big_sale',

        'sale_end',

        'views',

        'active',

        'add_image1',

        'add_image_bg',

        'item_text',

        'meta_url'

    ];

    protected $translatable = [

        'name',

        'meta_title',

        'meta_desc',

        'short_desc',

        'meta_robots',

        'shortname',

        'genre',

        'style',

        'description',

        'seo',

        'seo_city_title',

        'seo_city_desc',

        'meta_url',

        'other_work_h2',

        'zakaz_title_h2',

        'third_block_h2',

        'fourth_block_h2'


    ];

    protected $perPage = 10;

    protected $casts = [
        'allow_client_photo_upload' => 'boolean',
    ];

    // Мазки: P0 - нету, P1 - мазки маслом, P2 - полностью маслом.
    // Здесь храним оверрайды по catid для мазков.
    const BRUSHSTROKES_IMG_TAGS = [
        1053 => 'P2'
    ];



    public static function getNameById($id)
    {
        return GalleryItem::where('id', $id)->pluck('name')[0];
    }

    public static function getCatIdByProductId($pid)
    {
        return GalleryItem::where('id', $pid)->pluck('id_type')[0];
    }

    public static function getTermsByPrice($price)
    {
        $cur_price = intval($price);
        $ret_info = StringTranlation::get()->translate(App::getLocale(), 'ru')[0];
        if ($cur_price == 0) {
            return $ret_info['standart_text'];
        } else {
            return $ret_info['express_text'];
        }
    } // voyager per page

    public static function getTermsByPriceLocaled($price, $locale)
    {
        $cur_price = intval($price);

        $ret_info = StringTranlation::get()->translate($locale, 'ru')[0];

        if ($cur_price == 0) {
            return $ret_info['standart_text'];
        } else {
            return $ret_info['express_text'];
        }
    }

    public static function getImageById($id)
    {
        return json_decode(GalleryItem::where('id', $id)->pluck('images')->first())[0];
    }

    public static function getItemSingleUrlById($id)
    {
        $item_type_id = \DB::table('gallery_items')->where('id', $id)->pluck('id_type');

        $type_url = \DB::table('gallery_types')->where('id', $item_type_id)->pluck('url')->first();

        $cat_url = \DB::table('gallery_categories')->where('id_type', $item_type_id)->pluck('url')->first();

        $loc = App::getLocale();

        if ($loc == 'ru') {
            $loc = '';
        }
        $url = url($loc . '/gallery') . '/' . $type_url . '/' . $cat_url . '/item/' . $id;
        $clear_url = str_replace('//item', '/item', $url);
        return $clear_url;
    }

    public static function getRamNameById($id)
    {
        $ram_info = \DB::table('canvas_rams')->where('id', $id)->pluck('name')->first();
        return $ram_info;
    }

    public static function getSizeByItemId($id)
    {
        $size_id = DB::table('gallery_items_ gallery_tag_sizes')->where('gallery_item_id', $id)
            ->pluck('gallery_size_id')->first();

        $size_name = DB::table('gallery_sizes')->where('id', $size_id)->pluck('size')->first();

        return $size_name;
    }

    public static function getSizeIdByItemId($id)
    {
        $size_id = DB::table('gallery_items_ gallery_tag_sizes')->where('gallery_item_id', $id)
            ->pluck('gallery_size_id')->first();
        return $size_id;
    }

    public static function getTransName($id)
    {
        return GalleryItem::where('id', $id)->get()->translate(App::getLocale(), 'ru')->pluck('name')->first();
    }

    public static function getTransJenre($id)
    {
        return GalleryItem::where('id', $id)->get()->translate(App::getLocale(), 'ru')->pluck('genre')->first();
    }

    public static function getTransStyle($id)
    {
        return GalleryItem::where('id', $id)->get()->translate(App::getLocale(), 'ru')->pluck('style')->first();
    }

    public static function getSalePrice($price, $percent)
    {
        return round($price - ($price * ($percent / 100)));
    }

    public function scopeCurCat($query)
    {
        if (isset($_GET['type'])) {
            $_GET['type'] = (int) $_GET['type'];
            return $query->where('id_type', $_GET['type']);
        }
        return $query;
    }

    public function cats()
    {
        return $this->belongsToMany('App\Models\GalleryCategory');
    }

    public function types()
    {
        return $this->belongsTo(GalleryType::class, 'id_type', 'id');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function scopeCollage($query)
    {
        return $query->where('id_type', 3); // Фото картины
    }

    public function scopeModule($query)
    {
        return $query->where('id_type', 2); // Модульные картины
    }

    public function scopeRepr($query)
    {
        return $query->where('id_type', 4); // Репродукции
    }

    public static function getItemById($id)
    {
        return GalleryItem::where('id', $id);
    }

    public function getHolstById($id)
    {
        return (array) DB::table('gallery_holsts')->where('id', $id)->first();
    }

    public function getBoxById($id)
    {
        return (array) DB::table('gallery_boxes')->where('id', $id)->first();
    }

    public function scopeBeforeAfter($query)
    {
        return $query->whereIn('id_type', [5, 6]);
    }

    static public function rep_last_items()
    {
        return GalleryItem::withTranslation(App::getLocale(), false)
        ->where('id_type', 4)
        ->where('active', '=', '1')
        ->orderBy('id',"desc")
        ->limit(8)
        ->get();
    }

    static public function viewed_products($ids)
    {
        return GalleryItem::withTranslation(App::getLocale(), false)
        ->where('active', '=', '1')
        ->whereIn('id', $ids)
        ->limit(10)
        ->get();
    }


    // static public function curent_iteviewed_products($ids)
    // {
    //     $items = [];

	// 	$currentCategory = GalleryCategory::find($id);

	// 	$items = GalleryItem::whereHas('cats', function ($query) use ($currentCategory) {
	// 		$query->where('id', $currentCategory->id);
	// 	})->where('active', '=', '1')->get();

	// 	return $items;



    //     // $items = $items->whereHas('cats', function ($q) use ($category) {
    //     //     if($category) {
    //     //         $q->where('gallery_category_id', $category);
    //     //     }
    //     // });

    //     // return GalleryItem::withTranslation(App::getLocale(), false)
    //     // ->where('active', '=', '1')
    //     // ->whereIn('id', $ids)
    //     // ->limit(10)
    //     // ->get();
    // }





    static public function getItems($type, $category = false, $filtr = false, $all = false, $random = false)
    {
        $items = GalleryItem::where('id_type', $type)->where('active', '=', '1');

        if ($category !== false) {
            $items = $items->whereHas('cats', function ($q) use ($category) {
                    $q->where('gallery_category_id', $category);
            });
        }
        $color = $filtr['color'] ?? null;
        $color = is_scalar($color)
            ? filter_var($color, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;

        if ($color !== false && $color !== null) {
            $color_items_ids = DB::table('gallery_items_ gallery_tag_colors')
                ->where('gallery_tag_color_id', $color)->pluck('gallery_item_id');

            $items = $items->whereIn('id', $color_items_ids);
        }

        if (!empty($filtr['age'])) {
            $items = $items->whereHas('get_age', function ($q) use ($filtr) {
                $q->where('alias', $filtr['age']);
            });
        }

        if (!empty($filtr['style'])) {
            $items = $items->whereHas('get_style', function ($q) use ($filtr) {
                $q->where('alias', $filtr['style']);
            });
        }

        if (!empty($filtr['genre'])) {
            $items = $items->whereHas('get_genre', function ($q) use ($filtr) {
                $q->where('alias', $filtr['genre']);
            });
        }

        if (!empty($filtr['shape'])) {
            $shape_ids = self::filterByShape($items, $filtr);
            $items = $items->whereIn('id', $shape_ids);
        }

        $size = $filtr['size'] ?? null;
        if (is_string($size) && preg_match('/^(\d{1,4})x(\d{1,4})_(over|smaller)$/', $size, $matches)) {
            $size1 = (int) $matches[1];
            $size2 = (int) $matches[2];
            $size_sort = $matches[3];

            if($size_sort === "over")
            {
                $size_ids = DB::table('gallery_sizes')->where('height',">=", $size1)->where('length',">=", $size2)->pluck('id');
            }
            else
            {
                $size_ids = DB::table('gallery_sizes')->where('height',"<=", $size1)->where('length',"<=", $size2)->pluck('id');
            }

           $size_id = DB::table('gallery_items_ gallery_tag_sizes')->whereIn('gallery_size_id', $size_ids)->pluck('gallery_item_id');

            // dd($size1,$size2,$size_sort, $size_id);
            $items = $items->whereIn('id', $size_id);
        }

        if (!empty($filtr['search'])) {
            $locales = App::getLocale();
            if($locales != 'en')
            {
                $ids = Translation::where('table_name', 'gallery_items')
                ->where('column_name', 'name')
                ->where('value', 'like', "%".$filtr['search']."%")
                ->where('locale', $locales)
                ->pluck('foreign_key');

                $items = $items->whereIn('id', $ids);
            }
            else {
                $items = $items->where('name', 'like', "%".$filtr['search']."%");
            }
        }

        if (!empty($filtr['tag'])) {
            $arr_tag = explode(',', $filtr['tag']);

            $tag_room_ids = DB::table('gallery_items_gallery_tag_rooms')
                ->whereIn('gallery_tag_room_id', $arr_tag)->pluck('gallery_item_id');

            if (count($arr_tag) > 0) {
                $items = $items->whereIn('id', $tag_room_ids);
            }
        }

        if (isset($filtr['one']) && !empty($filtr['one'])) {
            $items = $items->where('id', '=', $filtr['one']);
        }


        if (isset($filtr['order']) && $filtr['order'] == "new") {
            $items = $items->orderBy('id',"desc") ;
        }

        if (isset($filtr['order']) && $filtr['order'] == "cheap") {
            $items = $items->orderBy('price_from',"asc") ;
        }

        if (isset($filtr['order']) && $filtr['order'] == "expensive") {
            $items = $items->orderBy('price_from',"desc") ;
        }

        if($all) {
            $items = $items->get();
        }
        else if($random) {
            $items = $items->inRandomOrder()->limit(10)->get();
        }
        else {
            $items = $items->paginate(15);
        }

        foreach ($items as $i => $item) {
            foreach ($item->attributes as $key => $data) {
                if ($key == 'size') {
                    $size_ids = explode(',', $data);

                    $sizes = [];

                    foreach ($size_ids as $id) {
                        $sizes[] = (array) DB::table('gallery_sizes')->where('id', $id)->first();
                    }

                    $items[$i]->size = $sizes;
                }

                if ($key == 'holst') {
                    $holst_ids = explode(',', $data);

                    $holsts = [];

                    foreach ($holst_ids as $id) {
                        $holsts[] = (array) DB::table('gallery_holsts')->where('id', $id)->first();
                    }

                    $items[$i]->holst = $holsts;
                }

                if ($key == 'box') {
                    $box_ids = explode(',', $data);

                    $boxs = [];

                    foreach ($box_ids as $id) {
                        $boxs[] = (array) DB::table('gallery_boxes')->where('id', $id)->first();
                    }

                    $items[$i]->box = $boxs;
                }
            }
        }

        self::calcPrice($items);
        return $items;
    }

    static public function filterByShape($items, $shape) {

        if ($shape['shape'] === 'square') {
            $size_ids = DB::table('gallery_sizes')->whereColumn('height',"=", "length")->pluck('id');
        } elseif ($shape['shape'] === 'landscape') {
            $size_ids = DB::table('gallery_sizes')->whereColumn('height',">", "length")->pluck('id');
        } elseif ($shape['shape'] === 'portrait') {
            $size_ids = DB::table('gallery_sizes')->whereColumn('height',"<", "length")->pluck('id');
        }

        $items = DB::table('gallery_items_ gallery_tag_sizes')->whereIn('gallery_size_id', $size_ids)->pluck('gallery_item_id');

        return $items;
    }

    static public function calcPrice($datas)
    {
        foreach ($datas as $id => $data) {
            $pricesSize = [];

            $pricesHolst = [];

            $pricesBox = [];

            foreach ($data->attributes as $key => $item) {
                if ($key == 'size') {
                    foreach ($item as $k => $array) {
                        foreach ($array as $k2 => $value) {
                            if ($k2 == 'price') {
                                $pricesSize[] = $value;
                            }
                        }
                    }
                }

                if ($key == 'holst') {
                    foreach ($item as $k => $array) {
                        foreach ($array as $k2 => $value) {
                            if ($k2 == 'price') {
                                $pricesHolst[] = $value;
                            }
                        }
                    }
                }

                if ($key == 'box') {
                    foreach ($item as $k => $array) {
                        foreach ($array as $k2 => $value) {
                            if ($k2 == 'price') {
                                $pricesBox[] = $value;
                            }
                        }
                    }
                }
            }

            $datas[$id]->priceSize = (count($pricesSize))

                ? ['min' => min($pricesSize), 'max' => max($pricesSize)]

                : [0];

            $datas[$id]->priceHolst = (count($pricesHolst) > 0)

                ? ['min' => min($pricesHolst), 'max' => max($pricesHolst)]

                : [0];

            $datas[$id]->priceBox = (count($pricesBox) > 0)

                ? ['min' => min($pricesBox), 'max' => max($pricesBox)]

                : [0];

            // $datas[$id]->minSumPrice = min($pricesSize ?? 0) + min($pricesHolst ?? 0);

            // $datas[$id]->maxSumPrice = max($pricesSize ?? 0) + max($pricesHolst ?? 0) + max($pricesBox ?? 0);

            $datas[$id]->minSumPrice = $datas[$id]->price_from;

            $datas[$id]->maxSumPrice = $datas[$id]->price_from;
        }

        return $datas;
    }

    public function sizes()
    {
        // return $this->hasMany(OrdersChats::class);
        return $this->hasManyThrough(
            GallerySize::class,
            GalleryItemsGalleryTagSizes::class,
            'gallery_item_id', // Foreign key on the environments table...
            'id', // Foreign key on the deployments table...
            'id', // Local key on the projects table...
            'gallery_size_id' // Local key on the environments table...
        );
    }

    public function get_style()
    {
        // return $this->hasMany(OrdersChats::class);
        return $this->hasManyThrough(
            AGalleryStyle::class,
            GalleryItemsAGalleryStyle::class,
            'gallery_item_id', // Foreign key on the environments table...
            'id', // Foreign key on the deployments table...
            'id', // Local key on the projects table...
            'a_gallery_style_id' // Local key on the environments table...
        );
    }

    public function get_genre()
    {
        // return $this->hasMany(OrdersChats::class);
        return $this->hasManyThrough(
            AGalleryGenre::class,
            GalleryItemsAGalleryGenre::class,
            'gallery_item_id', // Foreign key on the environments table...
            'id', // Foreign key on the deployments table...
            'id', // Local key on the projects table...
            'a_gallery_genre_id' // Local key on the environments table...
        );
    }

    public function get_age()
    {
        // return $this->hasMany(OrdersChats::class);
        return $this->hasManyThrough(
            AGalleryAge::class,
            GalleryItemsAGalleryAge::class,
            'gallery_item_id', // Foreign key on the environments table...
            'id', // Foreign key on the deployments table...
            'id', // Local key on the projects table...
            'a_gallery_age_id' // Local key on the environments table...
        );
    }


    public function sliderImage(): BelongsTo
    {
        return $this->belongsTo(PortraitSlider::class, 'id', 'cat_id');
    }

    public function GalleryTemplates()
    {
        return $this->hasOne(GalleryTemplates::class);
    }

}
