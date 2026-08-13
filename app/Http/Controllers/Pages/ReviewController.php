<?php

namespace App\Http\Controllers\Pages;

use DB;
use App;
use App\Models\Page;
use App\Models\User;
use App\Models\Review;
use App\Models\Orders;
use App\Models\PageFaq;
use Illuminate\Support\Arr;
use App\Models\NewhomeService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Request;
use App\Http\Requests\CreateReviewRequest;

class ReviewController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        //        $this->template = config('theme.resource') . '.index';
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $modals = "";
        
        $meta_item = Page::where('url', 'review')->first();
        $meta_item = $meta_item->translate(App::getLocale(), 'ru');

        $content = view(config('theme.resource') . 'pages.review.index')->with([
            'services'    => NewhomeService::where('is_show', 1)->withTranslation(App::getLocale(), false)->orderBy('order', 'asc')->get(),
            'faqs'        => PageFaq::where('page->review', 'review')->orderBy('sort', 'asc')->withTranslation(App::getLocale(), false)->get(),
        ]);

        $structuredData = $this->reviewStructuredData($request, $meta_item);

        $this->vars = Arr::add($this->vars, 'title', $meta_item->meta_title);
        $this->vars = Arr::add($this->vars, 'meta_desc', $meta_item->meta_description);
        $this->vars = Arr::add($this->vars, 'content', $content);
        $this->vars = Arr::add($this->vars, 'modals', $modals);
        $this->vars = Arr::add($this->vars, 'structured_data', $structuredData);

        return $this->renderOutput();
    }

    private function reviewStructuredData(\Illuminate\Http\Request $request, $metaItem): array
    {
        $url = $request->url();
        $siteName = trans('settings.site_name');
        $google = (array) config('services.google', []);
        $widget = (array) ($google['reviews_widget'] ?? []);
        $rating = isset($widget['rating']) && $widget['rating'] !== '' ? (float) $widget['rating'] : null;
        $total = isset($widget['total']) && $widget['total'] !== '' ? (int) $widget['total'] : null;
        $mapsUrl = $google['maps_url'] ?? ($widget['url'] ?? null);

        $organization = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            '@id' => url('/') . '#organization',
            'name' => $siteName,
            'url' => url('/'),
        ];

        if ($mapsUrl) {
            $organization['sameAs'] = [$mapsUrl];
        }

        if ($rating && $total) {
            $organization['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $rating,
                'bestRating' => 5,
                'reviewCount' => $total,
            ];
        }

        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                '@id' => $url . '#webpage',
                'url' => $url,
                'name' => strip_tags((string) $metaItem->meta_title),
                'description' => strip_tags((string) $metaItem->meta_description),
                'isPartOf' => [
                    '@type' => 'WebSite',
                    '@id' => url('/') . '#website',
                    'name' => $siteName,
                    'url' => url('/'),
                ],
                'about' => [
                    '@id' => url('/') . '#organization',
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => $siteName,
                        'item' => url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => strip_tags((string) $metaItem->meta_title),
                        'item' => $url,
                    ],
                ],
            ],
            $organization,
        ];
    }

    public function store(\Illuminate\Http\Request $request)
    {

        $locale = session('locale');

        $currentDateTime = date('Y-m-d H:i:s');
        // Retrieve the uploaded files
        $uploadedFiles = $request->file('file');

        // Decode the base64-encoded audio data
        if ($request->has('audioData')) {


            $audioData = $request->input('audioData');
            $decodedAudio = base64_decode(preg_replace('#^data:audio/\w+;base64,#i', '', $audioData));

            // Generate a unique filename
            $filename = uniqid('review_audio_') . '.webm';

            // Determine the storage path
            $storagePath = storage_path('app/public/review/audio/');
            if (!is_dir($storagePath)) {
                // Create the directory if it does not exist
                mkdir($storagePath, 0755, true);
            }


            // Save the audio file
            file_put_contents($storagePath . $filename, $decodedAudio);

            $databasePath = 'review/audio/' . $filename;
            $audioDB = '[{"download_link":"' . $databasePath . '","original_name": "' . $filename . '"}]';
        } else {
            $databasePath = '';
        }
        if ($uploadedFiles) {
            foreach ($uploadedFiles as $key => $uploadedFile) {
                $filePathName = $uploadedFile->store('public/review');

                $filePathName = str_replace('public/', '', $filePathName);

                if ($key == 1) {
                    $img = $filePathName;
                }
                if ($key == 2) {
                    $avatar = $filePathName;
                }
            }
        }
        if (!isset($img)) {
            $img = '';
        }
        if (!isset($avatar)) {
            $avatar = '';
        }

        // Определяем ID пользователя: сначала текущий, иначе — по email из запроса
        $userId = auth()->id();
        if (!$userId) {
            $user = User::where('email', $request->input('email'))->first();
            $userId = $user ? $user->id : null;
        }

        // Получаем последний завершённый заказ
        $orderId = 0;
        if ($userId) {
            $lastOrderId = User::getUserLastCompletedOrderId($userId);
            $orderId = $lastOrderId ?: 0;
        }

        // Извлекаем первый pid из заказа
        $pid = 0;
        if ($orderId) {
            $order = DB::table('orders')
                ->where('id', $orderId)
                ->orderBy('created_at', 'desc')
                ->first();
            $items = json_decode($order->items, true);
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (is_array($item) && isset($item['pid'])) {
                        $pid = (int) $item['pid'];
                        break;
                    }
                }
            }
        }

        $review = Review::create([
            'img'         => $img,
            'avatar'      => $avatar,
            'name'        => $request->input('name'),
            'email'       => $request->input('email'),
            'text'        => $request->input('text'),
            'active'      => 0,
            'a_player'    => $audioDB,
            'orig_locale' => $locale,
            'user_id'     => $userId,
            'order_id'    => $orderId,
            'pid'         => $pid,
        ]);

        return 'success';
    }
}
