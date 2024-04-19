<?php

use Codelayer\LaravelShopifyIntegration\Http\Controllers\ShopifyFallbackController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::fallback(ShopifyFallbackController::class)->middleware('shopify.installed');
});
