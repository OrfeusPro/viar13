<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;

class RobotsController extends Controller
{
    /**
     * Генерирует содержимое robots.txt на основе текущего языка.
     */
    public function index()
    {
        $locale = App::getLocale();

        // Определите содержимое для каждого языка
        $host = 'viarcanvas.com';
        $sitemap = "https://viarcanvas.com/{$locale}/sitemap.xml";
        $robotsContent = <<<EOL
User-agent: *
Disallow: /admin/
Disallow: /*login
Disallow: /*?
Disallow: /javascript/
Disallow: *?search=*
Disallow: *?color=*
Disallow: *?order=*
Disallow: *?size=*

Host: {$host}
Sitemap: {$sitemap}
EOL;

        return response($robotsContent, 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Перенаправление для основного robots.txt
     */
    public function mainRobots()
    {
        return redirect('/robots.txt');
    }
}
