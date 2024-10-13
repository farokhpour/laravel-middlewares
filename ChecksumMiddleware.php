<?php

namespace App\Http\Middleware;

use App\Model\MgaRTP;
use App\Model\ReqRequests;
use App\Repositories\Implement\ReqRequestRepository;
use App\Repositories\RequestRepositoryInterface;
use Closure;
use App\Events\FailedLogin;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Session;
use Symfony\Component\HttpFoundation\Response;

class ChecksumMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->user()->isDataValid()){
            return $next($request);
        }


        $request->merge([
            'storeChecksum' => false
        ]);

        event(new Logout(auth()->guard(), auth()->user()));
        createSimpleIssue([
            'title' => "خطای تغییر مشصخه امنیتی بر روی کاربر ". auth()->user()?->name,
            'requester_id' => 1,
            'responsible_id' => [
                'users' => [1]
            ],
        ]);


        Session::flush(); // Invalidates the session
        return redirect()->route('login')->withErrors(['message'=>'کاربر معتبر نیست.']);
    }
}
