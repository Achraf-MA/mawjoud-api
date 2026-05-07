<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class SanctumCookieOverride extends EnsureFrontendRequestsAreStateful
{
    public function handle($request, $next)
    {
        config([
            'session.http_only' => true,
            'session.same_site' => 'none',
            'session.secure' => true,
        ]);

        return parent::handle($request, $next);
    }
}