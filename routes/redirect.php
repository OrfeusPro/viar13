<?php
use App\Models\CanvasSlider;
use App\Models\ACollageSlider;
use App\Models\GalleryItem;
use App\Models\PortraitSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

if (strpos(URL::current(), 'politics.html') !== false) {
    header("Location: /page/terms-sale");
    exit;
}
if (strpos(URL::current(), '/page/terms-sale') !== false) {
    header("Location: /condition");
    exit;
}

function resolveCanvasBannerImageResponse()
{
    $canvasSlider = CanvasSlider::where('is_show', 1)
        ->where('sub_canvas', 0)
        ->orderBy('sort', 'asc')
        ->first();

    $source = resolveCanvasBannerImageSource($canvasSlider);
    $sourcePath = $source['path'] ?? null;
    $storageUrl = $source['url'] ?? null;
    $mime = $source['mime'] ?? 'image/png';

    if ($sourcePath && file_exists($sourcePath)) {
        return response()->file($sourcePath, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    if ($storageUrl) {
        $binary = @file_get_contents($storageUrl);
        if ($binary !== false && $binary !== '') {
            return response($binary, 200, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
    }

    if (request()->boolean('debug')) {
        return singleImageDebugResponse('canvas', $source, [
            'canvas_slider_id' => $canvasSlider->id ?? null,
            'canvas_slider_size_img' => $canvasSlider->size_img ?? null,
            'canvas_slider_image' => $canvasSlider->image ?? null,
        ]);
    }

    $fallback = public_path('images/logo.png');
    if (file_exists($fallback)) {
        return response()->file($fallback);
    }

    abort(404);
}

function resolveCanvasBannerImageSource(?CanvasSlider $canvasSlider): ?array
{
    if (!$canvasSlider) {
        return null;
    }

    if (!empty($canvasSlider->size_img)) {
        $relativePath = ltrim((string) $canvasSlider->size_img, '/');

        return [
            'relative' => $relativePath,
            'url' => url('/storage/' . $relativePath),
            'path' => Storage::disk('public')->exists($relativePath) ? Storage::disk('public')->path($relativePath) : null,
            'mime' => canvasImageMime($relativePath),
        ];
    }

    if (!empty($canvasSlider->image)) {
        $sourceUrl = (string) \Voyager::image($canvasSlider->image);
        $parsedPath = parse_url($sourceUrl, PHP_URL_PATH);

        return [
            'relative' => ltrim((string) $canvasSlider->image, '/'),
            'url' => $sourceUrl,
            'path' => $parsedPath ? public_path(ltrim($parsedPath, '/')) : null,
            'mime' => canvasImageMime((string) $canvasSlider->image),
        ];
    }

    return null;
}

function resolveSingleImageResponse(string $slug)
{
    $slug = trim($slug);

    if ($slug === 'canvas') {
        return resolveCanvasBannerImageResponse();
    }

    $source = resolveSingleImageSource($slug);
    $sourcePath = $source['path'] ?? null;
    $sourceUrl = $source['url'] ?? null;
    $mime = $source['mime'] ?? 'image/png';

    if ($sourcePath && file_exists($sourcePath)) {
        return response()->file($sourcePath, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    if ($sourceUrl) {
        $binary = @file_get_contents($sourceUrl);
        if ($binary !== false && $binary !== '') {
            return response($binary, 200, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
    }

    if (request()->boolean('debug')) {
        return singleImageDebugResponse($slug, $source);
    }

    abort(404);
}

function resolveSingleImageSource(string $slug): ?array
{
    if ($slug === 'sizesprices') {
        return resolveCanvasBannerImageSource(CanvasSlider::where('is_show', 1)->where('sub_canvas', 0)->first());
    }

    if ($slug === 'collage') {
        $slide = ACollageSlider::where('is_show', 1)->orderBy('sort', 'asc')->first();
        if (!$slide) {
            return singleImagePublicSource(env('THEME') . 'images/collage/main.webp');
        }

        $field = $slide->size_img ?? ($slide[app()->getLocale()] ?? $slide['ru'] ?? null);
        return $field ? singleImageStorageSource((string) $field) : singleImagePublicSource(env('THEME') . 'images/collage/main.webp');
    }

    if ($slug === 'module-generator') {
        return singleImagePublicSource(env('THEME') . 'images/module-generator/main.png');
    }

    if (preg_match('/^portrait-(\d+)$/', $slug, $matches)) {
        $item = GalleryItem::with('sliderImage')->find((int) $matches[1]);
        return $item ? resolvePortraitItemImageSource($item) : null;
    }

    if (preg_match('/^product-(\d+)$/', $slug, $matches)) {
        $item = GalleryItem::find((int) $matches[1]);
        return $item ? resolveProductItemImageSource($item) : null;
    }

    if (preg_match('/^product-oil-(\d+)$/', $slug, $matches)) {
        $item = GalleryItem::find((int) $matches[1]);
        return $item ? resolveProductOilItemImageSource($item) : null;
    }

    if (preg_match('/^gallery-item-(\d+)$/', $slug, $matches)) {
        $item = GalleryItem::find((int) $matches[1]);
        return $item ? resolveGalleryItemImageSource($item) : null;
    }

    return null;
}

function resolvePortraitItemImageSource(GalleryItem $item): ?array
{
    $portraitSlide = PortraitSlider::where('cat_id', $item->id)
        ->where('is_show', 1)
        ->orderBy('sort', 'asc')
        ->orderBy('id', 'desc')
        ->first() ?: $item->sliderImage;

    if ($portraitSlide && !empty($portraitSlide->size_img)) {
        return singleImageStorageSource((string) $portraitSlide->size_img);
    }

    if ($portraitSlide && !empty($portraitSlide->png)) {
        return singleImageStorageSource((string) $portraitSlide->png);
    }

    return singleImagePublicSource('img/logo.png');
}

function resolveProductItemImageSource(GalleryItem $item): ?array
{
    $sliderPreview = PortraitSlider::where('cat_id', $item->id)
        ->where('is_show', 1)
        ->orderBy('sort', 'asc')
        ->orderBy('id', 'desc')
        ->first() ?: $item->sliderImage;

    if ($sliderPreview && !empty($sliderPreview->size_img)) {
        return singleImageStorageSource((string) $sliderPreview->size_img);
    }

    if ($sliderPreview && !empty($sliderPreview->png)) {
        return singleImageStorageSource((string) $sliderPreview->png);
    }

    foreach (['our_works_new', 'background'] as $collection) {
        $media = $item->getMedia($collection)->first();
        if ($media) {
            return singleImageUrlSource($media->getUrl());
        }
    }

    if (!empty($item->new_main_image)) {
        return singleImageUrlSource(\Voyager::image($item->new_main_image));
    }

    return singleImagePublicSource('img/logo.png');
}

function resolveProductOilItemImageSource(GalleryItem $item): ?array
{
    $firstSlide = PortraitSlider::where('cat_id', $item->id)
        ->where('is_show', 1)
        ->orderBy('sort', 'asc')
        ->first();

    if ($firstSlide && !empty($firstSlide->png)) {
        return singleImageStorageSource((string) $firstSlide->png);
    }

    $source = resolveProductItemImageSource($item);
    if ($source && (($source['relative'] ?? '') !== 'img/logo.png')) {
        return $source;
    }

    $media = $item->getMedia('works_examples_new')->first();
    if ($media) {
        return singleImageUrlSource($media->getUrl());
    }

    return $source;
}

function resolveGalleryItemImageSource(GalleryItem $item): ?array
{
    $galleryImages = json_decode((string) $item->images, true);
    if (is_array($galleryImages) && !empty($galleryImages[0])) {
        return singleImageStorageSource((string) $galleryImages[0]);
    }

    if (!empty($item->new_main_image)) {
        return singleImageUrlSource(\Voyager::image($item->new_main_image));
    }

    return singleImagePublicSource('img/logo.png');
}

function singleImageStorageSource(string $relativePath): array
{
    $relativePath = ltrim($relativePath, '/');

    return [
        'relative' => $relativePath,
        'url' => url('/storage/' . $relativePath),
        'path' => Storage::disk('public')->exists($relativePath) ? Storage::disk('public')->path($relativePath) : null,
        'mime' => canvasImageMime($relativePath),
    ];
}

function singleImagePublicSource(string $relativePath): array
{
    $relativePath = ltrim($relativePath, '/');

    return [
        'relative' => $relativePath,
        'url' => url('/' . $relativePath),
        'path' => public_path($relativePath),
        'mime' => canvasImageMime($relativePath),
    ];
}

function singleImageUrlSource(string $url): array
{
    $path = parse_url($url, PHP_URL_PATH);

    return [
        'relative' => $path ? ltrim($path, '/') : $url,
        'url' => preg_match('~^https?://~i', $url) ? $url : url(ltrim($url, '/')),
        'path' => $path ? public_path(ltrim($path, '/')) : null,
        'mime' => canvasImageMime($url),
    ];
}

function singleImageDebugResponse(string $slug, ?array $source, array $extra = [])
{
    $sourcePath = $source['path'] ?? null;
    $sourceUrl = $source['url'] ?? null;
    $urlReadable = false;

    if ($sourceUrl) {
        $headers = @get_headers($sourceUrl);
        $urlReadable = is_array($headers) && isset($headers[0]) && strpos($headers[0], '200') !== false;
    }

    return response()->json(array_merge([
        'status' => 'debug',
        'slug' => $slug,
        'route_reached' => true,
        'source' => $source,
        'source_path_exists' => $sourcePath ? file_exists($sourcePath) : false,
        'source_url_readable' => $urlReadable,
        'public_path' => public_path(),
        'storage_public_path' => storage_path('app/public'),
    ], $extra), 404);
}

function canvasImageMime(string $path): string
{
    $extension = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?: $path, PATHINFO_EXTENSION));

    if ($extension === 'jpg' || $extension === 'jpeg') {
        return 'image/jpeg';
    }

    if ($extension === 'webp') {
        return 'image/webp';
    }

    return 'image/png';
}

function redirectToCanvasSingleImage(Request $request)
{
    $query = $request->query();
    unset($query['_url']);

    $queryString = http_build_query($query);

    return Redirect::to('/images_single/canvas.png' . ($queryString ? '?' . $queryString : ''), 301);
}

Route::get('/images_single/canvas.png', function () {
    return resolveSingleImageResponse('canvas');
});

Route::get('/images_single/canvas-banner-img.png', function (Request $request) {
    return redirectToCanvasSingleImage($request);
});

Route::get('/img/canvas-banner-img.png', function (Request $request) {
    return redirectToCanvasSingleImage($request);
});

Route::get('/images_single/{slug}.png', function ($slug) {
    return resolveSingleImageResponse((string) $slug);
})->where('slug', '[A-Za-z0-9_-]+');

Route::get('/images_single/{slug}', function ($slug) {
    return resolveSingleImageResponse((string) $slug);
})->where('slug', '[A-Za-z0-9_-]+');

Route::get('/images_single_debug/{slug}', function ($slug) {
    request()->merge(['debug' => true]);

    return resolveSingleImageResponse((string) $slug);
})->where('slug', '[A-Za-z0-9_-]+');

// Fallback: redirect any /pl/* requests to dedicated Polish domain
Route::any('/pl/{any?}', function () {
    return Redirect::to('https://viar-art.pl/', 301);
})->where('any', '.*');

function multiLangRedirect(string $from, string $to, array $locales = ['en','pl','lv','lt','ee','de','ru'])
{
    Route::get($from, function () use ($to) {
        return Redirect::to($to, 301);
    });

    foreach ($locales as $locale) {
        $fromLocale = "/{$locale}{$from}";
        $toLocale   = "/{$locale}{$to}";

        Route::get($fromLocale, function () use ($toLocale) {
            return Redirect::to($toLocale, 301);
        });
    }
}

multiLangRedirect('/new/graphic-portrait/portrait-Dream-Art', '/new/graphic-portrait/portrait-dream-art');
multiLangRedirect('/modular-pictures', '/modular-generator');
multiLangRedirect('/canvas', '/new/canvas');
multiLangRedirect('/gallery/oil-pictures', '/new/graphic-portrait/kartiny');
multiLangRedirect('/graphic-portrait/Doppelbelichtung-Portrait', '/all_styles');
multiLangRedirect('/graphic-portrait/Farb- und Schwarz-Weiß-Fotos', '/all_styles');
multiLangRedirect('/graphic-portrait/Fotokorrektur', '/all_styles');
multiLangRedirect('/graphic-portrait/Fotowiederherstellung', '/all_styles');
multiLangRedirect('/graphic-portrait/Grafik-Porträt', '/new/graphic-portrait/Grafik-Porträt');
multiLangRedirect('/graphic-portrait/Liebe ist Porträt', '/all_styles');
multiLangRedirect('/graphic-portrait/Low Poly Portrait', '/all_styles');
multiLangRedirect('/graphic-portrait/Portret-w-stylu-Love-is', '/all_styles');
multiLangRedirect('/graphic-portrait/Porträt-aus-Wörtern', '/all_styles');
multiLangRedirect('/graphic-portrait/classic-portrait', '/new/graphic-portrait/graphic-portrait');
multiLangRedirect('/graphic-portrait/classic-portrait/buy', '/new/graphic-portrait/graphic-portrait');
multiLangRedirect('/graphic-portrait/color-and-black-and-white-photos', '/all_styles');
multiLangRedirect('/graphic-portrait/customs-royal-portraits', '/new/graphic-portrait/portrait-historical');
multiLangRedirect('/graphic-portrait/double-exposition-portrait', '/all_styles');
multiLangRedirect('/graphic-portrait/dream-art-portrait', '/new/graphic-portrait/portrait-dream-art');
multiLangRedirect('/graphic-portrait/foto-korekcja', '/all_styles');
multiLangRedirect('/graphic-portrait/graphic-portrait', '/new/graphic-portrait/graphic-portrait');
multiLangRedirect('/graphic-portrait/graphic-portrait/buy', '/new/graphic-portrait/graphic-portrait');
multiLangRedirect('/graphic-portrait/graphic-portrait/buy', '/new/graphic-portrait/graphic-portrait');
multiLangRedirect('/graphic-portrait/karikatur-porträt', '/new/caricature');
multiLangRedirect('/graphic-portrait/kolorowe-czarno-białe-zdjęcie', '/all_styles');
multiLangRedirect('/graphic-portrait/love-is-portrait', '/all_styles');
multiLangRedirect('/graphic-portrait/love-is-portrait', '/all_styles');
multiLangRedirect('/graphic-portrait/love-isportrait', '/all_styles');
multiLangRedirect('/graphic-portrait/low-poly-portrait', '/all_styles');
multiLangRedirect('/graphic-portrait/low-poly-portrait', '/all_styles');
multiLangRedirect('/graphic-portrait/low-poly-portret', '/all_styles');
multiLangRedirect('/graphic-portrait/modulnaya-kartina-pavliny', '/all_styles');
multiLangRedirect('/graphic-portrait/murciano-portrait', '/all_styles');
multiLangRedirect('/graphic-portrait/murkisches Porträt', '/all_styles');
multiLangRedirect('/graphic-portrait/odnawianie-zdjęć', '/all_styles');
multiLangRedirect('/graphic-portrait/photo-correction', '/all_styles');
multiLangRedirect('/graphic-portrait/photo-recovery', '/all_styles');
multiLangRedirect('/graphic-portrait/pop-art-portrait', '/new/graphic-portrait/pop-art-portrait');
multiLangRedirect('/graphic-portrait/pop-art-portrait/buy', '/new/graphic-portrait/pop-art-portrait');
multiLangRedirect('/graphic-portrait/portrait-beauty-art', '/new/graphic-portrait/portrait-beauty-art');
multiLangRedirect('/graphic-portrait/portrait-beauty-art/buy', '/new/graphic-portrait/portrait-beauty-art');
multiLangRedirect('/graphic-portrait/portrait-caricature', '/new/graphic-portrait/portrait-caricature');
multiLangRedirect('/graphic-portrait/portrait-caricature/buy', '/new/graphic-portrait/portrait-caricature');
multiLangRedirect('/graphic-portrait/portrait-colorization', '/all_styles');
multiLangRedirect('/graphic-portrait/portrait-compliment', '/all_styles');
multiLangRedirect('/graphic-portrait/portrait-double-exposure', '/all_styles');
multiLangRedirect('/graphic-portrait/portrait-dream-art', '/new/graphic-portrait/portrait-dream-art');
multiLangRedirect('/graphic-portrait/portrait-dream-art/buy', '/new/graphic-portrait/portrait-dream-art');
multiLangRedirect('/graphic-portrait/portrait-in-image', '/new/graphic-portrait/portrait-historical');
multiLangRedirect('/graphic-portrait/portrait-in-the-image', '/new/graphic-portrait/portrait-historical');
multiLangRedirect('/graphic-portrait/portrait-made-from-words', '/all_styles');
multiLangRedirect('/graphic-portrait/portrait-of-love-is', '/all_styles');
multiLangRedirect('/graphic-portrait/portrait-of-murciano', '/all_styles');
multiLangRedirect('/graphic-portrait/portrait-pop-art', '/new/graphic-portrait/pop-art-portrait');
multiLangRedirect('/graphic-portrait/portrait-retouch', '/all_styles');
multiLangRedirect('/graphic-portrait/portrait-spancaricaturespan', '/new/caricature');
multiLangRedirect('/graphic-portrait/portrait-spanclassicspan', '/new/graphic-portrait/graphic-portrait');
multiLangRedirect('/graphic-portrait/portret-beauty-art', '/new/graphic-portrait/portrait-beauty-art');
multiLangRedirect('/graphic-portrait/portret-karykatura', '/new/caricature');
multiLangRedirect('/graphic-portrait/portret-murchiano', '/all_styles');
multiLangRedirect('/graphic-portrait/portret-pop-art', '/new/graphic-portrait/pop-art-portrait');
multiLangRedirect('/graphic-portrait/portret-stylizowany', '/new/graphic-portrait/portrait-historical');
multiLangRedirect('/graphic-portrait/portret-z-podwójną-ekspozycją','/all_styles');
multiLangRedirect('/graphic-portrait/portret-ze-słów', '/all_styles');
multiLangRedirect('/graphic-portrait/simpsons-portrait', '/simpsons');
multiLangRedirect('/graphic-portrait/spanartspan-portraits', '/new/graphic-portrait/portrait-dream-art');
multiLangRedirect('/graphic-portrait/spanpaintingsspan', '/new/graphic-portrait/kartiny');
multiLangRedirect('/graphic-portrait/spanpetspanportrait', '/new/graphic-portrait/pet-portrait');
multiLangRedirect('/graphic-portrait/vintage-portrait', '/all_styles');
multiLangRedirect('/new/caricature/athlete_caricatures', '/new/caricature/athlete-caricatures');
multiLangRedirect('/new/caricature/black_and_white_caricatures', '/new/caricature/black-and-white-caricatures');
multiLangRedirect('/new/caricature/caricature_for_birthday', '/new/caricature/caricature-for-birthday');
multiLangRedirect('/new/caricature/family_caricature', '/new/caricature/family-caricature');
multiLangRedirect('/new/caricature/group_caricatures', '/new/caricature/group-caricatures');
multiLangRedirect('/new/caricature/portrait-in-image', '/new/graphic-portrait/portrait-historical');
multiLangRedirect('/new/caricature/portrait-in-the-image', '/new/graphic-portrait/portrait-historical');
multiLangRedirect('/new/caricature/portret-stylizowany', '/new/graphic-portrait/portrait-historical');
multiLangRedirect('/new/caricature/spancaricaturespan-for-birthday', '/new/caricature/caricature-for-birthday');
multiLangRedirect('/new/caricature/wedding_caricature', '/new/caricature/wedding-caricature');
multiLangRedirect('/new/caricature/сaricature_for_сolleagues', '/new/caricature/caricature-for-colleagues');
multiLangRedirect('/new/graphic-portrait/-988', '/new/graphic-portrait/kartiny');
multiLangRedirect('/new/graphic-portrait/Grafik-Porträt', '/new/graphic-portrait/graphic-portrait');
multiLangRedirect('/new/graphic-portrait/classic-portrait', '/new/graphic-portrait/graphic-portrait');
multiLangRedirect('/new/graphic-portrait/customs-royal-portraits', '/new/graphic-portrait/portrait-historical');
multiLangRedirect('/new/graphic-portrait/karikatur-porträt', '/new/caricature');
multiLangRedirect('/new/graphic-portrait/klasisks-portrets', '/new/graphic-portrait/graphic-portrait');
multiLangRedirect('/new/graphic-portrait/portrait-beauty-art/buy', '/new/graphic-portrait/portrait-beauty-art');
multiLangRedirect('/new/graphic-portrait/portrait-caricature', '/new/caricature');
multiLangRedirect('/new/graphic-portrait/portrait-dream-art/buy', '/new/graphic-portrait/portrait-dream-art');
multiLangRedirect('/new/graphic-portrait/portrait-pop-art', '/new/graphic-portrait/pop-art-portrait');
multiLangRedirect('/new/graphic-portrait/portrait-spancaricaturespan', '/new/caricature');
multiLangRedirect('/new/graphic-portrait/portrait-spanclassicspan', '/new/graphic-portrait/graphic-portrait');
multiLangRedirect('/new/graphic-portrait/portret-beauty-art', '/new/graphic-portrait/portrait-beauty-art');
multiLangRedirect('/new/graphic-portrait/portret-dream-art', '/new/graphic-portrait/portrait-dream-art');
multiLangRedirect('/new/graphic-portrait/portret-graficzny', '/new/graphic-portrait/graphic-portrait');
multiLangRedirect('/new/graphic-portrait/portret-karykatura', '/new/caricature');
multiLangRedirect('/new/graphic-portrait/simposons-custums', '/simpsons');
multiLangRedirect('/new/graphic-portrait/simpsons-portrait', '/simpsons');
multiLangRedirect('/new/graphic-portrait/spanartspan-portraits', '/new/graphic-portrait/portrait-dream-art');
multiLangRedirect('/new/graphic-portrait/spanbeauty-art-span-portree', '/new/graphic-portrait/portrait-beauty-art');
multiLangRedirect('/new/graphic-portrait/spanpaintingsspan', '/new/graphic-portrait/kartiny');
multiLangRedirect('/new/graphic-portrait/spanpetspanportrait', '/new/graphic-portrait/pet-portrait');
multiLangRedirect('/new/graphic-portrait/süßes Kunstportrait', '/new/graphic-portrait/portrait-beauty-art');
multiLangRedirect('/new/graphic-portrait/traum-kunst-porträt', '/new/graphic-portrait/portrait-dream-art');
multiLangRedirect('/oil-portrait', '/new/graphic-portrait/kartiny');
multiLangRedirect('/page/faq', '/faq');
multiLangRedirect('/photo', '/photo_portrait');
multiLangRedirect('/blog/gift-for_man', '/blog/gifts-for-men-1');
multiLangRedirect('/blog/gift-for-mather', '/blog/gift-for-mom');
multiLangRedirect('/blog/foto-kanvas', '/blog/canvas-printing-from-photo');
multiLangRedirect('/blog/man-gift', '/blog/gifts-for-men');
multiLangRedirect('/blog/ffff', '/blog/gifts-for-men');
multiLangRedirect('/blog/kanvas_apdruka', '/blog/kanvas-apdruka');
multiLangRedirect('/blog/foto_uz_kanvas', '/blog/foto-uz-kanvas');
multiLangRedirect('/blog/kanva', '/blog/custom-canvas-prints');
multiLangRedirect('/blog/kanvas', '/blog/custom-canvas-prints');
multiLangRedirect('/blog/fotolõuend', '/blog/custom-canvas-prints');
multiLangRedirect('/blog/fotodrobės', '/blog/custom-canvas-prints');
multiLangRedirect('/blog/canvas', '/blog/custom-canvas-prints');
multiLangRedirect('/blog/-20', '/blog/portrait-for-parents');
multiLangRedirect('/blog/-19', '/blog/canvas-modular-generator');
multiLangRedirect('/blog/-18', '/blog/pet-portrait');
multiLangRedirect('/blog/-17', '/blog/simpsons-portrait');
multiLangRedirect('/blog/-16', '/blog/portrait-for-woman');
multiLangRedirect('/blog/-15', '/blog/royal-portraits');
multiLangRedirect('/blog/-12', '/blog/portrait-for-wedding');
multiLangRedirect('/blog/-14', '/blog/portrait-for-wedding');
multiLangRedirect('/blog/-13', '/blog/anniversary-portrait');
multiLangRedirect('/blog/-12', '/blog/children-portrait');
multiLangRedirect('/blog/-11', '/blog/family-portrait');
multiLangRedirect('/blog/-10', '/blog/portrait-for-man');
multiLangRedirect('/blog/-9', '/blog/gift-for-boss');
multiLangRedirect('/blog/sarzas-karikaturas', '/blog/caricature-from-photo-is-a-great-gift');
multiLangRedirect('/blog/sarzas-karykatura', '/blog/caricature-from-photo-is-a-great-gift');
multiLangRedirect('/blog/giclee-technology-art', '/blog/creating-realistic-paintings');
multiLangRedirect('/blog/photo-recovery-canvas', '/blog/photo-restoration-and-photo-retouching');
multiLangRedirect('/blog/painting-artists', '/blog/professional-viarcanvas-artists');
multiLangRedirect('/blog/canvas-wall-print', '/blog/canvas-print-composition');
multiLangRedirect('/blog/kompozycja-z-fotoobrazów', '/blog/canvas-print-composition');
multiLangRedirect('/blog/art-portrait-canvas-kanvas', '/blog/art-portrait-canvas');
multiLangRedirect('/blog/portrety-artystyczne', '/blog/art-portrait-canvas');
multiLangRedirect('/blog/-4', '/blog/art-portrait-canvas');
multiLangRedirect('/blog/art-portrait-kanvas-art', '/blog/art-portrait-as-a-gift-for-the-loved-ones');
multiLangRedirect('/blog/art-prezent-dla-bliskich', '/blog/art-portrait-as-a-gift-for-the-loved-ones');
multiLangRedirect('/blog/druka-uz-audekla-dāvanā', '/blog/photo-on-canvas-as-a-gift-how-to-surprise-loved-ones-on-a-holiday');
multiLangRedirect('/blog/kanvas-nice-gift-for', '/blog/photo-on-canvas-as-a-gift-how-to-surprise-loved-ones-on-a-holiday');
multiLangRedirect('/blog/maalimine-fotost-lõuendile-suurepärane-kingitus', '/blog/photo-on-canvas-as-a-gift-how-to-surprise-loved-ones-on-a-holiday');
multiLangRedirect('/blog/Paveikslas-is-nuotraukos-ant-drobės', '/blog/photo-on-canvas-as-a-gift-how-to-surprise-loved-ones-on-a-holiday');
multiLangRedirect('/blog/fotoobraz-na-płótnie-w-prezencie', '/blog/photo-on-canvas-as-a-gift-how-to-surprise-loved-ones-on-a-holiday');
multiLangRedirect('/blog/-2', '/blog/photo-on-canvas-as-a-gift-how-to-surprise-loved-ones-on-a-holiday');
multiLangRedirect('/blog/kanvas-kvalitāte-foto', '/blog/which-photos-are-suitable-for-creating-a-portrait');
multiLangRedirect('/blog/photo-canvas-quality', '/blog/which-photos-are-suitable-for-creating-a-portrait');
multiLangRedirect('/blog/millised-fotod-sobivad-portree-loomiseks', '/blog/which-photos-are-suitable-for-creating-a-portrait');
multiLangRedirect('/blog/fotografie-do-portretu', '/blog/which-photos-are-suitable-for-creating-a-portrait');
multiLangRedirect('/blog/portret-chastichka-dushi-na-polotne', '/blog/portrait-is-a-part-of-the-soul-on-the-canvas-a-few-words-about-the-quality-of-the-portrait');
multiLangRedirect('/blog/portrets', '/blog/portrait-is-a-part-of-the-soul-on-the-canvas-a-few-words-about-the-quality-of-the-portrait');
multiLangRedirect('/blog/portree-on-tükk-hinge-lõuendil', '/blog/portrait-is-a-part-of-the-soul-on-the-canvas-a-few-words-about-the-quality-of-the-portrait');
multiLangRedirect('/blog/-14', '/blog/portrait-is-a-part-of-the-soul-on-the-canvas-a-few-words-about-the-quality-of-the-portrait');
multiLangRedirect('/blog/portret-to-fragment-duszy-na-płotnie-o-jakości-portretu', '/blog/portrait-is-a-part-of-the-soul-on-the-canvas-a-few-words-about-the-quality-of-the-portrait');
multiLangRedirect('/blog/porträt-ist-ein-teil-der-seele-auf-der-leinwand-ein-ein paar-worte-über-die-qualität-des-porträts', '/blog/portrait-is-a-part-of-the-soul-on-the-canvas-a-few-words-about-the-quality-of-the-portrait');
multiLangRedirect('/blog/detskij-kollazh-samyj-luchshij-podarok-dlya-roditelej-i-druzej', '/blog/collage-with-children-the-best-gift-for-parents-and-children');
multiLangRedirect('/blog/kolaza–pati-labaka-davana', '/blog/collage-with-children-the-best-gift-for-parents-and-children');
multiLangRedirect('/blog/kollaaž-on-parim-kingitus-vanematele-ja-nende', '/blog/collage-with-children-the-best-gift-for-parents-and-children');
multiLangRedirect('/blog/Koliažas-ant-drobės', '/blog/collage-with-children-the-best-gift-for-parents-and-children');
multiLangRedirect('/blog/kolaże-z-dziećmi-najlepszy-prezent-dla-rodziców-i-ich-dzieci', '/blog/collage-with-children-the-best-gift-for-parents-and-children');
multiLangRedirect('/blog/collage-mit-kindern-das-beste-geschenk-für-eltern-und-ihre-kinder', '/blog/collage-with-children-the-best-gift-for-parents-and-children');
multiLangRedirect('/blog/druzheskij-sharzh-otlichnyj-sposob-poveselit-druzej-i-znakomyh', '/blog/friendly-caricature-is-a-great-way-to-have-fun-with-friends-and-acquaintances');
multiLangRedirect('/blog/karikatura-pec-pasutijuma', '/blog/friendly-caricature-is-a-great-way-to-have-fun-with-friends-and-acquaintances');
multiLangRedirect('/blog/sõbralik-koomiks-on-suurepärane-viis-sõprade-ja-tuttavate-lõbustamiseks', '/blog/friendly-caricature-is-a-great-way-to-have-fun-with-friends-and-acquaintances');
multiLangRedirect('/blog/-12', '/blog/friendly-caricature-is-a-great-way-to-have-fun-with-friends-and-acquaintances');
multiLangRedirect('/blog/wspólna-karykatura-to-świetny-sposób-na-przyjemną-zabawę-z-przyjaciółmi-i-znajomymi', '/blog/friendly-caricature-is-a-great-way-to-have-fun-with-friends-and-acquaintances');
multiLangRedirect('/blog/freundliche-karikatur-ist-ein-guter-weg-um-spaß-mit-freunden-und-bekannten-zu-haben', '/blog/friendly-caricature-is-a-great-way-to-have-fun-with-friends-and-acquaintances');
multiLangRedirect('/blog/fotokartina-luchshij-podarok-roditelyam', '/blog/photo-on-canvas-is-the-best-gift-to-parents');
multiLangRedirect('/blog/labaka-davana-vecakiem', '/blog/photo-on-canvas-is-the-best-gift-to-parents');
multiLangRedirect('/blog/fotopilt-on-parim-kingitus-vanematele', '/blog/photo-on-canvas-is-the-best-gift-to-parents');
multiLangRedirect('/blog/fotopaveikslas', '/blog/photo-on-canvas-is-the-best-gift-to-parents');
multiLangRedirect('/blog/fotoobraz-jest-najlepszym-prezentem-dla-rodziców', '/blog/photo-on-canvas-is-the-best-gift-to-parents');
multiLangRedirect('/blog/modulnye-kartiny-neobychnoe-i-interesnoe-reshenie-dlya-interera', '/blog/modular-paintings-an-unusual-and-interesting-interior-solution');
multiLangRedirect('/blog/moduļu-kanvas-generators', '/blog/modular-paintings-an-unusual-and-interesting-interior-solution');
multiLangRedirect('/blog/moodulmaalid-ebatavaline-ja-huvitav-lahendus-interjööri-jaoks', '/blog/modular-paintings-an-unusual-and-interesting-interior-solution');
multiLangRedirect('/blog/Moduliniai-paveikslai', '/blog/modular-paintings-an-unusual-and-interesting-interior-solution');
multiLangRedirect('/blog/obrazy-modułowe', '/blog/modular-paintings-an-unusual-and-interesting-interior-solution');
multiLangRedirect('/blog/modulare-malerei-eine-ungewöhnliche-und-interessante-einrichtungslösung', '/blog/modular-paintings-an-unusual-and-interesting-interior-solution');
multiLangRedirect('/blog/kanvas_ pamatā _pigments_vai_šķīdinātāja_tinte_kura_ir_labāka', '/blog/pigment-or-solvent-ink-what-is-better');
multiLangRedirect('/blog/pigment-või-lahustivärvil-põhinevad-maalid-millised-neist-on-paremad', '/blog/pigment-or-solvent-ink-what-is-better');
multiLangRedirect('/blog/pigmentiniai-ar-tirpikliniai-paveikslai', '/blog/pigment-or-solvent-ink-what-is-better');
multiLangRedirect('/blog/kartiny-na-osnove-pigmentnyh-ili-solventnyh-chernil-kakie-luchshe', '/blog/pigment-or-solvent-ink-what-is-better');
multiLangRedirect('/blog/obrazy-wykonane-farbami', '/blog/pigment-or-solvent-ink-what-is-better');
multiLangRedirect('/blog/Pigment- oder Lösemitteltinte - was ist besser', '/blog/pigment-or-solvent-ink-what-is-better');
multiLangRedirect('/blog/graficheskie-portrety-iskusstvo-sovremennogo-mira', '/blog/graphic-portraits-the-art-of-the-modern-world');
multiLangRedirect('/blog/portreti-pēc-fotografījas', '/blog/graphic-portraits-the-art-of-the-modern-world');
multiLangRedirect('/blog/graafilised-portreed-kaasaegse-maailma-kunst', '/blog/graphic-portraits-the-art-of-the-modern-world');
multiLangRedirect('/blog/-4', '/blog/graphic-portraits-the-art-of-the-modern-world');
multiLangRedirect('/blog/portrety-graficzne-sztuka-nowoczesności.', '/blog/graphic-portraits-the-art-of-the-modern-world');
multiLangRedirect('/blog/grafische-porträts-der-kunst-der-modernen-welt', '/blog/graphic-portraits-the-art-of-the-modern-world');
multiLangRedirect('/blog/zabawny-portret-w-stylu', '/blog/portrait-in-the-style-of-the-simpsons');
multiLangRedirect('/blog/lustiges-simpsons-stil-porträt-werden-einer-der-helden-des-legendären-cartoons', '/blog/portrait-in-the-style-of-the-simpsons');
multiLangRedirect('/blog/zabavnyj-portret-v-stile-simpsony-stan-odnim-iz-geroev-legendarnogo-multseriala', '/blog/portrait-in-the-style-of-the-simpsons');
multiLangRedirect('/blog/portrets-uz-kanvas -simpsonu-stilā', '/blog/portrait-in-the-style-of-the-simpsons');
multiLangRedirect('/blog/naljakas-portree-stiilis-simpsonid-saage-üheks-legendaarse-animasarja-kangelaseks', '/blog/portrait-in-the-style-of-the-simpsons');
multiLangRedirect('/blog/simpsonu-stiliaus-portretas', '/blog/portrait-in-the-style-of-the-simpsons');
multiLangRedirect('/blog/portret-z-charakterem', '/blog/royal-historical-portrait');
multiLangRedirect('/blog/porträt-in-der-similitude-mehr-als-nur-eine-fotomontage', '/blog/royal-historical-portrait');
multiLangRedirect('/blog/portret-v-obraze-nechto-bolshee-chem-prosto-fotomontazh', '/blog/royal-historical-portrait');
multiLangRedirect('/blog/portrets_vesturiska_kostima', '/blog/royal-historical-portrait');
multiLangRedirect('/blog/portree-pildil-on-midagi-enamat-kui-lihtsalt-fotomontaaž', '/blog/royal-historical-portrait');
multiLangRedirect('/blog/ivaizdzio-portretas', '/blog/royal-historical-portrait');
multiLangRedirect('/blog/karta-podarunkowa', '/blog/gift-card-for-canvas-paintings-and-portraits');
multiLangRedirect('/blog/eine-geschenk-karte-ist-ein-gutes-geschenk-für-einen-anlass', '/blog/gift-card-for-canvas-paintings-and-portraits');
multiLangRedirect('/blog/podarochnaya-karta-velikolepnyj-prezent-na-lyuboj-sluchaj', '/blog/gift-card-for-canvas-paintings-and-portraits');
multiLangRedirect('/blog/kinkekaart-on-suurepärane-kingitus-igal-juhul', '/blog/gift-card-for-canvas-paintings-and-portraits');
multiLangRedirect('/blog/dovanu-kortele-puiki-dovana', '/blog/gift-card-for-canvas-paintings-and-portraits');
multiLangRedirect('/blog/tworzenie-realistycznych-obrazów-za-pomocą-wydruków-cyfrowych-i-olejów', '/blog/creating-realistic-paintings-using-digital-printing-and-oil');
multiLangRedirect('/blog/Realistische-Bilder-erstellen-mit-Digitaldruck-und-Öl', '/blog/creating-realistic-paintings-using-digital-printing-and-oil');
multiLangRedirect('/blog/sozdanie-realistichnyh-kartin-pri-pomoshchi-cifrovoj-pechati-i-masla', '/blog/creating-realistic-paintings-using-digital-printing-and-oil');
multiLangRedirect('/blog/digitāla-druka-un-eļļa.', '/blog/creating-realistic-paintings-using-digital-printing-and-oil');
multiLangRedirect('/blog/looge-digitaalse-printimise-ja-õli-abil-elutruud-maalid', '/blog/creating-realistic-paintings-using-digital-printing-and-oil');
multiLangRedirect('/blog/-6', '/blog/creating-realistic-paintings-using-digital-printing-and-oil');
multiLangRedirect('/blog/bagetnoe-obramlenie-kartin', '/blog/baguette-framing-of-paintings-relics-of-the-past-or-still-relevant');
multiLangRedirect('/blog/Gleznu-bagetes-ieramešana', '/blog/baguette-framing-of-paintings-relics-of-the-past-or-still-relevant');
multiLangRedirect('/blog/maalide-baguette-raamimine', '/blog/baguette-framing-of-paintings-relics-of-the-past-or-still-relevant');
multiLangRedirect('/blog/Bagetinis-paveikslų-rėminimas', '/blog/baguette-framing-of-paintings-relics-of-the-past-or-still-relevant');
multiLangRedirect('/blog/ramki-do-obrazów', '/blog/baguette-framing-of-paintings-relics-of-the-past-or-still-relevant');
multiLangRedirect('/blog/Baguette-Rahmen-aus-Gemälden-Relikte-aus-der-Vergangenheit-oder-noch-relevant', '/blog/baguette-framing-of-paintings-relics-of-the-past-or-still-relevant');
multiLangRedirect('/blog/reprodukciya-legendarnyh-kartin-na-holste-v-vysokom-kachestve', '/blog/reproduction-of-legendary-paintings-on-canvas-in-high-quality');
multiLangRedirect('/blog/leģendāras-gleznas-reprodukcijas', '/blog/reproduction-of-legendary-paintings-on-canvas-in-high-quality');
multiLangRedirect('/blog/legendaarsete-maalide-reprodutseerimine-lõuendil-kvaliteetselt', '/blog/reproduction-of-legendary-paintings-on-canvas-in-high-quality');
multiLangRedirect('/blog/aukstos-kokybės-reprodukcija', '/blog/reproduction-of-legendary-paintings-on-canvas-in-high-quality');
multiLangRedirect('/blog/reprodukcja-legendarnych-obrazów', '/blog/reproduction-of-legendary-paintings-on-canvas-in-high-quality');
multiLangRedirect('/blog/Reproduktion-der-Legendenmalerei-auf-Leinwand-in-hochwertiger-Qualität', '/blog/reproduction-of-legendary-paintings-on-canvas-in-high-quality');
multiLangRedirect('/blog/Podarochnaya upakovka', '/blog/gift-packaging-for-paintings');
multiLangRedirect('/blog/gleznas-uz-audekla-dāvanu-iesaiņojums', '/blog/gift-packaging-for-paintings');
multiLangRedirect('/blog/kinkepakk', '/blog/gift-packaging-for-paintings');
multiLangRedirect('/blog/dovanu-pakuote', '/blog/gift-packaging-for-paintings');
multiLangRedirect('/blog/opakowanie-prezentowe', '/blog/gift-packaging-for-paintings');
multiLangRedirect('/blog/Podarotschnaja-upakowka', '/blog/gift-packaging-for-paintings');
multiLangRedirect('/blog/portraitdrawing', '/blog/how-do-we-draw-a-graphic-portrait');
multiLangRedirect('/blog/portrets-pēc-foto – kā-dāvana', '/blog/how-do-we-draw-a-graphic-portrait');
multiLangRedirect('/blog/kuidas-joonistatakse-graafiline-portree', '/blog/how-do-we-draw-a-graphic-portrait');
multiLangRedirect('/blog/-9', '/blog/how-do-we-draw-a-graphic-portrait');
multiLangRedirect('/blog/jak-powstaje-portret-graficzny', '/blog/how-do-we-draw-a-graphic-portrait');
multiLangRedirect('/blog/kak-risuetsya-graficheskiy-portret', '/blog/how-do-we-draw-a-graphic-portrait');
multiLangRedirect('/blog/zdjęcia-na-płótnie', '/blog/photos-on-canvas');
multiLangRedirect('/blog/Fotos auf Leinwand', '/blog/photos-on-canvas');
multiLangRedirect('/blog/quality-print-on-canvas', '/blog/photos-on-canvas');
multiLangRedirect('/blog/kanvas-apdruka-foto', '/blog/photos-on-canvas');
multiLangRedirect('/blog/fotod-fotosessioonist-õuendil', '/blog/photos-on-canvas');
multiLangRedirect('/blog/Paveikslas-iš-nuotraukos-ant-drobės', '/blog/photos-on-canvas');
multiLangRedirect('/blog/portret-w-stylu-pupila', '/blog/portrait-of-a-pet');
multiLangRedirect('/blog/Haustierporträt Aristokraten', '/blog/portrait-of-a-pet');
multiLangRedirect('/blog/pet-portrait-on-canvas', '/blog/portrait-of-a-pet');
multiLangRedirect('/blog/mājdzīvnieka-portrets', '/blog/portrait-of-a-pet');
multiLangRedirect('/blog/portree-stiilis-lemmikloomad-aristokraadid', '/blog/portrait-of-a-pet');
multiLangRedirect('/blog/augintinio-portretas', '/blog/portrait-of-a-pet');
multiLangRedirect('/blog/photo-canvas-with-high-quality-printing', '/blog/foto-uz-kanvas');
multiLangRedirect('/blog/canvas-or-photo-painting-transform-your-memories-into-art', '/blog/custom-canvas-prints');
