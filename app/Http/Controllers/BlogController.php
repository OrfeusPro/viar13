<?php

namespace App\Http\Controllers;

use App;
use DB;
use Throwable;
use App\Models\Blog;
use App\Models\PageFaq;
use App\Models\BlogPost;
use App\Models\BlogReklama;
use Illuminate\Support\Arr;
use App\Models\AllStyleForm;
use App\Models\AMailTopSale;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\View;
use App\Models\BlogCategoriesShortBlock;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class BlogController extends Controller
{
    protected $per_page = 6;
    protected $paginate_per_page = 8;

    public function __construct()
    {
        //стартовый шаблон
        $this->template = env('THEME_RESOURCES') . '.index';
    }

    public function render_blog()
    {
        $blog_posts = BlogPost::published()->take($this->per_page)->orderBy(
            'created_at',
            'desc'
        )->get()->translate(App::getLocale());

        $idea_posts = BlogPost::published()->take(8)->where('is_idea', 1)->get()->translate(App::getLocale());

        $data = Blog::first()->get()->translate(App::getLocale());

        return view('blog', [
            'blog_posts' => $blog_posts,
            'idea_posts' => $idea_posts,
            'data' => $data[0],
        ]);
    }

    public function blog(Request $request)
    {
        $params = $request->query();
        $blog_posts = BlogPost::published()->orderBy('created_at', 'desc')->paginate($this->paginate_per_page);
        $blog_posts->appends($params);

        // $new_posts = BlogPost::take(8)->where('is_idea', 1)->get()->translate(App::getLocale());
        $blog_categories = BlogCategory::orderBy('id', 'desc')->get();
        $new_posts = BlogPost::published()->orderBy('id', 'desc')->limit(8)->get();
        $popular_posts = BlogPost::published()->inRandomOrder()->limit(4)->get();
        $rand_post = BlogPost::published()->inRandomOrder()->limit(1)->first();


        $blogwhy = BlogPost::published()->inRandomOrder()->limit(5)->get();

        $cat_rand = false;
        do {
            $rand_cat_post = BlogPost::published()->inRandomOrder()->limit(1)->first();
            if($rand_cat_post->categoryes()->first())
            {
                $cat_rand = true;
            }
        } while ($cat_rand == false);

        $category = BlogCategory::where('slug', $rand_cat_post->categoryes()->first()->slug)->first();
        $category = BlogCategory::find($category->id);
        $blogwhy = $category->posts()->published()->orderBy('created_at', 'desc')->inRandomOrder()->limit(5)->get();

        $blogwhy = BlogPost::published()->inRandomOrder()->limit(5)->get();

        $stories = BlogPost::published()->where('is_stories', 1)->inRandomOrder()->limit(4)->get();
        $blogwant = BlogPost::published()->where('is_blogwant', 1)->inRandomOrder()->limit(4)->get();
        $AMailTopSale = AMailTopSale::inRandomOrder()->limit(3)->get();

        $data = Blog::first()->translate(App::getLocale());

        $content = view(env('THEME_RESOURCES') . '.blog.blog')
            ->with('rand_post', $rand_post)
            ->with('new_posts', $new_posts)
            ->with('popular_posts', $popular_posts)
            ->with('blogwhy', $blogwhy)
            ->with('stories', $stories)
            ->with('blogwant', $blogwant)
            ->with('AMailTopSale', $AMailTopSale)
            ->with('blog_categories', $blog_categories)
            ->with('data', $data)
            ->with('faqs', PageFaq::where('page->blog', 'blog')->withTranslation(App::getLocale(), false)->orderBy('sort', 'asc')->get())
            ->render();

        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'title', $data['meta_title']);
        $this->vars = Arr::add($this->vars, 'meta_desc', $data['meta_desc'] );

        return $this->renderOutput();
    }

    public function blog_category($slug, Request $request)
    {
        try {
            $category = BlogCategory::whereTranslation('slug', $slug)->first();
        } catch (Throwable $th) {
            return abort(404);
        }

        $category = BlogCategory::find($category->id);
        //$category->posts;

        $params = $request->query();
        // $blog_posts = BlogPost::orderBy('created_at', 'desc')->paginate($this->paginate_per_page);
        $blog_posts = $category->posts()->published()->orderBy('created_at', 'desc')->paginate($this->paginate_per_page);
        $blog_posts->appends($params);
        $blog_categories = BlogCategory::orderBy('id', 'desc')->get();
        // $idea_posts = BlogPost::take(8)->where('is_idea', 1)->get()->translate(App::getLocale());
        // ->with('idea_posts', $idea_posts)
        $data = Blog::first()->translate(App::getLocale());
        $AMailTopSale = AMailTopSale::inRandomOrder()->limit(3)->get();
        $blog_short_blocks = BlogCategoriesShortBlock::orderBy('sort', 'asc')->get();

        $content = view(env('THEME_RESOURCES') . '.blog.category')
            ->with('AMailTopSale', $AMailTopSale)
            ->with('blog_short_blocks', $blog_short_blocks)
            ->with('blog_posts', $blog_posts)
            ->with('blog_categories', $blog_categories)
            ->with('category', $category)
            ->with('slug', $slug)
            ->with('data', $data)
            ->render();

        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'title', $category->getTranslatedAttribute('meta_title') );
        $this->vars = Arr::add($this->vars, 'meta_desc', $category->getTranslatedAttribute('meta_desc') );
        return $this->renderOutput();

    }

    public function blog_category_all(Request $request)
    {
        $blog_posts = BlogPost::published()->orderBy('created_at', 'desc')->paginate($this->paginate_per_page);
        $blog_categories = BlogCategory::orderBy('id', 'desc')->get();
        $data = Blog::first()->translate(App::getLocale());
        $AMailTopSale = AMailTopSale::inRandomOrder()->limit(3)->get();
        $blog_short_blocks = BlogCategoriesShortBlock::orderBy('sort', 'asc')->get();

        $category = false;
        $slug = false;

        $content = view(env('THEME_RESOURCES') . '.blog.category')
            ->with('AMailTopSale', $AMailTopSale)
            ->with('blog_short_blocks', $blog_short_blocks)
            ->with('blog_posts', $blog_posts)
            ->with('blog_categories', $blog_categories)
            ->with('category', $category)
            ->with('slug', $slug)
            ->with('data', $data)
            ->render();

        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'title', $data['seo_tag_all_meta_title'] );
        $this->vars = Arr::add($this->vars, 'meta_desc', $data['seo_tag_all_meta_desc'] );
        return $this->renderOutput();
    }



    public function blog_article(Request $request)
    {
        try {
            $blog_item = BlogPost::published()
                ->where(function ($query) use ($request) {
                    $query->whereTranslation('slug', $request->slug);
                })
                ->firstOrFail();

            BlogPost::where('id', $blog_item->id)->update([
                'count_viewed' => \DB::raw('count_viewed + 1'),
                'updated_at' => $blog_item->updated_at,
            ]);
        } catch (Throwable $th) {
            return abort(404);
        }

        $data = Blog::first()->translate(App::getLocale());


        $blogItemModel = $blog_item;
		$blog_author = $blogItemModel->author()->first();
		$blog_right_banner = $blogItemModel->reklama()->first();
        $localized_urls = $this->buildBlogPostLocalizedUrls($blogItemModel);

        $expected_url = $localized_urls[App::getLocale()] ?? null;
        if ($expected_url && urldecode(url()->current()) !== urldecode($expected_url)) {
            return redirect($expected_url, 301);
        }

        $blog_item = $blogItemModel->translate(App::getLocale());

        do {
            $blog_item->text = $this->reklama($blog_item->text);
            $pos1 = strpos($blog_item->text, '{{rek-');
        } while ($pos1 == true);

        if(isset($blog_right_banner->id) && $blog_right_banner->id)
        {
            do {
                $right_banner = " {{rek-".$blog_right_banner->id."}} ";
                $blog_right_banner = $this->reklama($right_banner);
                $pos1 = strpos($blog_right_banner, '{{rek-');
            } while ($pos1 == true);
        }
        else
        {
            $blog_right_banner = '';
        }


        // dd($pos1, $pos2, $reklama, $shablon, $blog_item->text);
        //dd($blog_item, $blog_item->text);
		$faqs = PageFaq::where('page->blog', 'blog')->withTranslation(App::getLocale(), false)->orderBy('sort', 'asc')->get();

        $blog_categories = BlogCategory::orderBy('id', 'desc')->get();
        $popular_posts = BlogPost::published()->inRandomOrder()->limit(4)->get();

        $content = view(env('THEME_RESOURCES') . '.blog.article')
            ->with('blog_item', $blog_item)
            ->with('blog_author', $blog_author)
            ->with('blog_right_banner', $blog_right_banner)
            ->with('popular_posts', $popular_posts)
            ->with('blog_categories', $blog_categories)
            ->with('data', $data)
            ->with('faqs', $faqs)
            ->render();




        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'localized_urls', $localized_urls);
        $this->vars = Arr::add($this->vars, 'canonical_url', $localized_urls[App::getLocale()] ?? null);
        $this->vars = Arr::add($this->vars, 'og_url', $localized_urls[App::getLocale()] ?? null);
        $this->vars = Arr::add($this->vars, 'title', $blog_item->meta_title );
        $this->vars = Arr::add($this->vars, 'meta_desc', $blog_item->meta_desc );
        $this->vars = Arr::add($this->vars, 'updated_at', $blog_item->updated_at);
        return $this->renderOutput();
    }

    private function buildBlogPostLocalizedUrls(BlogPost $blogPost): array
    {
        $localizedUrls = [];
        $supportedLocales = LaravelLocalization::getSupportedLocales();
        $defaultLocale = LaravelLocalization::getDefaultLocale();

        foreach (array_keys($supportedLocales) as $localeCode) {
            $slug = $blogPost->getTranslatedAttribute('slug', $localeCode, false) ?: $blogPost->slug;
            $path = trim(($localeCode === $defaultLocale ? '' : $localeCode . '/') . 'blog/' . ltrim($slug, '/'), '/');
            $localizedUrls[$localeCode] = url($path);
        }

        return $localizedUrls;
    }

    public function reklama($text)
    {
        $reklama_id = false;
        $pos1 = strpos($text, '{{rek-');
        if($pos1)
        {
            $pos2 = strpos($text, '}}', $pos1+1);

            if($pos2)
            {
                $t = substr($text, $pos1+6, $pos2-$pos1-6);
                $reklama_id = (int)$t;
            }
        }

        if($reklama_id)
        {
            $reklama = BlogReklama::where("id",$reklama_id)->first();
            $shablon = '';
            if($reklama)
            {
                $shablon = $reklama->retype()->first()->source;

                $shablon = str_replace("{{title}}", $reklama->getTranslatedAttribute('title'), $shablon);
                $shablon = str_replace("{{promo_text}}", $reklama->getTranslatedAttribute('promo_text'), $shablon);
                $shablon = str_replace("{{btn_href}}", $reklama->btn_href, $shablon);
                $shablon = str_replace("{{btn_text}}", $reklama->getTranslatedAttribute('btn_text'), $shablon);
                $shablon = str_replace("{{size}}", $reklama->getTranslatedAttribute('size'), $shablon);
                $shablon = str_replace("{{image}}", Voyager::image($reklama->image), $shablon);
            }

            $text = str_replace("<p>{{rek-".$reklama_id."}}</p>", $shablon, $text);
            $text = str_replace("{{rek-".$reklama_id."}}", $shablon, $text);
        }

        return $text;
    }

    public function render_inner_blog(Request $request)
    {
        try {
            $blog_item = BlogPost::published()
                ->where(function ($query) use ($request) {
                    $query->whereTranslation('slug', $request->slug);
                })
                ->get()
                ->translate(App::getLocale(), 'ru')[0];
        } catch (Throwable $th) {
            return abort(404);
        }

        $b_data = Blog::first()->get()->translate(App::getLocale(), 'ru')[0];
        $fb_short_text = mb_strimwidth(strip_tags($blog_item['text']), 0, 150, '...');
        $fb_short_text = preg_replace('/\s\s+/', ' ', $fb_short_text);
        $fb_short_text = html_entity_decode($fb_short_text);

        return view('inner_blog')->with('data', $blog_item)->with('b_data', $b_data)->with('fb_short_text', $fb_short_text);
    }

    public function load_ajax_posts(Request $request)
    {
        $page = $request->input('page');
        $offset = $page * $this->per_page;
        $posts = BlogPost::published()->take($this->per_page)->where('is_idea', 0)->where('is_stories', 0)->orderBy(
            'created_at',
            'desc'
        )->skip($offset)->get()->translate(App::getLocale(), 'ru');

        return $posts;
    }
}
