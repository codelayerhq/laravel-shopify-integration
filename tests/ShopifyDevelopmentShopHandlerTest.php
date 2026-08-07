<?php

use Codelayer\LaravelShopifyIntegration\Exceptions\ShopifyDevelopmentShopException;
use Codelayer\LaravelShopifyIntegration\Lib\ShopifyDevelopmentShopHandler;
use Shopify\Auth\Session;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;

class DevelopmentShopGraphqlClient extends Graphql
{
    public function __construct(private readonly HttpResponse $response) {}

    /**
     * @param  string|array<string, mixed>  $data
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $extraHeaders
     */
    public function query(
        $data,
        array $query = [],
        array $extraHeaders = [],
        ?int $tries = null,
    ): HttpResponse {
        return $this->response;
    }
}

function developmentShopSession(): Session
{
    $session = new Session('offline_example.myshopify.com', 'example.myshopify.com', false, 'state');
    $session->setAccessToken('access-token');

    return $session;
}

function developmentShopHandler(HttpResponse $response): ShopifyDevelopmentShopHandler
{
    $client = new DevelopmentShopGraphqlClient($response);

    return new class($client) extends ShopifyDevelopmentShopHandler
    {
        public function __construct(private readonly Graphql $graphql) {}

        protected function client(Session $session): Graphql
        {
            return $this->graphql;
        }
    };
}

/**
 * @param  array<string, mixed>|string|null  $body
 */
function developmentShopResponse(array|string|null $body, int $status = 200): HttpResponse
{
    return new HttpResponse(
        status: $status,
        headers: ['X-Request-Id' => 'request-id'],
        body: is_array($body) ? json_encode($body, JSON_THROW_ON_ERROR) : $body,
    );
}

it('returns the development shop status', function (bool $status) {
    $response = developmentShopResponse([
        'data' => [
            'shop' => [
                'plan' => [
                    'partnerDevelopment' => $status,
                ],
            ],
        ],
    ]);

    expect(developmentShopHandler($response)->fetchIsDevelopmentShop(developmentShopSession()))
        ->toBe($status);
})->with([true, false]);

it('throws a descriptive exception for an unsuccessful response', function () {
    $response = developmentShopResponse(['errors' => [['message' => 'Unauthorized']]], 401);

    $exception = expectDevelopmentShopException(
        fn () => developmentShopHandler($response)->fetchIsDevelopmentShop(developmentShopSession()),
    );

    expect($exception->getMessage())->toBe('Shopify failed to fetch the development shop status.')
        ->and($exception->context())->toMatchArray([
            'shop' => 'example.myshopify.com',
            'status_code' => 401,
            'request_id' => 'request-id',
            'errors' => [['message' => 'Unauthorized']],
        ]);
});

it('throws a descriptive exception for GraphQL errors', function () {
    $response = developmentShopResponse(['errors' => [['message' => 'GraphQL failed']]]);

    expect(fn () => developmentShopHandler($response)->fetchIsDevelopmentShop(developmentShopSession()))
        ->toThrow(ShopifyDevelopmentShopException::class, 'Shopify failed to fetch the development shop status.');
});

it('throws a descriptive exception when the status is missing', function () {
    $response = developmentShopResponse(['data' => ['shop' => ['plan' => []]]]);

    expect(fn () => developmentShopHandler($response)->fetchIsDevelopmentShop(developmentShopSession()))
        ->toThrow(
            ShopifyDevelopmentShopException::class,
            'Shopify development shop response did not contain a boolean status.',
        );
});

it('throws a descriptive exception for invalid JSON', function () {
    $response = developmentShopResponse('not-json');

    expect(fn () => developmentShopHandler($response)->fetchIsDevelopmentShop(developmentShopSession()))
        ->toThrow(
            ShopifyDevelopmentShopException::class,
            'Shopify returned an invalid response while fetching the development shop status.',
        );
});

function expectDevelopmentShopException(Closure $callback): ShopifyDevelopmentShopException
{
    try {
        $callback();
    } catch (ShopifyDevelopmentShopException $exception) {
        return $exception;
    }

    throw new LogicException('Expected ShopifyDevelopmentShopException to be thrown.');
}
