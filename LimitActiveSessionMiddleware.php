<?php

namespace App\Http\Middleware;

use Str;
use Closure;
use App\Model\Setting;
use Illuminate\Contracts\Auth\Guard;
use App\Model\Session as SessionModel;

class LimitActiveSessionMiddleware
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
        if(auth()->guest())
            return $next($request);

        if(Str::is('/UserManagement/User/session/*', $request->getPathInfo())){
            return $next($request);
        }



        if(Str::is('*/user-profile-simple', $request->getPathInfo())){
            return $next($request);
        }


        $user = auth()->user();
        $setting = Setting::where('parameter_name', 'count_session_active_per_user')->first();
        if((! $setting) || ($setting->parameter_value < 1) || (config('session.lifetime') == 0))
            return $next($request);

        $sessionId = request()->session()->getId();
        $activeTime = time() - (config('session.lifetime') * 60);
        $sessionsActiveCount = SessionModel::query()
            ->where('user_id' , $user->id)
            ->where('last_activity' , '>' , $activeTime)
            ->where('id' , '!=' , $sessionId)
            ->count();

        if($sessionsActiveCount <= $setting->parameter_value - 1)
            return $next($request);


        

        return redirect()->route('UserProfileSimple')->withErrors("تعداد نشست فعال شما بیشتر از حد مجاز است. نشست فعال خود را پاک کنید");
    }
}
