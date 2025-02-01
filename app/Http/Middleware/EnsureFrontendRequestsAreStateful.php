<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFrontendRequestsAreStateful extends \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful
{
    /**
     * Configure secure cookie sessions.
     *
     * @return void
     */
    protected function configureSecureCookieSessions()
    {
        config([
            'session.http_only' => true,
            'session.same_site' => 'none',
            'session.secure' => true,
        ]);
    }
}
