<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use App\Model\Session as SessionModel;

class SessionAuthMiddleware
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
        if(! auth()->check()){
            SessionModel::where('user_id' , null)->delete();

            $session = SessionModel::find($request->session()->getId());
            if($session){
                $request->session()->regenerate();
            }
            return $next($request);
        }

        $sessionId = $request->session()->getId();
        $sessionModel = SessionModel::find($sessionId);

        if(!$sessionModel){
            $sessionModel = new SessionModel;
            $sessionModel->id = $sessionId;
        }

        $sessionModel->fill([
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'last_activity' => time(),
            'user_agent' => $request->userAgent()
        ]);

        $sessionModel->save();

        return $next($request);
    }
}
