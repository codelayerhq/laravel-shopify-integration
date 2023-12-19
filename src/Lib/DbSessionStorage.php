<?php

declare(strict_types=1);

namespace Codelayer\LaravelShopifyIntegration\Lib;

use Codelayer\LaravelShopifyIntegration\Models\ShopifySession as SessionModel;
use Shopify\Auth\AccessTokenOnlineUserInfo;
use Shopify\Auth\Session;
use Shopify\Auth\SessionStorage;

class DbSessionStorage implements SessionStorage
{
    public function loadSession(string $sessionId): ?Session
    {
        $dbSession = SessionModel::where('session_id', $sessionId)->first();

        if ($dbSession) {
            $session = new Session(
                id: $dbSession->session_id,
                shop: $dbSession->shop,
                isOnline: $dbSession->is_online == 1,
                state: $dbSession->state
            );
            if ($dbSession->expires_at) {
                $session->setExpires($dbSession->expires_at);
            }
            if ($dbSession->access_token) {
                $session->setAccessToken($dbSession->access_token);
            }
            if ($dbSession->scope) {
                $session->setScope($dbSession->scope);
            }
            if ($dbSession->user_id) {
                $onlineAccessInfo = new AccessTokenOnlineUserInfo(
                    id: $dbSession->user_id,
                    firstName: $dbSession->user_first_name,
                    lastName: $dbSession->user_last_name,
                    email: $dbSession->user_email,
                    emailVerified: $dbSession->user_email_verified === true,
                    accountOwner: $dbSession->account_owner === true,
                    locale: $dbSession->locale,
                    collaborator: $dbSession->collaborator === true
                );
                $session->setOnlineAccessInfo($onlineAccessInfo);
            }

            return $session;
        }

        return null;
    }

    public function storeSession(Session $session): bool
    {
        $model = SessionModel::updateOrCreate(
            ['session_id' => $session->getId()],
            [
                'session_id' => $session->getId(),
                'shop' => $session->getShop(),
                'state' => $session->getState(),
                'is_online' => $session->isOnline(),
                'access_token' => $session->getAccessToken(),
                'expires_at' => $session->getExpires(),
                'scope' => $session->getScope(),
                'is_development_shop' => app(ShopifyDevelopmentShopHandler::class)->fetchIsDevelopmentShop($session),

                'user_id' => $session->getOnlineAccessInfo()?->getId(),
                'user_first_name' => $session->getOnlineAccessInfo()?->getFirstName(),
                'user_last_name' => $session->getOnlineAccessInfo()?->getLastName(),
                'user_email' => $session->getOnlineAccessInfo()?->getEmail(),
                'user_email_verified' => $session->getOnlineAccessInfo()?->isEmailVerified(),
                'account_owner' => $session->getOnlineAccessInfo()?->isAccountOwner(),
                'locale' => $session->getOnlineAccessInfo()?->getLocale(),
                'collaborator' => $session->getOnlineAccessInfo()?->isCollaborator(),
            ]
        );

        return true;
    }

    public function deleteSession(string $sessionId): bool
    {
        return SessionModel::where('session_id', $sessionId)->delete() === 1;
    }
}
