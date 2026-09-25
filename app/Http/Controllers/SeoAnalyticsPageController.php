<?php

namespace App\Http\Controllers;

class SeoAnalyticsPageController extends Controller
{
    public function home()
    {
        return $this->renderPage('home');
    }

    public function privacy()
    {
        return $this->renderPage('privacy');
    }

    public function terms()
    {
        return $this->renderPage('terms');
    }

    private function renderPage($page)
    {
        app()->setLocale('en');

        return view('seo-analytics.'.$page, [
            'title' => trans('seo_analytics.'.$page.'.meta_title'),
            'meta_desc' => trans('seo_analytics.'.$page.'.meta_description'),
        ]);
    }
}
