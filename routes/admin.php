<?php

use App\Http\Controllers\Admin\ImageGenController;
use App\Http\Controllers\Admin\OrderPaymentRequestController as AdminOrderPaymentRequestController;
use App\Http\Controllers\Admin\VoyagerAdminController;
use App\Http\Controllers\Admin\OrdersController as AdminOrdersController;
use Illuminate\Support\Facades\Storage;

Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
    Route::get('user_images/{path}', function ($path) {
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        $path = str_replace(['../', '..\\'], '', $path);

        $storageRel = 'user_images/' . $path;
        if (Storage::disk('public')->exists($storageRel)) {
            return response()->file(Storage::disk('public')->path($storageRel));
        }

        $publicPath = public_path('admin/user_images/' . $path);
        if (file_exists($publicPath)) {
            return response()->file($publicPath);
        }

        $theme = config('theme.current') ?: 'theme/viar';
        $fallback = public_path($theme . '/images/no_image.png');
        if (!file_exists($fallback)) {
            abort(404);
        }

        return response()->file($fallback);
    })->where('path', '.*\\.(?:png|jpe?g|webp|gif|svg)$');

    Route::get('gallery-items/{path}', function ($path) {
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        $path = str_replace(['../', '..\\'], '', $path);

        $storageRel = 'gallery-items/' . $path;
        if (Storage::disk('public')->exists($storageRel)) {
            return response()->file(Storage::disk('public')->path($storageRel));
        }

        $publicPath = public_path('admin/gallery-items/' . $path);
        if (file_exists($publicPath)) {
            return response()->file($publicPath);
        }

        $theme = config('theme.current') ?: 'theme/viar';
        $fallback = public_path($theme . '/images/no_image.png');
        if (!file_exists($fallback)) {
            abort(404);
        }

        return response()->file($fallback);
    })->where('path', '.*\\.(?:png|jpe?g|webp|gif|svg)$');

    Route::group(['middleware' => 'admin.user'], function () {
        Route::post('seo-meta/generate', '\App\Http\Controllers\Voyager\SeoMetaGenerationController@generate')
            ->name('voyager.seo-meta.generate');


        Route::get('seo-meta-suggestions', '\App\Http\Controllers\Voyager\SeoMetaSuggestionController@index')
            ->name('voyager.seo-meta-suggestions.index');
        Route::post('seo-meta-suggestions/scan', '\App\Http\Controllers\Voyager\SeoMetaSuggestionController@scan')
            ->name('voyager.seo-meta-suggestions.scan');
        Route::post('seo-meta-suggestions/generate/{id}', '\App\Http\Controllers\Voyager\SeoMetaSuggestionController@generate')
            ->name('voyager.seo-meta-suggestions.generate');
        Route::post('seo-meta-suggestions/approve/{id}', '\App\Http\Controllers\Voyager\SeoMetaSuggestionController@approve')
            ->name('voyager.seo-meta-suggestions.approve');
        Route::post('seo-meta-suggestions/reject/{id}', '\App\Http\Controllers\Voyager\SeoMetaSuggestionController@reject')
            ->name('voyager.seo-meta-suggestions.reject');
        Route::post('seo-meta-suggestions/apply/{id}', '\App\Http\Controllers\Voyager\SeoMetaSuggestionController@apply')
            ->name('voyager.seo-meta-suggestions.apply');
        Route::post('seo-meta-suggestions/bulk', '\App\Http\Controllers\Voyager\SeoMetaSuggestionController@bulk')
            ->name('voyager.seo-meta-suggestions.bulk');

        Route::get('alt-suggestions', '\App\Http\Controllers\Voyager\AltSuggestionController@index')
            ->name('voyager.alt-suggestions.index');
        Route::post('alt-suggestions/approve/{id}', '\App\Http\Controllers\Voyager\AltSuggestionController@approve')
            ->name('voyager.alt-suggestions.approve');
        Route::post('alt-suggestions/reject/{id}', '\App\Http\Controllers\Voyager\AltSuggestionController@reject')
            ->name('voyager.alt-suggestions.reject');
        Route::post('alt-suggestions/regenerate/{id}', '\App\Http\Controllers\Voyager\AltSuggestionController@regenerate')
            ->name('voyager.alt-suggestions.regenerate');
        Route::post('alt-suggestions/revert/{id}', '\App\Http\Controllers\Voyager\AltSuggestionController@revert')
            ->name('voyager.alt-suggestions.revert');
        Route::post('alt-suggestions/bulk', '\App\Http\Controllers\Voyager\AltSuggestionController@bulk')
            ->name('voyager.alt-suggestions.bulk');
    });

    Voyager::routes();

    Route::get('/sitemap', function () {
        // ini_set('max_execution_time', 300);
        // SitemapGenerator::create(\URL::to('/'))->writeToFile('sitemap.xml');
        return back();
    });

    Route::any('/image_gen_by_order_id/{id}', [ImageGenController::class, 'image_gen_by_order_id'])->name('image_gen_by_order_id');
    Route::post('/user/change-pdf-locale', [AdminOrdersController::class, 'changePdfLocale'])->name('change_pdf_locale');

    Route::any('/upload/tinyimage', [VoyagerAdminController::class, 'tiny_upload'])->name('tiny_upload');
    Route::post('/delete_order_image', [VoyagerAdminController::class, 'delete_order_image'])->name('delete_order_image');

    Route::post('/update/painter/orders/', 'VoyagerAdminController@update_painter_orders')->name('update_painter_orders');
    Route::get('/users/{painter_id}/remove/{order_id}', 'VoyagerAdminController@remove_order_painter')->name('remove_order_painter');
    Route::get('/printing/{printing_id}/remove/{order_id}', 'VoyagerAdminController@remove_order_printing')->name('remove_order_printing');

    Route::post('/update_order_chat_ajax', [VoyagerAdminController::class, 'update_order_chat_ajax'])->name('update_order_chat_ajax');

    Route::post('/update_admin_chat_ajax', [VoyagerAdminController::class, 'update_admin_chat_ajax'])->name('update_admin_chat_ajax');
    // vinepak api
    Route::post('send_courier', 'Api\VinepakApiController@send_courier')->name('send_courier');
    Route::post('create_label', 'Api\VinepakApiController@create_label')->name('create_label');
    Route::post('print_label', 'Api\VinepakApiController@print_label')->name('print_label');
    // endapi

    // admin new orders
    Route::get('create_admin_order', [AdminOrdersController::class, 'create'])->name('create_admin_order');
    Route::get('user_filter', [AdminOrdersController::class, 'user_filter'])->name('user_filter');
    Route::post('create_admin_order', [AdminOrdersController::class, 'create_admin_order'])->name('create_admin_order_form');
    // endorders

    Route::get('/orders/{id}/edit', [AdminOrdersController::class, 'edit_admin_order'])->name('edit_admin_order');
    Route::get('/orders/{id}', [AdminOrdersController::class, 'edit_admin_order'])->name('edit_admin_order');

    Route::post('/orders/{id}/update', [AdminOrdersController::class, 'update_admin_order'])->name('update_admin_order');
    Route::post('/orders/{order}/payment-requests', [AdminOrderPaymentRequestController::class, 'store'])->name('admin.order_payment_requests.store');

    Route::get('/orders/{id}/remove_painter_image/{img_id?}', '\App\Http\Controllers\OrdersController@remove_painter_image')->name('remove_painter_image');
    Route::post('add_painter_images', '\App\Http\Controllers\OrdersController@add_painter_images')->name('add_painter_images');
    Route::post('update_order_item_price', [AdminOrdersController::class, 'update_order_item_price'])->name('update_order_item_price');
    Route::post('add_order_item', '\App\Http\Controllers\OrdersController@add_order_item')->name('add_order_item');
    Route::get('remove_user_image', '\App\Http\Controllers\OrdersController@remove_user_image')->name('remove_user_image');

    // TODO: Facebook Пути функций для одобрения или отклонения скриншота
    // TODO: 30-40 Пути функций для одобрения или отклонения скриншота
    Route::get('/user/{id}/set_sale/{sale}', 'VoyagerAdminController@give_user_sale')->name('give_user_sale');
    Route::get('/user/{id}/cancel_sale/{sale}', 'VoyagerAdminController@cancel_user_sale')->name('cancel_user_sale');

    Route::any('/user/{id}/set_sale30_40', 'VoyagerAdminController@set_sale30_40')->name('set_sale30_40');

    Route::get('/user/{user_id}/leave_rev/{order_id}', '\App\Http\Controllers\OrdersController@leave_rev');
    Route::get('/user/{user_id}/leave_rev', '\App\Http\Controllers\OrdersController@leave_rev_no_orders');

    Route::post('/set_sub_cats', [AdminOrdersController::class, 'set_sub_cats']);
    Route::post('/upload_audio_rev', 'VoyagerAdminController@upload_audio_rev');
    Route::get('/cache/clear', 'VoyagerAdminController@cache_clear');
    Route::get('/cache/set', 'VoyagerAdminController@cache_set');

    Route::get('/user_notify', 'VoyagerAdminController@user_notify');

    Route::get('/email-sender', 'EmailSenderController@index');
    Route::post('/email-sender', 'EmailSenderController@send')->name('mail.send');

