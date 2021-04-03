<?php

namespace App\Http\Middleware;

use Closure;

class HeaderMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('Content-Type') != 'application/json') {
            return response()->json(['message' => 'Only JSON requests are allowed.'], 400);
        }

        return $next($request);
    }
}
