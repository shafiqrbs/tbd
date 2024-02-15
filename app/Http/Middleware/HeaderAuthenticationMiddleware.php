<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Mockery\Matcher\Closure;

class HeaderAuthenticationMiddleware extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next , $guard="api")
    {
        dd('ok');
        /*$apiKey = \config('api.X-API-KEY');
        $requestHeaderKey = $request->header('X-API-KEY');
        if($requestHeaderKey == $apiKey){
            return $next($request);
        }
        return \response('Unauthorized access',404);*/

    }
}
