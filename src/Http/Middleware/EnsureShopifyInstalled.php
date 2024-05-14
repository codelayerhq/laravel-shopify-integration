<?php

namespace Codelayer\LaravelShopifyIntegration\Http\Middleware;

use Closure;
use Codelayer\LaravelShopifyIntegration\Events\ShopifyAppInstalled;
use Codelayer\LaravelShopifyIntegration\Lib\EnsureBilling;
use Codelayer\LaravelShopifyIntegration\Lib\ShopifyOAuth;
use Codelayer\LaravelShopifyIntegration\Models\ShopifySession;
use Illuminate\Http\Request;
use Shopify\Context;
use Shopify\Utils;

class EnsureShopifyInstalled
{
    /**
     * Checks if the shop in the query arguments is currently installed.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $shop = $request->query('shop') ? Utils::sanitizeShopDomain($request->query('shop')) : null;

        $appInstalled = $shop && ShopifySession::where('shop', $shop)->where('access_token', '<>', null)->where('scope', Context::$SCOPES->toString())->exists();
        $isExitingIframe = preg_match('/^ExitIframe/i', $request->path());

        if ($isExitingIframe) {
            return $next($request);
        }

        if (! $appInstalled) {
            $session = ShopifyOAuth::authorizeFromRequest($request);

            event(new ShopifyAppInstalled($shop));
        }

        $session ??= Utils::loadOfflineSession($shop);

        if (config('shopify-integration.billing.required')) {
            [$hasPayment, $confirmationUrl] = EnsureBilling::check(
                $session,
                config('shopify-integration.billing')
            );

            if (! $hasPayment) {
                $queryString = http_build_query(array_merge($request->query(), ['redirectUri' => urlencode($confirmationUrl)]));

                $redirectUrl = "/ExitIframe?$queryString";

                return redirect($redirectUrl);
            }
        }

        return $next($request);
    }
}
