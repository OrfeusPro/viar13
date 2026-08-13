<?php

namespace App\Http\Controllers\Pages;

use App;
use App\Models\Page;
use App\Models\Stock;
use App\Models\PageFaq;
use App\Models\NewhomeService;
use Illuminate\Support\Arr as Arr;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class FaqController extends Controller
{
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->template = config('theme.resource') . '.index';
    }

    public function index()
    {
        $meta_item = Page::where('url', 'faq')->first();
        $meta_item = $meta_item->translate(App::getLocale(), 'ru');

        $page = Stock::first()->get()->translate(App::getLocale(), 'ru')[0];
        $modals = "";
        $content = view(config('theme.resource') . 'pages.faq.faq')->with([
            'page' => $page,
            'faqs' => PageFaq::where('page->faq', 'faq')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),
            'services' => NewhomeService::where('is_show', 1)->withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
        ]);

        $this->vars = Arr::add($this->vars, 'title', $meta_item->meta_title);
        $this->vars = Arr::add($this->vars, 'meta_desc', $meta_item->meta_description);
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);

        return $this->renderOutput();
    }

}
