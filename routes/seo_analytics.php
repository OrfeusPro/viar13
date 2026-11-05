<?php

use App\Http\Controllers\SeoAnalyticsPageController;

Route::get('/seo-analytics', [SeoAnalyticsPageController::class, 'home'])
    ->name('seo-analytics.home');
Route::get('/seo-analytics/privacy', [SeoAnalyticsPageController::class, 'privacy'])
    ->name('seo-analytics.privacy');
Route::get('/seo-analytics/terms', [SeoAnalyticsPageController::class, 'terms'])
    ->name('seo-analytics.terms');
