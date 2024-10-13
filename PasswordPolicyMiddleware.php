<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Str;
use Closure;
use App\Model\Setting;
use Illuminate\Contracts\Auth\Guard;
use App\Model\Session as SessionModel;

class PasswordPolicyMiddleware
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->ajax())
            return $next($request);

        if($request->method() === "POST")
            return $next($request);

        $userPasswordPath = parse_url(route('UserChangePassword'))['path'];

        if(in_array($request->getPathInfo() ,[
            $userPasswordPath,
            '/auth/logout',
            '/auth/login'
        ])){
            return $next($request);
        }

        $user = auth()->user();
        if(is_null($user->password_changed_at)){
            return redirect()
            ->route('UserChangePassword')
            ->withErrors("لطفا کلمه عبور خود را تغییر دهید.");
        }
        
        $setting = Setting::where('parameter_name', 'day_of_changed_password')->first();
        if (!isset($setting->parameter_name)){
            return $next($request);
        }

        if((int)$setting->parameter_value === 0) {
            return $next($request);
        }

        if (Carbon::now()->greaterThan($user->password_changed_at->addDays((int)$setting->parameter_value))){
            return redirect()
                ->route('UserChangePassword')
                ->withErrors("لطفا کلمه عبور خود را تغییر دهید.");
        }

        return $next($request);
    }
}
