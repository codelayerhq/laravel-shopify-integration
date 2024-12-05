<?php

namespace Codelayer\LaravelShopifyIntegration\Lib;

use DateTime;
use Exception;
use Shopify\Auth\OAuth;
use Shopify\Auth\Session;
use Shopify\Context;

class ShopifySessionHandler
{
    public function getSessionForShopOrThrow(string $shop): Session
    {
        $session = $this->loadOfflineSession($shop);

        if ($session === null) {
            throw new Exception(
                message: "No offline session found for shop $shop",
            );
        }

        return $session;
    }

    public function sessionIsValid(Session $session): bool
    {
        return $session->getAccessToken() &&
            (! $session->getExpires() || ($session->getExpires() > new DateTime));
    }

    private function loadOfflineSession(string $shop, bool $includeExpired = false): ?Session
    {
        Context::throwIfUninitialized();

        $sessionId = OAuth::getOfflineSessionId($shop);
        $session = Context::$SESSION_STORAGE->loadSession($sessionId);

        if ($session && ! $includeExpired && ! $this->sessionIsValid($session)) {
            return null;
        }

        return $session;
    }
}
