<?php

namespace Codelayer\LaravelShopifyIntegration\Exceptions;

class ShopifyBillingException extends \Exception
{
    /** @var array<mixed>|null */
    public ?array $errorData;

    /** @param  array<mixed>|null  $errorData */
    public function __construct(string $message, ?array $errorData = null)
    {
        parent::__construct($message);

        $this->errorData = $errorData;
    }
}
