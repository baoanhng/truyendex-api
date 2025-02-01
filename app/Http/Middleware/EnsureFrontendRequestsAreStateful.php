<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFrontendRequestsAreStateful extends \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful
{

}
