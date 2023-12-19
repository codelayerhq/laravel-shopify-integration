<?php

namespace Codelayer\LaravelShopifyIntegration\Lib;

use Exception;
use Shopify\Auth\Session;
use Shopify\Utils;

class ShopifySessionHandler
{
    public function getSessionForShopOrThrow(string $shop): Session
    {
        $session = Utils::loadOfflineSession($shop);

        if ($session === null) {
            throw new Exception(
                message: "No offline session found for shop $shop",
            );
        }

        return $session;
    }
}
