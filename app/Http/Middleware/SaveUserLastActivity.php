<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SaveUserLastActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->last_activity_at ?? $user->created_at)->diffInMinutes() > 5) {
            $user->timestamps = false;
            $user->last_activity_at = now();
            $user->save();
        }

        return $next($request);
    }
}
