<?php

namespace Codelayer\LaravelShopifyIntegration\Listeners;

use Codelayer\LaravelShopifyIntegration\Events\ShopifyAppInstalled;
use Codelayer\LaravelShopifyIntegration\Events\ShopifyShopUpdated;
use Codelayer\LaravelShopifyIntegration\Lib\ShopifyDevelopmentShopHandler;
use Codelayer\LaravelShopifyIntegration\Lib\ShopifySessionHandler;
use Codelayer\LaravelShopifyIntegration\Models\ShopifySession;

class RefreshShopDevelopmentState
{
    public function __construct(
        private ShopifySessionHandler $sessionHandler,
        private ShopifyDevelopmentShopHandler $developmentShopHandler,
    ) {
    }

    /**
     * Handle the given event.
     */
    public function handle(ShopifyAppInstalled|ShopifyShopUpdated $event): void
    {
        $session = $this->sessionHandler->getSessionForShopOrThrow($event->shop);
        $dbSession = ShopifySession::where('session_id', $session->getId())->firstOrFail();

        $isDevelopmentShop = $this->developmentShopHandler->fetchIsDevelopmentShop($session);

        $dbSession->update(['is_development_shop' => $isDevelopmentShop]);
    }
}
