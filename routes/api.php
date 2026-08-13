<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/google-rating', 'Api\\GoogleRatingController');
Route::get('/google-reviews', 'Api\\GoogleReviewsController');
Route::post('/test/synvolve/order-capture', 'Api\\SynvolveTestWebhookController@captureOrder');
Route::post('/test/synvolve/message-capture', 'Api\\SynvolveTestWebhookController@captureManagerMessage');
Route::get('/test/synvolve/latest/{type?}', 'Api\\SynvolveTestWebhookController@latest');

Route::middleware(['integration.api_key'])->group(function () {
    Route::get('/blog/categories', 'Api\\BlogIntegrationController@categories');
    Route::get('/blog/authors', 'Api\\BlogIntegrationController@authors');
    Route::post('/blog/authors', 'Api\\BlogIntegrationController@storeAuthor');
    Route::get('/blog/posts', 'Api\\BlogIntegrationController@posts');
    Route::post('/blog/media', 'Api\\BlogIntegrationController@storeMedia');
    Route::post('/blog/posts', 'Api\\BlogIntegrationController@storePost');
    Route::patch('/blog/posts/{post_id}', 'Api\\BlogIntegrationController@updatePost');

    Route::post('/sa/webhooks/messages', 'Api\\SaIntegrationController@messagesWebhook');
    Route::post('/crm/webhooks/send-message', 'Api\\SaIntegrationController@sendMessageWebhook');
    Route::get('/sa/services-catalog', 'Api\\SaIntegrationController@servicesCatalog');
    Route::get('/sa/catalog-full', 'Api\\SaIntegrationController@catalogFull');
    Route::get('/sa/services/{service_id}/sizes', 'Api\\SaIntegrationController@serviceSizes');
    Route::get('/sa/services/{service_id}/price-by-size', 'Api\\SaIntegrationController@servicePriceBySize');
    Route::get('/sa/orders/lookup', 'Api\\SaIntegrationController@lookupOrders');
    Route::post('/crm/webhooks/pipeline-changed', 'Api\\SaIntegrationController@pipelineChangedWebhook');
    Route::post('/sa/leads', 'Api\\SaIntegrationController@createLead');
    Route::patch('/sa/leads/{lead_id}', 'Api\\SaIntegrationController@updateLead');
    Route::post('/sa/escalations', 'Api\\SaIntegrationController@createEscalation');
    Route::post('/crm/webhooks/bot-control', 'Api\\SaIntegrationController@botControlWebhook');
});