/// TODO: 30_40
    Route::get('/Coupon30_40', [AdminOrdersController::class, 'Coupon30_40'])->name('Coupon30_40');
    Route::get('/facebook', [AdminOrdersController::class, 'facebookCoupon'])->name('facebookCoupon');
    Route::post('/salefacebook', [AdminOrdersController::class, 'ajaxfacebook'])->name('ajaxfacebook');

    Route::post('/sale30_40', [AdminOrdersController::class, 'ajax30_40'])->name('ajax30_40');

    // SA Integration (Admin API)
    Route::post('/sa/bot-control', [\App\Http\Controllers\Admin\AdminSaIntegrationController::class, 'botControl'])->name('admin.sa.bot_control');
    Route::post('/sa/send-message', [\App\Http\Controllers\Admin\AdminSaIntegrationController::class, 'sendMessage'])->name('admin.sa.send_message');
    Route::get('/sa/messages', [\App\Http\Controllers\Admin\AdminSaIntegrationController::class, 'getMessages'])->name('admin.sa.messages');
    Route::get('/sa-conversations', [\App\Http\Controllers\Admin\AdminSaIntegrationController::class, 'conversationsIndex'])->name('admin.sa.conversations.index');
    Route::get('/sa-conversations/unread-state', [\App\Http\Controllers\Admin\AdminSaIntegrationController::class, 'conversationsUnreadState'])->name('admin.sa.conversations.unread_state');
    Route::get('/sa-conversations/{conversation}', [\App\Http\Controllers\Admin\AdminSaIntegrationController::class, 'conversationShow'])->name('admin.sa.conversations.show');
    Route::post('/sa-conversations/{conversation}/send-message', [\App\Http\Controllers\Admin\AdminSaIntegrationController::class, 'conversationSendMessage'])->name('admin.sa.conversations.send_message');
    Route::post('/sa-conversations/{conversation}/create-order', [\App\Http\Controllers\Admin\AdminSaIntegrationController::class, 'conversationCreateOrder'])->name('admin.sa.conversations.create_order');
    Route::post('/sa-conversations/{conversation}/bind-order', [\App\Http\Controllers\Admin\AdminSaIntegrationController::class, 'conversationBindOrder'])->name('admin.sa.conversations.bind_order');
    
    // SA Simulator Pages
    Route::get('/sa-simulator', [\App\Http\Controllers\Admin\AdminSaIntegrationController::class, 'simulator'])->name('admin.sa.simulator');
    Route::post('/sa-simulator/trigger', [\App\Http\Controllers\Admin\AdminSaIntegrationController::class, 'simulatorTrigger'])->name('admin.sa.simulator.trigger');

});
