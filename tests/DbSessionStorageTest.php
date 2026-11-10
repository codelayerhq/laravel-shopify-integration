<?php

use Codelayer\LaravelShopifyIntegration\Lib\DbSessionStorage;
use Codelayer\LaravelShopifyIntegration\Models\ShopifySession;
use Shopify\Auth\Session;

beforeEach(function () {
    (include __DIR__.'/../database/migrations/create_shopify_sessions_table.php')->up();
    (include __DIR__.'/../database/migrations/add_is_development_shop_to_shopify_sessions.php')->up();
});

function authorizedSession(string $state = 'state'): Session
{
    $session = new Session('offline_example.myshopify.com', 'example.myshopify.com', false, $state);
    $session->setAccessToken('access-token');
    $session->setScope('read_orders,write_customers');

    return $session;
}

it('keeps the access token and scope of an authorized session when storing an oauth state', function () {
    $storage = new DbSessionStorage;
    $storage->storeSession(authorizedSession());

    $stored = $storage->storeSession(
        new Session('offline_example.myshopify.com', 'example.myshopify.com', false, 'new-state')
    );

    $session = $storage->loadSession('offline_example.myshopify.com');

    expect($stored)->toBeTrue()
        ->and($session->getState())->toBe('new-state')
        ->and($session->getAccessToken())->toBe('access-token')
        ->and($session->getScope())->toBe('read_orders,write_customers');
});

it('stores an oauth state if no session exists', function () {
    $stored = (new DbSessionStorage)->storeSession(
        new Session('offline_example.myshopify.com', 'example.myshopify.com', false, 'new-state')
    );

    $session = ShopifySession::where('session_id', 'offline_example.myshopify.com')->sole();

    expect($stored)->toBeTrue()
        ->and($session->state)->toBe('new-state')
        ->and($session->access_token)->toBeNull();
});

it('replaces an unauthorized session when storing an oauth state', function () {
    $storage = new DbSessionStorage;
    $storage->storeSession(new Session('offline_example.myshopify.com', 'example.myshopify.com', false, 'state'));

    $storage->storeSession(new Session('offline_example.myshopify.com', 'example.myshopify.com', false, 'new-state'));

    $session = ShopifySession::where('session_id', 'offline_example.myshopify.com')->sole();

    expect($session->state)->toBe('new-state')
        ->and($session->access_token)->toBeNull();
});

it('replaces an authorized session when storing an authorized session', function () {
    $storage = new DbSessionStorage;
    $storage->storeSession(authorizedSession());

    $session = authorizedSession('new-state');
    $session->setAccessToken('new-access-token');
    $session->setScope('read_orders');
    $storage->storeSession($session);

    $session = $storage->loadSession('offline_example.myshopify.com');

    expect($session->getState())->toBe('new-state')
        ->and($session->getAccessToken())->toBe('new-access-token')
        ->and($session->getScope())->toBe('read_orders');
});
