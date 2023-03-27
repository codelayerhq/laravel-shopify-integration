<?php

namespace Codelayer\LaravelShopifyIntegration\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifySession extends Model
{
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
