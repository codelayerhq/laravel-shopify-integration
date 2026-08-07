<?php

namespace Codelayer\LaravelShopifyIntegration\Exceptions;

use RuntimeException;
use Throwable;

class ShopifyDevelopmentShopException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        string $message,
        private readonly array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
