<?php

namespace App\Http\Middleware;

use Closure;

class ForceSanctumCookieSettings
{
    public function handle($request, Closure $next)
    {
        config([
            'session.same_site' => 'none',
            'session.secure' => true,
        ]);

        return $next($request);
    }
}