<?php

namespace Codelayer\LaravelShopifyIntegration\Http\Controllers;

use Codelayer\LaravelShopifyIntegration\Lib\ShopifyOAuth;
use Illuminate\Http\Request;

class ShopifyAuthController extends Controller
{
    public function authorizeShopify(Request $request): bool
    {
        try {
            ShopifyOAuth::authorizeFromRequest($request);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        return true;
    }
}
