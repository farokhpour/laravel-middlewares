<?php

namespace App\Http\Middleware;

use Closure;

class TwoFactor
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
        $user = auth()->user();
        if(config('module.use_two_factor_authentication') && auth()->check() && $user->two_factor_code)
        {

            if(! cache()->has("user_{$user->id}_show_verify_form_expired_at")) //expired form
            {
                $user->resetTwoFactorCode();
                auth()->logout();

                return redirect()
                    ->route('login')
                    ->withMessage('The two factor code has expired. Please login again.');
            }


            if(!$request->is('verify*')) //prevent enless loop, otherwise send to verify
            {
                return redirect()->route('verify.index');
            }
        }

        return $next($request);
    }
}
