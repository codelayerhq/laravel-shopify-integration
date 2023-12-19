<?php

namespace Codelayer\LaravelShopifyIntegration\Lib;

use Shopify\Auth\Session;
use Shopify\Clients\Graphql;

class ShopifyDevelopmentShopHandler
{
    public const DEVELOPMENT_SHOP_GRAPHQL_QUERY = <<<'QUERY'
    {
        shop {
            plan {
                partnerDevelopment
            }
        }
    }
    QUERY;

    public function fetchIsDevelopmentShop(Session $session): bool
    {
        $client = new Graphql($session->getShop(), $session->getAccessToken());

        $response = $client->query(self::DEVELOPMENT_SHOP_GRAPHQL_QUERY);

        return data_get($response->getDecodedBody(), 'data.shop.plan.partnerDevelopment');
    }
}
