<?php

use Codelayer\LaravelShopifyIntegration\Http\Controllers\ShopifyAuthController;
use Codelayer\LaravelShopifyIntegration\Http\Controllers\ShopifyFallbackController;
use Codelayer\LaravelShopifyIntegration\Http\Controllers\ShopifyWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::fallback(ShopifyFallbackController::class)->middleware('shopify.installed');
    Route::get('/api/auth', [ShopifyAuthController::class, 'initialize']);
    Route::get('/api/auth/callback', [ShopifyAuthController::class, 'callback']);
});
