<?php

namespace Codelayer\LaravelShopifyIntegration\Lib;

use Codelayer\LaravelShopifyIntegration\Exceptions\ShopifyDevelopmentShopException;
use JsonException;
use Shopify\Auth\Session;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;

class ShopifyDevelopmentShopHandler
{
    public const DEVELOPMENT_SHOP_GRAPHQL_QUERY = <<<'QUERY'
    {
        shop {
            plan {
                partnerDevelopment
            }
        }
    }
    QUERY;

    public function fetchIsDevelopmentShop(Session $session): bool
    {
        $response = $this->client($session)->query(self::DEVELOPMENT_SHOP_GRAPHQL_QUERY);

        try {
            $responseBody = $response->getDecodedBody();
        } catch (JsonException $exception) {
            throw $this->exception(
                'Shopify returned an invalid response while fetching the development shop status.',
                $session,
                $response,
                previous: $exception,
            );
        }

        $errors = is_array($responseBody) ? data_get($responseBody, 'errors') : null;
        if ($response->getStatusCode() !== 200 || ! empty($errors)) {
            throw $this->exception(
                'Shopify failed to fetch the development shop status.',
                $session,
                $response,
                $errors,
            );
        }

        $isDevelopmentShop = is_array($responseBody)
            ? data_get($responseBody, 'data.shop.plan.partnerDevelopment')
            : null;

        if (! is_bool($isDevelopmentShop)) {
            throw $this->exception(
                'Shopify development shop response did not contain a boolean status.',
                $session,
                $response,
            );
        }

        return $isDevelopmentShop;
    }

    protected function client(Session $session): Graphql
    {
        return new Graphql($session->getShop(), $session->getAccessToken());
    }

    private function exception(
        string $message,
        Session $session,
        HttpResponse $response,
        mixed $errors = null,
        ?\Throwable $previous = null,
    ): ShopifyDevelopmentShopException {
        return new ShopifyDevelopmentShopException($message, [
            'shop' => $session->getShop(),
            'status_code' => $response->getStatusCode(),
            'request_id' => $response->getRequestId(),
            'errors' => $errors,
        ], $previous);
    }
}
