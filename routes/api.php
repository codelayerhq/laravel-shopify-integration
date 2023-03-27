<?php

use Codelayer\LaravelShopifyIntegration\Http\Controllers\ShopifyWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->group(function () {
    Route::post('/api/webhooks', [ShopifyWebhookController::class, 'handle']);
});
