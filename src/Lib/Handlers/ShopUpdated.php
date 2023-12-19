<?php

namespace Codelayer\LaravelShopifyIntegration\Lib\Handlers;

use Codelayer\LaravelShopifyIntegration\Events\ShopifyShopUpdated;
use Shopify\Webhooks\Handler;

class ShopUpdated implements Handler
{
    public function handle(string $topic, string $shop, array $body): void
    {
        event(new ShopifyShopUpdated($shop));
    }
}
