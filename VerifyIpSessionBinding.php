<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Logout;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Request as FacadeRequest;

class VerifyIpSessionBinding
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionIp = Session::get('session_ip');
        if ($sessionIp && $request->ip() !== $sessionIp) {

            if(is_null(FacadeRequest::user())){
                FacadeRequest::setUserResolver(function(){
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
