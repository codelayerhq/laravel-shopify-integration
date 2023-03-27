<?php

use Codelayer\LaravelShopifyIntegration\Lib\EnsureBilling;

return [
    'frontend_directory_path' => 'resources/frontend/',

    'shopify_api_key' => env('SHOPIFY_API_KEY', 'not_defined'),
    'shopify_api_secret' => env('SHOPIFY_API_SECRET', 'not_defined'),
    'app_scopes' => env('SCOPES', 'not_defined'),
    'shop_custom_domain' => env('SHOP_CUSTOM_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Shopify billing
    |--------------------------------------------------------------------------
    |
    | You may want to charge merchants for using your app. Setting required to true will cause the EnsureShopifySession
    | middleware to also ensure that the session is for a merchant that has an active one-time payment or subscription.
    | If no payment is found, it starts off the process and sends the merchant to a confirmation URL so that they can
    | approve the purchase.
    |
    | Learn more about billing in our documentation: https://shopify.dev/docs/apps/billing
    |
    */
    'billing' => [
        'required' => false,

        // Example set of values to create a charge for $5 one time
        'chargeName' => 'My Shopify App One-Time Billing',
        'amount' => 5.0,
        'currencyCode' => 'USD', // Currently only supports USD
        'interval' => EnsureBilling::INTERVAL_ONE_TIME,
    ],
];
