<?php

declare(strict_types=1);

namespace Codelayer\LaravelShopifyIntegration\Lib;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Shopify\Auth\OAuth;
use Shopify\Context;
use Shopify\Exception\CookieSetException;
use Shopify\Exception\PrivateAppException;
use Shopify\Exception\SessionStorageException;
use Shopify\Exception\UninitializedContextException;
use Shopify\Utils;

class AuthRedirection
{
    public static function redirect(Request $request, bool $isOnline = false): RedirectResponse
    {
        $shop = Utils::sanitizeShopDomain($request->query('shop'));

        if (Context::$IS_EMBEDDED_APP && $request->query('embedded', '0') === '1') {
            $redirectUrl = self::clientSideRedirectUrl($shop, $request->query());
        } else {
            $redirectUrl = self::serverSideRedirectUrl($shop, $isOnline);
        }

        return redirect($redirectUrl);
    }

    /**
     * @throws CookieSetException
     * @throws UninitializedContextException
     * @throws PrivateAppException
     * @throws SessionStorageException
     */
    private static function serverSideRedirectUrl(string $shop, bool $isOnline): string
    {
        return OAuth::begin(
            shop: $shop,
            redirectPath: '/api/auth/callback',
            isOnline: $isOnline,
            setCookieFunction: [CookieHandler::class, 'saveShopifyCookie'],
        );
    }

    private static function clientSideRedirectUrl(string $shop, array $query): string
    {
        $appHost = Context::$HOST_NAME;
        $redirectUri = urlencode("https://$appHost/api/auth?shop=$shop");

        $queryString = http_build_query(array_merge($query, ['redirectUri' => $redirectUri]));

        return "/ExitIframe?$queryString";
    }
}
