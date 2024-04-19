<?php

namespace Codelayer\LaravelShopifyIntegration\Lib;

use Illuminate\Http\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Ramsey\Uuid\Uuid;
use Shopify\Auth\AccessTokenResponse;
use Shopify\Auth\OAuth;
use Shopify\Auth\Session;
use Shopify\Clients\Http;
use Shopify\Context;
use Shopify\Exception\HttpRequestException;
use Shopify\Exception\SessionStorageException;
use Shopify\Exception\UninitializedContextException;
use Shopify\Utils;

class ShopifyOAuth extends OAuth
{
    public static function authorizeFromRequest(Request $request): ?Session
    {
        $encodedSessionToken = self::getSessionTokenHeader($request) ?? self::getSessionTokenFromUrlParam($request);
        $decodedSessionToken = Utils::decodeSessionToken($encodedSessionToken);

        $dest = $decodedSessionToken['dest'];
        $shop = parse_url($dest, PHP_URL_HOST);

        $cleanShop = Utils::sanitizeShopDomain($shop);

        $session = Utils::loadOfflineSession($cleanShop);

        if (empty($session)) {
            $session = new Session(
                id: OAuth::getOfflineSessionId($cleanShop),
                shop: $cleanShop,
                isOnline: false,
                state: Uuid::uuid4()->toString()
            );
        }

        $accessTokenResponse = ShopifyOAuth::exchangeToken(
            shop: $shop,
            sessionToken: $encodedSessionToken,
            requestedTokenType: 'urn:shopify:params:oauth:token-type:offline-access-token',
        );

        $session->setAccessToken($accessTokenResponse->getAccessToken());
        $session->setScope($accessTokenResponse->getScope());

        $sessionStored = Context::$SESSION_STORAGE->storeSession($session);

        if (! $sessionStored) {
            throw new SessionStorageException(
                'OAuth Session could not be saved. Please check your session storage functionality.'
            );
        }

        return $session;
    }

    /**
     * From https://github.com/Shopify/shopify-app-js/blob/ab752293284d344a5e3803271c25e4237e478565/packages/apps/shopify-api/lib/auth/oauth/token-exchange.ts#L27
     *
     * @throws HttpRequestException
     * @throws \JsonException
     * @throws ClientExceptionInterface
     * @throws UninitializedContextException
     */
    public static function exchangeToken(string $shop, string $sessionToken, string $requestedTokenType): AccessTokenResponse
    {
        Utils::decodeSessionToken($sessionToken);

        $body = [
            'client_id' => Context::$API_KEY,
            'client_secret' => Context::$API_SECRET_KEY,
            'grant_type' => 'urn:ietf:params:oauth:grant-type:token-exchange',
            'subject_token' => $sessionToken,
            'subject_token_type' => 'urn:ietf:params:oauth:token-type:id_token',
            'requested_token_type' => $requestedTokenType,
        ];

        $cleanShop = Utils::sanitizeShopDomain($shop);

        $client = new Http($cleanShop);
        $response = $client->post(path: '/admin/oauth/access_token', body: $body, headers: [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new HttpRequestException("Failed to get access token: {$response->getDecodedBody()}");
        }

        $responseBody = $response->getDecodedBody();

        return new AccessTokenResponse(
            accessToken: $responseBody['access_token'],
            scope: $responseBody['scope'],
        );
    }

    private static function getSessionTokenHeader(Request $request): ?string
    {
        $authorizationHeader = $request->header('authorization');

        if (empty($authorizationHeader)) {
            return null;
        }

        return str_replace('Bearer ', '', $authorizationHeader);
    }

    private static function getSessionTokenFromUrlParam(Request $request): ?string
    {
        return $request->get('id_token');
    }
}
