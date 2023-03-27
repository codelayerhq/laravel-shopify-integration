<?php

namespace Codelayer\LaravelShopifyIntegration\Http\Controllers;

use Codelayer\LaravelShopifyIntegration\Events\ShopifyAppInstalled;
use Codelayer\LaravelShopifyIntegration\Lib\AuthRedirection;
use Codelayer\LaravelShopifyIntegration\Lib\CookieHandler;
use Codelayer\LaravelShopifyIntegration\Lib\EnsureBilling;
use Codelayer\LaravelShopifyIntegration\Models\ShopifySession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Shopify\Auth\OAuth;
use Shopify\Utils;

class ShopifyAuthController extends Controller
{
    public function initialize(Request $request): RedirectResponse
    {
        $shop = Utils::sanitizeShopDomain((string) $request->query('shop'));

        // Delete any previously created OAuth sessions that were not completed (don't have an access token)
        ShopifySession::where('shop', $shop)->where('access_token', null)->delete();

        return AuthRedirection::redirect($request);
    }

    public function callback(Request $request): RedirectResponse
    {
        $session = OAuth::callback(
            cookies: $request->cookie(),
            query: $request->query(),
            setCookieFunction: [CookieHandler::class, 'saveShopifyCookie'],
        );

        $host = $request->query('host');
        $shop = Utils::sanitizeShopDomain($request->query('shop'));

        event(new ShopifyAppInstalled($shop));

        $redirectUrl = Utils::getEmbeddedAppUrl($host);
        if (config('shopify-integration.billing.required')) {
            [$hasPayment, $confirmationUrl] = EnsureBilling::check($session, config('shopify-integration.billing'));

            if (! $hasPayment) {
                $redirectUrl = $confirmationUrl;
            }
        }

        return redirect($redirectUrl);
    }
}
