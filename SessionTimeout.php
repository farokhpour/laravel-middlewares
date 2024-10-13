<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // If user is not logged in...
        if (! auth()->check()) {
            return $next($request);
        }

        if ($request->ajax()) {
            return $next($request);
        }

        storeLastSeenAt();

        return $next($request);
    }
}
