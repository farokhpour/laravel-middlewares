<?php

namespace App\Http\Middleware;

use Str;
use Closure;
use jeremykenedy\LaravelLogger\App\Http\Middleware\LogActivity as MiddlewareLogActivity;

class LogActivity extends MiddlewareLogActivity
{
    protected $mapModels = [
        "VulnerabilityAssessment" => "MgaItem",
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $description = null)
    {
        try{
            if(Str::is('*/destroy/*', $request->getPathInfo())){
                $explodeRoute = explode('/',$request->getPathInfo());
                $index = array_search('destroy', $explodeRoute);
                if($index){
                    $modelClass = @$explodeRoute[$index - 1];
                    $modelClass = $this->mapModels[$modelClass] ?: $modelClass;

                    if($modelClass){
                        $id = @$explodeRoute[$index + 1];
                        $modelObject = call_user_func('\App\Model\\' . $modelClass . '::find', $id);
                        if($modelObject->title){
                            $description = $modelObject->title.' '.trans('LaravelLogger::laravel-logger.verbTypes.deleted');
                        }
                    }
                }
            }
    
            if(strtolower($request->method()) === "delete" && Str::is('/destroy-item-selected/*', $request->getPathInfo())){
                $explodeRoute = explode('/',$request->getPathInfo());
                
                $modelClass = end($explodeRoute);
                $modelClass = $this->mapModels[$modelClass] ?: $modelClass;

                if($modelClass){
    
                    $collection = call_user_func('\App\Model\\' . $modelClass . '::whereIn', 'id',$request->ids)->get();
                    if($collection->count()){
                        $description = $collection->pluck('title')->implode('و ').' '.trans('LaravelLogger::laravel-logger.verbTypes.deleted');
                    }
                }
            }

        }catch(\Exception $e){
            info('Error in '.__CLASS__. ' Line Is : '.$e->getLine().' Message Is: ' . $e->getMessage());
        }
        
        
        return parent::handle($request, $next, $description);
    }
}
