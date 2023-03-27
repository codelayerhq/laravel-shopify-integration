<?php

namespace Codelayer\LaravelShopifyIntegration\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Shopify\Context;
use Shopify\Utils;

class ShopifyFallbackController extends Controller
{
    public function __invoke(Request $request): string|RedirectResponse
    {
        if (Context::$IS_EMBEDDED_APP && $request->query('embedded', '0') === '1') {
            if (config('app.env') === 'production') {
                return file_get_contents(public_path('index.html'));
            } else {
                return file_get_contents(base_path(config('shopify-integration.frontend_directory_path').'index.html'));
            }
        } else {
            return redirect(Utils::getEmbeddedAppUrl($request->query('host', null)).'/'.$request->path());
        }
    }
}
