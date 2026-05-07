<?php

namespace App\Http\Middleware;

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class CustomEnsureFrontendRequestsAreStateful extends EnsureFrontendRequestsAreStateful
{
    protected function configureSecureCookieSessions()
    {
        config([
            'session.http_only' => true,
            'session.same_site' => 'none',
            'session.secure' => true,
        ]);
    }
}