<?php

namespace Codelayer\LaravelShopifyIntegration\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

/**
 * @property bool $is_development_shop
 */
class ShopifySession extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'is_online' => 'bool',
        'user_email_verified' => 'bool',
        'account_owner' => 'bool',
        'collaborator' => 'bool',
    ];
}
