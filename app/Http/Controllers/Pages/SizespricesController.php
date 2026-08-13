<?php

namespace App\Http\Controllers\Pages;

use App;
use App\Models\Page;
use App\Models\PageFaq;
use App\Models\GalleryItem;
use App\Models\CanvasSlider;
use Illuminate\Support\Arr;
use App\Models\ACollageHead;
use App\Models\CanvasHeader;
use App\Models\NewhomeService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class SizespricesController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
//        $this->template = config('theme.resource') . '.index';
    }

    public function index()
    {
        $modals = "";
        $meta_item = Page::where('url', 'sizesprices')->first();
        $meta_item = $meta_item->translate(App::getLocale(), 'ru');
        $canonicalUrl = url()->current();

        $canvasCard = CanvasSlider::where('is_show', 1)->where('sub_canvas', 0)->first();
        $ogImageVersion = seo_image_version($canvasCard && $canvasCard->updated_at ? $canvasCard->updated_at : null);
        $ogImageUrl = single_image_url('sizesprices', $ogImageVersion);

        $galleries = GalleryItem::with(['sizes', 'sliderImage'])
            ->withTranslation(App::getLocale())->where('id_type', 5)
            ->orderBy('sorting')->get();
        $collage = ACollageHead::first()->translate(App::getLocale(), 'ru');

        $canvas = CanvasHeader::withTranslation(App::getLocale())->first();

        $content = view(config('theme.resource') . 'pages.sizesprices.index')->with([
            'services'    => NewhomeService::where('is_show', 1)->withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
            'galleries'   => $galleries,
            'collage'     => $collage,
            'canvas'      => $canvas,
            'faqs'        => PageFaq::where('page->prices_sizes', 'prices_sizes')->withTranslation(App::getLocale(), false)->orderBy('sort', 'asc')->get(),
        ]);

        $this->vars = Arr::add($this->vars, 'title', $meta_item->meta_title);
        $this->vars = Arr::add($this->vars, 'meta_desc', $meta_item->meta_description);
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);
        $this->vars = Arr::add($this->vars, 'og_url', $canonicalUrl);
        $this->vars = Arr::add($this->vars, 'og_image', $ogImageUrl);
        $this->vars = Arr::add($this->vars, 'image_src', $ogImageUrl);
        $this->vars = Arr::add($this->vars, 'og_type', 'website');

        return $this->renderOutput();
    }
}
