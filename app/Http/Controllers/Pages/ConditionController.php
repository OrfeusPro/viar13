<?php

namespace App\Http\Controllers\Pages;

use App;
use App\Models\Page;
use App\Models\PageFaq;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\Models\NewhomeService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class ConditionController extends Controller
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
//        $this->template = config('theme.resource') . '.index';
    }

    public function index()
    {
        $modals = "";
        $page = Page::where('url', 'condition')->first();
        $meta_item = $page->translate(App::getLocale(), 'ru');
        $conditionUpdatedAt = Carbon::parse($page->updated_at)
            ->locale(App::getLocale())
            ->translatedFormat('j F Y');
        $pageUrl = url()->current();

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
                        'name' => trans('pages.condition.bread_title'),
                        'item' => $pageUrl,
                    ],
                ],
            ],
        ];

        $content = view(config('theme.resource') . 'pages.condition.index')->with([
            'services' => NewhomeService::where('is_show', 1)->withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
            'faqs' => PageFaq::where('page->condition', 'condition')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),
            'condition_updated_at' => $conditionUpdatedAt,
        ]);

        $this->vars = Arr::add($this->vars, 'title', $meta_item->meta_title);
        $this->vars = Arr::add($this->vars, 'meta_desc', $meta_item->meta_description);
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);
        $this->vars = Arr::add($this->vars, 'structured_data', $structured_data);
        $this->vars = Arr::add($this->vars, 'condition_updated_at', $conditionUpdatedAt);

        return $this->renderOutput();
    }

}
