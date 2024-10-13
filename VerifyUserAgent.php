<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;

class VerifyUserAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if(config('module.app_agent_mode') == 'developer'){
            return $next($request);
        }
        $sessionUserAgent = Session::get('session_user_agent');    
        if ($sessionUserAgent && $request->header('User-Agent') !== $sessionUserAgent) {

            if(is_null(Request::user())){
                Request::setUserResolver(function(){
                    return auth()->user();
                });
            }

            event(new Logout(auth()->guard(), auth()->user()));
            Session::flush(); // Invalidates the session
            return redirect()->route('login');
        }

        return $next($request);
    }
}
