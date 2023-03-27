<?php

namespace Codelayer\LaravelShopifyIntegration\Http\Middleware;

use Closure;
use Codelayer\LaravelShopifyIntegration\Lib\AuthRedirection;
use Codelayer\LaravelShopifyIntegration\Models\ShopifySession;
use Illuminate\Http\Request;
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

        $appInstalled = $shop && ShopifySession::where('shop', $shop)->where('access_token', '<>', null)->exists();
        $isExitingIframe = preg_match('/^ExitIframe/i', $request->path());

        return ($appInstalled || $isExitingIframe) ? $next($request) : AuthRedirection::redirect($request);
    }
}
