<?php

namespace Codelayer\LaravelShopifyIntegration;

use Codelayer\LaravelShopifyIntegration\Events\ShopifyAppInstalled;
use Codelayer\LaravelShopifyIntegration\Events\ShopifyShopUpdated;
use Codelayer\LaravelShopifyIntegration\Http\Middleware\EnsureShopifyInstalled;
use Codelayer\LaravelShopifyIntegration\Http\Middleware\EnsureShopifySession;
use Codelayer\LaravelShopifyIntegration\Lib\DbSessionStorage;
use Codelayer\LaravelShopifyIntegration\Listeners\RefreshShopDevelopmentState;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Shopify\ApiVersion;
use Shopify\Context;
use Shopify\Exception\MissingArgumentException;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelShopifyIntegrationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-shopify-integration')
            ->hasConfigFile()
            ->hasMigrations(['create_shopify_sessions_table', 'add_is_development_shop_to_shopify_sessions'])
            ->hasRoutes('web', 'api');

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('shopify.auth', EnsureShopifySession::class);
        $router->aliasMiddleware('shopify.installed', EnsureShopifyInstalled::class);

        try {
            $this->configureShopifyContext();
            $this->configurePackageEvents();
        } catch (MissingArgumentException $e) {
            report($e);
        }
    }

    private function configurePackageEvents(): void
    {
        Event::listen(ShopifyAppInstalled::class, RefreshShopDevelopmentState::class);
        Event::listen(ShopifyShopUpdated::class, RefreshShopDevelopmentState::class);
    }

    /**
     * @throws MissingArgumentException
     */
    private function configureShopifyContext(): void
    {
        $hostname = preg_replace('/(^\w+:|^)\/\//', '', config('app.url'));
        $customDomain = config('shopify-integration.shop_custom_domain');

        Context::initialize(
            apiKey: config('shopify-integration.shopify_api_key', 'not-set'),
            apiSecretKey: config('shopify-integration.shopify_api_secret', 'not-set'),
            scopes: config('shopify-integration.app_scopes', ''),
            hostName: $hostname,
            sessionStorage: new DbSessionStorage(),
            apiVersion: config('shopify-integration.shopify_api_version', ApiVersion::LATEST),
            customShopDomains: (array) $customDomain,
        );
    }
}
