<?php

declare(strict_types=1);

namespace Codelayer\LaravelShopifyIntegration\Lib\Handlers;

use Codelayer\LaravelShopifyIntegration\Models\ShopifySession;
use Illuminate\Support\Facades\Log;
use Shopify\Webhooks\Handler;

class AppUninstalled implements Handler
{
    public function handle(string $topic, string $shop, array $body): void
    {
        Log::debug("App was uninstalled from $shop - removing all sessions");
        ShopifySession::where('shop', $shop)->delete();
    }
}
