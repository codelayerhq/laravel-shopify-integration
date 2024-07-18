<?php

namespace Codelayer\LaravelShopifyIntegration\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShopifyShopUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public string $shop) {}
}
