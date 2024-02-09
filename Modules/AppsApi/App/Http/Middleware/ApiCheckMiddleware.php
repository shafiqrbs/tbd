<?php

namespace Modules\AppsApi\Http\Middleware;

use http\Env\Response;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Mockery\Matcher\Closure;

class ApiCheckMiddleware extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next , $guard="web")
    {


        $apiKey = \config('api.X-API-KEY');
        $requestHeaderKey = $request->header('X-API-KEY');

        if($requestHeaderKey == $apiKey){
            return $next($request);
        }
        return \response('Unauthorized access',404);


    }
}
