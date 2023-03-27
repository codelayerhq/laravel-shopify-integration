<?php

declare(strict_types=1);

namespace Codelayer\LaravelShopifyIntegration\Lib;

use Exception;
use Illuminate\Support\Facades\Cookie;
use Shopify\Auth\OAuthCookie;
use Shopify\Context;

class CookieHandler
{
    /**
     * @throws Exception
     */
    public static function saveShopifyCookie(OAuthCookie $cookie): bool
    {
        $domain = parse_url(Context::$HOST_SCHEME.'://'.Context::$HOST_NAME, PHP_URL_HOST);

        if ($domain === false) {
            throw new Exception('Could not parse url');
        }

        Cookie::queue(
            Cookie::make(
                name: $cookie->getName(),
                value: $cookie->getValue(),
                minutes: (int) $cookie->getExpire(),
                path: '/',
                domain: $domain,
                secure: $cookie->isSecure(),
                httpOnly: $cookie->isHttpOnly(),
                sameSite: 'Lax'
            )
        );

        return true;
    }
}
