<?php

use App\Http\Controllers\Account;
use App\Http\Controllers\MyController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\BasketController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\Payment\PayPal\OneTimePayPalController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\GiftcardController;
use App\Http\Controllers\Payment\OrderPaymentRequestController as PublicOrderPaymentRequestController;
use App\Http\Controllers\Pages\FaqController;
use App\Http\Controllers\UserManageController;
use App\Http\Controllers\AdvertisingController;
use App\Http\Controllers\Pages\SharjController;
use App\Http\Controllers\StaticPagesController;
use App\Http\Controllers\Pages\ReviewController;
use App\Http\Controllers\PortraitPageController;
use App\Http\Controllers\AbandonedCartController;
use App\Http\Controllers\Admin\ImageGenController;
use App\Http\Controllers\Pages\SimpsonsController;
use App\Http\Controllers\Pages\ConditionController;
use App\Http\Controllers\Pages\SizespricesController;
use App\Http\Controllers\Libwebtopay\PayseraController;
use App\Http\Controllers\Admin\Api\VinepakApiController;
use App\Http\Controllers\Pages\ModulegeneratorController;
use App\Http\Controllers\Pages\PagePortraitOilController;
use App\Http\Controllers\Pages\PagePortraitRoyalController;
use App\Http\Controllers\Account\AccountController as NewAccountController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Storage;
//Route::get('/err_sizes', function () {
//    return view('errors.err_sizes');
//});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
Route::get('/password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
Route::post('/password/confirm', [ConfirmPasswordController::class, 'confirm']);

$localizedPasswordResetLocales = array_diff(
    array_keys(config('laravellocalization.supportedLocales', [])),
    [config('app.locale', 'ru')]
);
$localizedPasswordResetLocalePattern = implode('|', array_map('preg_quote', $localizedPasswordResetLocales));

Route::get('/{locale}/password/reset/{token}', [ResetPasswordController::class, 'showLocalizedResetForm'])
    ->where('locale', $localizedPasswordResetLocalePattern)
    ->name('password.reset.localized');
Route::post('/{locale}/password/reset', [ResetPasswordController::class, 'resetLocalized'])
    ->where('locale', $localizedPasswordResetLocalePattern)
    ->name('password.update.localized');

if (config('app.admin_enabled', false)) {
    require_once __DIR__ . '/admin.php';
}
require_once __DIR__ . '/redirect.php';

Route::get('/storage/{path}', function ($path) {
    $path = str_replace('\\', '/', $path);
    $path = ltrim($path, '/');
    $path = str_replace(['../', '..\\'], '', $path);
    if (stripos($path, 'http://') === 0 || stripos($path, 'https://') === 0) {
        $theme = config('theme.current') ?: 'theme/viar';
        $fallback = public_path($theme . '/images/no_image.png');
        if (!file_exists($fallback)) {
            abort(404);
        }
        return response()->file($fallback);
    }

    if (Storage::disk('public')->exists($path)) {
        return response()->file(Storage::disk('public')->path($path));
    }

    $theme = config('theme.current') ?: 'theme/viar';
    $fallback = public_path($theme . '/images/no_image.png');
    if (!file_exists($fallback)) {
        abort(404);
    }

    return response()->file($fallback);
})->where('path', '.*');

Route::get('/uploads/{path}', function ($path) {
    $path = str_replace('\\', '/', $path);
    $path = ltrim($path, '/');
    $path = str_replace(['../', '..\\'], '', $path);

    $publicPath = public_path('uploads/' . $path);
    if (file_exists($publicPath)) {
        return response()->file($publicPath);
    }

    $theme = config('theme.current') ?: 'theme/viar';
    $fallback = public_path($theme . '/images/no_image.png');
    if (!file_exists($fallback)) {
        abort(404);
    }

    return response()->file($fallback);
})->where('path', '.*');

Route::get('/orders/{path}', function ($path) {
    $path = str_replace('\\', '/', $path);
    $path = ltrim($path, '/');
    $path = str_replace(['../', '..\\'], '', $path);

    $publicPath = public_path('orders/' . $path);
    if (file_exists($publicPath)) {
        return response()->file($publicPath);
    }

    $theme = config('theme.current') ?: 'theme/viar';
    $fallback = public_path($theme . '/images/no_image.png');
    if (!file_exists($fallback)) {
        abort(404);
    }

    return response()->file($fallback);
})->where('path', '.*');

Route::get('/auth/facebook/redirect', [SocialController::class, 'facebook_redirect'])->name('facebook_redirect');
Route::get('/auth/facebook/callback', [SocialController::class, 'facebook_callback'])->name('facebook_callback');
Route::get('/auth/google/redirect', [SocialController::class, 'google_redirect'])->name('google_redirect');
Route::get('/auth/google/callback', [SocialController::class, 'google_callback'])->name('google_callback');

Route::post('/admin/check-user', [AccountController::class, 'ajax_check_user']);


Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['resetPlLocale', 'localize', 'localeSessionRedirect', 'localizationRedirect', 'lastModified'],
    ],
    function () {

        Route::get('robots.txt', [RobotsController::class, 'index'])->name('robots.txt');
        //painter
//        Route::get('/test/auth/painter', function() {
//            \Auth::loginUsingId(580);
//        });

        // user
//        Route::get('/test/auth/client', function() {
//            \Auth::loginUsingId(1728);
//        });

        /** Facebook OAuth routes */
        Route::get('/save_base64_image2', [BasketController::class, 'save_base64_image2']); //пересохраняет фотку с параметра в коллаже с base64

        Route::any('/image_gen_all', [ImageGenController::class, 'image_gen_all'])->name('image_gen_all');

        Route::get('/mail/test', [MailController::class, 'index']);
        Route::get('/mail/gift_cart', [MailController::class, 'gift_cart']);
        Route::get('/mail/abandoned_cart', [MailController::class, 'abandoned_cart']);
        Route::get('/mail/abandoned_cart24', [MailController::class, 'abandoned_cart24']);
        Route::get('/mail/order', [MailController::class, 'order']);

        Route::get('/mail/1/', function () {
            return view('mail_new.1.1');
        });

        Route::get('/mail/2/', function () {
            return view('mail_new.1.2');
        });

        Route::get('/mail/3/', function () {
            return view('mail_new.1.3');
        });

        Route::get('/mail/4/', function () {
            return view('mail_new.1.4');
        });

        Route::get('/mail/5/', function () {
            return view('mail_new.1.5');
        });

        Route::get('/mail/6/', function () {
            return view('mail_new.1.6');
        });

        Route::get('/mail/7/', function () {
            return view('mail_new.1.7');
        });

        Route::get('faq', [FaqController::class, 'index'])->name('faq'); //https://www.figma.com/proto/g7NrKxoyPwlzbUwJRrlYDQ/Untitled?node-id=1168-31
        Route::get('condition', [ConditionController::class, 'index'])->name('condition'); //https://www.figma.com/proto/g7NrKxoyPwlzbUwJRrlYDQ/Untitled?node-id=1172-57


        Route::get('simpsons', [SimpsonsController::class, 'index'])->name('simpsons');
        Route::get('/new/caricature', [SharjController::class, 'index'])->name('caricature');
        Route::get('/new/caricature/{pageslug}', [SharjController::class, 'index'])->name('sub_caricature');


        Route::get('sizesprices', [SizespricesController::class, 'index'])->name('sizesprices'); //https://www.figma.com/proto/g7NrKxoyPwlzbUwJRrlYDQ/Untitled?node-id=1172-534

        Route::get('review', [ReviewController::class, 'index'])->name('review'); //https://www.figma.com/proto/1JwO1ukr9PzfwGFn2pFI1X/Untitled-(Copy)?type=design&node-id=437-572&scaling=min-zoom&page-id=0%3A1 //https://www.figma.com/file/JTVGxVPmEyCubUMlvPQ52l/%D0%9E%D1%81%D1%82%D0%B0%D0%B2%D0%B8%D1%82%D1%8C-%D0%BE%D1%82%D0%B7%D1%8B%D0%B2?type=design&node-id=0-1&t=FitSjY1mLn6iYbyn-0
        Route::post('review', [ReviewController::class, 'store'])->name('review.store');

        // Route::get('giftcard', [GiftcardController::class, 'index'])->name('giftcard'); //https://www.figma.com/proto/1zhN8SunBk3I3V31mGD5NO/Untitled?node-id=1509-499&scaling=scale-down-width
        Route::get('modular-generator', [ModulegeneratorController::class, 'index'])->name('modular-generator'); //https://www.figma.com/proto/1zhN8SunBk3I3V31mGD5NO/Untitled?type=design&node-id=1314-56&scaling=scale-down-width&page-id=1222%3A8

        Route::get('paysera', [PayseraController::class, 'index'])->name('paysera');
        Route::get('pay_accept', [PayseraController::class, 'pay_accept'])->name('pay_accept');
        Route::get('pay_cancel', [PayseraController::class, 'pay_cancel'])->name('pay_cancel');
        Route::get('pay_callback', [PayseraController::class, 'pay_callback'])->name('pay_callback');

        Route::group(
            ['prefix' => 'paypal'],
            function () {
                Route::any('pay_accept', [OneTimePayPalController::class, 'pay_accept'])->name('paypal.onetime.pay_accept');
                Route::any('pay_cancel', [OneTimePayPalController::class, 'pay_cancel'])->name('paypal.onetime.pay_cancel');
            }
        );

        Route::get('/payment-request/{token}', [PublicOrderPaymentRequestController::class, 'show'])->name('payment_request.show');
        Route::post('/payment-request/{token}', [PublicOrderPaymentRequestController::class, 'start'])->name('payment_request.start');
        Route::any('/payment-request/paysera/accept', [PublicOrderPaymentRequestController::class, 'payseraAccept'])->name('payment_request.paysera.accept');
        Route::any('/payment-request/paysera/cancel', [PublicOrderPaymentRequestController::class, 'payseraCancel'])->name('payment_request.paysera.cancel');
        Route::any('/payment-request/paysera/callback', [PublicOrderPaymentRequestController::class, 'payseraCallback'])->name('payment_request.paysera.callback');
        Route::group(
            ['prefix' => 'payment-request/paypal'],
            function () {
                Route::any('pay_accept', [PublicOrderPaymentRequestController::class, 'paypalAccept'])->name('payment_request.paypal.accept');
                Route::any('pay_cancel', [PublicOrderPaymentRequestController::class, 'paypalCancel'])->name('payment_request.paypal.cancel');
            }
        );

        Route::get('change_phones', [MyController::class, 'change_phones'])->name('change_phones');
        Route::get('change_delivery_phones', [MyController::class, 'change_delivery_phones'])->name('change_delivery_phones');


        /** API */
        Route::post('get_towns', [VinepakApiController::class, 'get_towns'])->name('get_towns');
        Route::post('get_towns_for_admin', [VinepakApiController::class, 'get_towns_for_admin'])->name('get_towns_for_admin');
        Route::get('get_warehouse', [VinepakApiController::class, 'get_warehouse'])->name('get_warehouse');

        /** Login& register */
        Route::post('custom_login_ajax', [UserManageController::class, 'custom_login_ajax'])
            ->middleware('throttle:10,1')
            ->name('custom_login_ajax');
        Route::post(
            'custom_register_ajax',
            [UserManageController::class, 'custom_register_ajax']
        )->middleware('throttle:5,1')->name('custom_register_ajax');

        Route::post(
            'send_photo_portrait_form',
            [UserManageController::class, 'send_photo_portrait_form']
        )->name('send_photo_portrait_form');


        Route::post(
            'send_photo_form',
            [UserManageController::class, 'send_photo_form']
        )->name('send_photo_form');


        Route::get('/', [IndexController::class, 'hbrender'])->name('home');
        // Route::get('/', [IndexController::class, 'render_new'])->name('home');

        Route::get('/all_styles', [PageController::class, 'render_all_styles'])->name('all_styles');
        Route::get('/photo_portrait', [PageController::class, 'photo_portrait'])->name('photo_portrait');

        Route::post(
            '/all_styles_form',
            [UserManageController::class, 'send_all_styles_form']
        )->name('send_all_styles_form');
        Route::post('/user/send_photo_form', [UserManageController::class, 'send_photo_form'])->name('send_photo_form');

        Route::middleware(['auth'])->group(function () {
            Route::get('/account', [AccountController::class, 'render'])->name('account.index');

            Route::post('/update_painter_order_images', [AccountController::class, 'update_painter_order_images'])->name('update_painter_order_images');
            Route::post('/update_painter_sketch_order_images', [AccountController::class, 'update_painter_sketch_order_images'])->name('update_painter_sketch_order_images');
            Route::post('/update_painter_comment', [AccountController::class, 'update_painter_comment'])->name('update_painter_comment');
            Route::post('/update_order_chat', [AccountController::class, 'update_order_chat'])->name('update_order_chat');
            Route::post('/send_client_painter_comments',[AccountController::class, 'send_client_painter_comments'])->name('send_client_painter_comments');
            Route::post('/ajax_change_information', [AccountController::class, 'ajax_change_information'])->name("account.ajax_change_information");
        });

        Route::middleware(['auth'])->group(function () {
            Route::get('/new/account', [NewAccountController::class, 'index'])->name('new_account.index');
            Route::get('/new/orders', [NewAccountController::class, 'orders'])->name('new_account.orders');
            Route::get('/new/mystocks', [NewAccountController::class, 'mystocks'])->name('new_account.mystocks');
            Route::get('/new/settings', [NewAccountController::class, 'settings'])->name('new_account.settings');

            Route::get('/new/unpaid', [NewAccountController::class, 'unpaid'])->name('new_account.unpaid');
            Route::get('/new/paid', [NewAccountController::class, 'paid'])->name('new_account.paid');
            Route::get('/new/orders-success', [NewAccountController::class, 'orders_success'])->name('new_account.orders_success');
            Route::get('/new/orders/{order}/payment', [NewAccountController::class, 'order_payment'])->name('new_account.order_payment');
            Route::post('/new/orders/{order}/payment', [NewAccountController::class, 'start_order_payment'])->name('new_account.order_payment.start');
            Route::post('/new/ajax_change_information', [NewAccountController::class, 'ajax_change_information'])->name("new_account.ajax_change_information");

            Route::post('/new/update_painter_order_images', [NewAccountController::class, 'update_painter_order_images'])->name('new_update_painter_order_images');
            Route::post('/new/update_painter_sketch_order_images', [NewAccountController::class, 'update_painter_sketch_order_images'])->name('new_update_painter_sketch_order_images');
            Route::post('/new/update_order_chat', [NewAccountController::class, 'update_order_chat'])->name('new_update_order_chat');
            Route::post('/new/new_send_client_painter_comments',[NewAccountController::class, 'send_client_painter_comments'])->name('new_send_client_painter_comments');
            Route::post('/new/new_send_admin_to_client_painter_comments',[NewAccountController::class, 'send_admin_to_client_painter_comments'])->name('new_send_admin_to_client_painter_comments');
            Route::post('/new/message_read',[NewAccountController::class, 'message_read'])->name('new_message_read');
            Route::post('/new/admin_to_client_message_read',[NewAccountController::class, 'admin_to_client_message_read'])->name('admin_to_client_message_read');
            Route::post('/new/admin_message_read',[NewAccountController::class, 'admin_message_read'])->name('new_admin_message_read');
            Route::get('/new/set_all_painter_images',[NewAccountController::class, 'set_all_painter_images'])->name('set_all_painter_images');
            Route::post('/new/changeOrderPainterImageStatus', [NewAccountController::class, 'changeOrderPainterImageStatus'])->name('changeOrderPainterImageStatus');
        });



        Route::get('/user/{user_id}/approve_checkout/{order_id}', [OrdersController::class, 'approve_user_checkout']);
        Route::get('/generate_checkout/{order_id}', [OrdersController::class, 'generate_checkout'])->name('generate_checkout');
        Route::get('/user/send_review', [ReviewController::class, 'index'])->name('send_rev');

        Route::get('/user/{id}/unsubscribe', [UserManageController::class, 'unsubscribe']);
        Route::post('/user_send_rev/{locale}', [UserManageController::class, 'user_send_rev'])->name('user_send_rev');
        Route::post('/user/forget_email', [UserManageController::class, 'reset_password'])->name('forget_email_reset');

        /** all stocks */

        /// TODO: 30-40 Акция, выполняется при submit формы
        Route::post('/user/send_screen', [UserManageController::class, 'send_screen'])->name('send_screen');
        /// TODO: 2 даты акция , выполняется при submit формы
        Route::post('/user/send_dates', [UserManageController::class, 'send_dates'])->name('send_dates');



        Route::post('/user/send_free_image', [UserManageController::class, 'send_free_image'])->name('user_free_img');

        /** pages */

        Route::get('/page/contacts', [PageController::class, 'hb_render_contacts'])->name('contacts');

        Route::get('/page/partnership', [PageController::class, 'render_partnership']);
        Route::get('/page/delivery', [PageController::class, 'render_delivery'])->name('delivery_page');
        Route::get('/page/{page}', [PageController::class, 'render']);

        Route::get('/set_sizes', [GalleryController::class, 'hb_set_sizes']);
        Route::get('/set_genre', [GalleryController::class, 'hb_set_genre']);
        Route::get('/set_style', [GalleryController::class, 'hb_set_style']);

        Route::get('/get/ram_search', [GalleryController::class, 'ram_search'])->name('hb.gallery.ram_search');
        Route::post('/get/ram_search', [GalleryController::class, 'ram_search'])->name('hb.gallery.ram_search');
        Route::get('/gallery/painters', [GalleryController::class, 'painters'])->name('hb.gallery.painters');
        Route::get('/gallery/painters/{letter}', [GalleryController::class, 'painters'])->name('hb.gallery.painters_letter');
        Route::get('/gallery/paintings-top', [GalleryController::class, 'paintings_top'])->name('hb.gallery.paintings_top');
        Route::get('/gallery/modern_handmade_paintings', [GalleryController::class, 'modern_handmade_paintings'])->name('hb.gallery.modern_handmade_paintings');
        Route::get('/gallery/modern_painters', [GalleryController::class, 'modern_painters'])->name('hb.gallery.modern_painters');
        Route::get('/gallery/age', [GalleryController::class, 'list_age'])->name('hb.gallery.list_age');
        Route::get('/gallery/age/{alias}', [GalleryController::class, 'age'])->name('hb.gallery.age');
        Route::get('/gallery/nationality', [GalleryController::class, 'list_nationality'])->name('hb.gallery.list_nationality');
        Route::get('/gallery/nationality/{alias}', [GalleryController::class, 'nationality'])->name('hb.gallery.nationality');
        Route::get('/gallery/style', [GalleryController::class, 'list_styles'])->name('hb.gallery.list_styles');
        Route::get('/gallery/style/{alias}', [GalleryController::class, 'style'])->name('hb.gallery.style');
        Route::get('/gallery/genre', [GalleryController::class, 'list_genre'])->name('hb.gallery.list_genre');
        Route::get('/gallery/genre/{alias}', [GalleryController::class, 'genre'])->name('hb.gallery.genre');

        Route::get('/new/gallery', [GalleryController::class, 'hb_render'])->name('hb.gallery.index');
        Route::get('/new/gallery/{type}', [GalleryController::class, 'hb_type_render'])->name('hb.gallery.module');
        Route::get('/new/gallery/{type}/{category}', [GalleryController::class, 'hb_type_render'])->name('hb.gallery.category');
        Route::get('/gallery/{type}/item/{item}', [GalleryController::class, 'hb_item_render'])->name('hb.gallery.item.single');

        Route::get('/gallery', [GalleryController::class, 'render'])->name('gallery.index');
        Route::get('/gallery/{type}', [GalleryController::class, 'type_render'])->name('gallery_module');
        Route::get('/gallery/{type}/{category}', [GalleryController::class, 'category_render'])->name('gallery.category');
        Route::get('/gallery/{type}/{category}/item/{item}', [GalleryController::class, 'item_render'])->name('gallery_item_single');
        // Route::get('/gallery/{type}/item/{id}', [GalleryController::class, 'item_render_single']);

        Route::get('/google-ads.xml', [AdvertisingController::class, 'google'])->name('google.ads');
        Route::get('/kurpirkt.xml', [AdvertisingController::class, 'kurpirkt'])->name('kurpirkt');
        Route::get('/salidzini.xml', [AdvertisingController::class, 'salidzini'])->name('kurpirkt');
        Route::get('/lv-google-ads.xml', [AdvertisingController::class, 'google_lv'])->name('google.ads.lv');
        Route::get('/lt-google-ads.xml', [AdvertisingController::class, 'google_lt'])->name('google.ads.lt');
        Route::get('/ee-google-ads.xml', [AdvertisingController::class, 'google_ee'])->name('google.ads.ee');
        Route::get('/translate_item', [AdvertisingController::class, 'translate_item'])->name('translate_item');
        Route::get('/sitemap_products.xml', [AdvertisingController::class, 'sitemap_products'])->name('sitemap_products');
        Route::get('/set_meta', [AdvertisingController::class, 'set_meta'])->name('set_meta');

        /** basket */
        Route::post('/get_cart', [BasketController::class, 'get_cart'])->name('get_cart')->middleware('preventBackHistory');
        Route::post('/cart/delimage', [BasketController::class, 'delimage'])->name('delimage')->middleware('preventBackHistory');
        Route::post('/cart/updateimg', [BasketController::class, 'updateimg'])->name('updateimg')->middleware('preventBackHistory');
        Route::post('/cart/setmaking', [BasketController::class, 'setmaking'])->name('setmaking')->middleware('preventBackHistory');
        Route::post('/cart/setcoupon', [BasketController::class, 'setcoupon'])->name('setcoupon')->middleware('preventBackHistory');
        Route::post('/cart/setdelivery', [BasketController::class, 'setdelivery'])->name('setdelivery')->middleware('preventBackHistory');
        Route::post('/cart/setuser', [BasketController::class, 'setuser'])->name('setuser')->middleware('preventBackHistory');
        Route::post('/cart/setpay', [BasketController::class, 'setpay'])->name('setpay')->middleware('preventBackHistory');
        Route::get('/save_order_and_pay', [OrdersController::class, 'save_order_and_pay'])->name('save_order_and_pay');
        Route::get('/cart/clear_coupon', [BasketController::class, 'clearcart'])->name('clearcart')->middleware('preventBackHistory');
        Route::get('/cart', [BasketController::class, 'cart'])->name('cart.index')->middleware('preventBackHistory');
        Route::get('/cart/data', [BasketController::class, 'cart_step2'])->name('cart.step2')->middleware('preventBackHistory');
        Route::get('/cart/delivery', [BasketController::class, 'cart_step3'])->name('cart.step3')->middleware('preventBackHistory');
        Route::get('/cart/payment', [BasketController::class, 'cart_step4'])->name('cart.step4')->middleware('preventBackHistory');
        Route::post('/cart/replace-size', [BasketController::class, 'replaceItemSize'])->name('cart.replace.size')->middleware('preventBackHistory');
        Route::post('/cart/add-recommended', [BasketController::class, 'addRecommendedItem'])->name('cart.add.recommended')->middleware('preventBackHistory');
        Route::get('/basket', [BasketController::class, 'render'])->name('basket.index')->middleware('preventBackHistory');
        Route::get('/basket/thanks', [BasketController::class, 'renderThanksPage'])->middleware('preventBackHistory')->name('basket.thanks');

        Route::get('/cart/recover/{redirect}/{token}', [AbandonedCartController::class, 'processRecovery'])
            ->name('cart.recover');

        Route::post('/cart/set_email', [AbandonedCartController::class, 'setEmail'])
            ->name('cart.set-email');
        /** basket actions */
        Route::post('/basket/add', [BasketController::class, 'addToBasket'])->name('add_item_to_basket');
        Route::post('/basket/update/count', [BasketController::class, 'updateCount'])->name('update_count');
        Route::any('/basket/coupon_use', [BasketController::class, 'coupon_use'])->name('coupon_use');

        Route::post('/basket/add/portrait',[BasketController::class, 'addToBasketPortrait'])->name('add_item_to_basket_portrait');
        Route::post('/basket/add/inter', [BasketController::class, 'retiredLegacyBasketEndpoint'])->name('add_item_to_basket_inter');
        Route::post('/basket/add/module', [BasketController::class, 'retiredLegacyBasketEndpoint'])->name('add_item_to_basket_module');
        Route::post('/basket/add/construct', [BasketController::class, 'addToBasketConstruct'])->name('add_item_to_basket_construct');
        Route::post('/basket/add/future_art', [BasketController::class, 'addToBasketArt'])->name('add_future_art');
        Route::post('/basket/add/recommended', [BasketController::class, 'addRecommendedToBasket'])->name('basket.add_recommended');
        Route::post('/basket/add-canvas-recommendation', [BasketController::class, 'addCanvasRecommendation'])->name('basket.add_canvas_recommendation');

        Route::post('/basket/remove', [BasketController::class, 'removeFromBasket'])->name('remove_item_from_basket');

        Route::post('/basket/submitbonuses', [BasketController::class, 'submitBonuses'])->name('submit_bonuses');

        Route::get('/basket/submitbonuses', [BasketController::class, 'submitBonuses'])->name('submit_bonuses');


        Route::any('/basket/send_gift_card', [BasketController::class, 'send_gift_card'])->name('send_gift_card');
        Route::post('/basket/update_prices', [BasketController::class, 'update_prices'])->name('update_prices');

        /** pages */
        Route::get('gift-card', [PageController::class, 'render_gift'])->name('gift_card');
        Route::get('/new/gift-card', [PageController::class, 'render_gift_new'])->name('gift_card_new');
        // Предпросмотр подарочной карты (отображает HTML)
        Route::get('/giftcard/preview', [GiftcardController::class, 'previewGiftCard'])->name('previewGiftCard');
        // Генерация PDF подарочной карты
        Route::any('/giftcard/pdf/{price}/{locale}', [GiftcardController::class, 'generateGiftCardPDF'])->name('generateGiftCardPDF');
        // Route::get('/giftcard/jpg/{price}/{locale}', [GiftcardController::class, 'generateGiftCardJPG'])->name('generateGiftCardJPG');


        // Route::get('/about', [PageController::class, 'render_about']);
        Route::get('/about', [PageController::class, 'hb_render_about'])->name('about');
        Route::get('/graphic-portrait', [StaticPagesController::class, 'render_graphic_portrait'])->name('graphic_portrait.index');
        Route::get('/thanks', [StaticPagesController::class, 'thanks'])->name('thanks');
        Route::get('/why_are_you_leaving_questions', [StaticPagesController::class, 'why_are_you_leaving_questions'])->name('why_are_you_leaving_questions');

        /** new/old test routes */
        /** old **/
        Route::get('/graphic-portrait/{slug}', [StaticPagesController::class, 'render_graphic_portrait_page'])->name('graphic_portrait.page');
        Route::get('/graphic-portrait/{slug}/buy',[StaticPagesController::class, 'buy_portrait_page'])->name('graphic_portrait.buy');

        Route::get('/stylization-paintings/{slug}',[StaticPagesController::class, 'render_styl_painting_page'])->name('stylization_paintings.page');
        Route::get('/stylization-paintings/{slug}/buy',[StaticPagesController::class, 'buy_portrait_page'])->name('styl_portrait.buy');
        /** new */

        //Route::get('new/graphic-portrait/{slug}', [PortraitPageController::class, 'index'])->name('graphic_portrait.new_page');

         //временно закоментил перед заливкой на основну - разкоментируй когда надо будет или переименуй маршрут пока не будет готова страница
        Route::get('new/graphic-portrait/portrait-oil', [PagePortraitOilController::class, 'index'])->name('graphic_portrait.oil');
        Route::get('new/graphic-portrait/portrait-historical', [PagePortraitRoyalController::class, 'index'])->name('graphic_portrait.new_page--royal');
        Route::get('new/graphic-portrait/{slug}', [PortraitPageController::class, 'hbindex'])->name('graphic_portrait.new_page');
        /** end routes */
        Route::get('/oil-portrait', [StaticPagesController::class, 'render_oil_portrait'])->name('oil_portrait.index');
        Route::get('/oil-portrait/buy', [StaticPagesController::class, 'buy_oil_portrait_page'])->name('oil_portrait.buy');
        Route::get('/stylization-paintings',[StaticPagesController::class, 'render_stylization_paintings'])->name('stylization_paintings.index');

        // Заменяем старую версию акций на новую
        //Route::get('/stocks', [StaticPagesController::class, 'render_stocks'])->name('stocks.index');


        Route::get('/stocks', [StaticPagesController::class, 'hb_render_stocks'])->name('new_stocks');

        Route::get('/new/stocks', [StaticPagesController::class, 'render_stocks'])->name('stocks.index');



        Route::get('/collage-constructor',[StaticPagesController::class, 'render_collage_constructor'])->name('collage_constructor.index');
        Route::get('/collage-js',[PortraitPageController::class, 'collage_js'])->name('collage_js.index');

        // Route::get('blog', [BlogController::class, 'render_blog'])->name('blog');
        Route::get('blog', [BlogController::class, 'blog'])->name('blog');
        Route::get('blog/category/all/', [BlogController::class, 'blog_category_all'])->name('blog_category_all');
        Route::get('blog/category/{slug}/', [BlogController::class, 'blog_category'])->name('blog_category');
        Route::get('/blog_article/{slug}/', [BlogController::class, 'blog_article'])->name('blog_article');
        // Route::get('/blog/{slug}/', [BlogController::class, 'render_inner_blog'])->name('blog_inner');
        Route::get('/blog/{slug}/', [BlogController::class, 'blog_article'])->name('blog_inner');

        Route::get('/family-constructor', [StaticPagesController::class, 'render_family'])->name('family_constructor');

        Route::get('/sitemap', [PageController::class, 'generate_sitemap_html'])->name('sitemap');
        Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap.main');
        Route::get('/sitemap/sitemap.xml', [SitemapController::class, 'sitemap_products'])->name('sitemap.lang');
        Route::get('/sitemap/sitemap-images.xml', [SitemapController::class, 'sitemap_products'])->name('sitemap.images');

        // Route::get('/new/canvas', [PortraitPageController::class, 'canvas'])->name('canvas');
        Route::get('/new/canvas', [PortraitPageController::class, 'hbcanvas'])->name('canvas');
        Route::get('/kanvas/apdruka', [PortraitPageController::class, 'ads_canvas'])->name('ads.canvas');
        Route::get('collage', [PortraitPageController::class, 'hbcollage'])->name('collage');
        Route::get('/{slug}', [StaticPagesController::class, 'render_slug_page'])->name('page');

        /** actions */
        Route::post('/orders/make', [OrdersController::class, 'makeOrder'])->name('make_order');
        Route::post('/orders/delete', [OrdersController::class, 'deleteOrder'])->name('delete_order');
        Route::post('/orders/approve', [OrdersController::class, 'approveOrder'])->name('approve_order');
        Route::post('/orders/change', [OrdersController::class, 'changeOrder'])->name('change_order');
        Route::post('/orders/add_client_images', [OrdersController::class, 'add_client_images'])->name('add_client_images');
        Route::get('/orders/remove_painter_sketch_image', [OrdersController::class, 'remove_painter_sketch_image'])->name('remove_painter_sketch_image');
        Route::post('/orders/changePainterSketchImagesStatus', [OrdersController::class, 'changePainterSketchImagesStatus'])->name('changePainterSketchImagesStatus');
        Route::post('/orders/changePainterImagesStatus', [OrdersController::class, 'changePainterImagesStatus'])->name('changePainterImagesStatus');
        Route::post('/orders/change/payment',[OrdersController::class, 'changeOrderPayment'])->name('change_order_payment');
        Route::post('/orders/change/vrv', [OrdersController::class, 'changeOrderVrv'])->name('change_order_vrv');
        Route::post('/orders/update/vrv', [OrdersController::class, 'update_vr_num'])->name('update_vr_num');
        Route::post('/orders/remove/vrv', [OrdersController::class, 'rem_vr_num'])->name('rem_vr_num');
        Route::post('/orders/update/painter_time',[OrdersController::class, 'update_painter_time'])->name('update_painter_time');

        Route::post('/orders/update/update_when_send_time', [OrdersController::class, 'update_when_send_time'])->name('update_when_send_time');

        Route::post('/orders/update/update_prepayment_price', [OrdersController::class, 'update_prepayment_price'])->name('update_prepayment_price');

        Route::post('/orders/update/painter_payed', [OrdersController::class, 'update_painter_payed'])->name('update_painter_payed');
        Route::post('/orders/update/show_painter_images', [OrdersController::class, 'show_painter_images'])->name('show_painter_images');
        Route::post('/orders/update/painter',[OrdersController::class, 'update_order_painter'])->name('update_order_painter');
        Route::post('/orders/update/printing',[OrdersController::class, 'update_printing_order'])->name('update_printing_order');
        Route::post('/orders/update/firm', [OrdersController::class, 'update_order_firm'])->name('update_order_firm');
        Route::post('/orders/change_client_status',[OrdersController::class, 'change_client_status'])->name('change_client_status');

        //// TODO: Акция 4 - 1 в подарок
        //// TODO: Акция 30-40
        Route::post('/stocks/create_coupon',[StaticPagesController::class, 'create_stock_coupon'])->name('create_stock_coupon');

        Route::post('/send_frend_email',[StaticPagesController::class, 'send_frend_email'])->name('send_frend_email');

        Route::post('/load_ajax_posts', [BlogController::class, 'load_ajax_posts'])->name('load_ajax_posts');


        Route::get('/image/{filename}', [ImageController::class, 'show'])->middleware('check.webp');

    }
);

Route::get('/robots.txt', [RobotsController::class, 'mainRobots'])->name('main.robots.txt');
