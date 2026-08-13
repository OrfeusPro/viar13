<?php

namespace App\Http\Controllers;

use App;
use App\Models\Blog;
use App\Models\BlogPost;
use App\Models\AGalleryAge;
use App\Models\GalleryItem;
use App\Models\GalleryType;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use App\Models\AGalleryGenre;
use App\Models\AGalleryStyle;
use App\Models\GalleryCategory;
use Illuminate\Support\Facades\DB;
use App\Models\AGalleryNationality;

class SitemapController extends Controller
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


    public function sitemap(Request $request)
	{
        $gallery = new GalleryType();
        $module =  GalleryItem::getItems($gallery->getType('module')->id, false, false, true);
        $photo =  GalleryItem::getItems($gallery->getType('photo')->id, false, false, true);
        $reproduction =  GalleryItem::getItems($gallery->getType('reproduction')->id, false, false, true);

        $contry_mult = DB::table('country_tels')->get();

        $loc = app()->getLocale();

        if ($loc == 'ru') {
            $loc = '';
        } else {
            $loc = '/' . app()->getLocale();
        }

		$xml = view(env('THEME_RESOURCES').'.pages.advertising.sitemap')
        ->with("contry_mult", $contry_mult)
        ->with("loc", $loc)
        ->render();
		return response($xml, 200)->header('Content-Type', 'application/xml');
	}

	public function sitemap_products(Request $request)
	{
        $gallery = new GalleryType();
        $module =  GalleryItem::getItems($gallery->getType('module')->id, false, false, true);
        $photo =  GalleryItem::getItems($gallery->getType('photo')->id, false, false, true);
        $reproduction =  GalleryItem::getItems($gallery->getType('reproduction')->id, false, false, true);
//        dd($reproduction);
        $gc = new GalleryCategory();
        $categories = collect();

        foreach (['module', 'photo', 'reproduction'] as $type) {
            $items = $gc->getAll($type)->map(function ($item) use ($type) {
                $item->type = $item->getType(); // или просто $type, если нужен текущий тип
                return $item;
            });

            $categories = $categories->merge($items);
        }


        $contry_mult = DB::table('country_tels')->get();

        $loc = app()->getLocale();

        if ($loc == 'ru') {
            $loc = '';
        } else {
            $loc = '/' . app()->getLocale();
        }

        $blog_posts = BlogPost::published()->get()->translate(App::getLocale(), 'ru')->toArray();
        $blog_categories = BlogCategory::orderBy('id', 'desc')->get();
        $graphicportrait =  GalleryItem::getItems(5, false, false, true);
        $gallery_genres = AGalleryGenre::all();
        $gallery_styles = AGalleryStyle::all();
        $gallery_nationality = AGalleryNationality::all();
        $gallery_age = AGalleryAge::all();
        $gallery_painters = GalleryCategory::where("is_painter", 1)->get();
        // dd($gallery_painters);
        // dd($gallery_styles);

		$xml = view(env('THEME_RESOURCES').'.pages.advertising.sitemap_main_products')
        ->with("module", $module)
        ->with("photo", $photo)
        ->with("reproduction", $reproduction)
        ->with("categories", $categories)
        ->with("graphicportrait", $graphicportrait)
        ->with("contry_mult", $contry_mult)
        ->with("loc", $loc)
        ->with("blog_posts", $blog_posts)
        ->with("blog_categories", $blog_categories)
        ->with("gallery_genres", $gallery_genres)
        ->with("gallery_styles", $gallery_styles)
        ->with("gallery_nationality", $gallery_nationality)
        ->with("gallery_age", $gallery_age)
        ->with("gallery_painters", $gallery_painters)
        ->render();
		return response($xml, 200)->header('Content-Type', 'application/xml');
	}

}
