<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HeaderAuthenticationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestHeaderKey = $request->header('x-api-key');
        $appApiKey = config('core.api_key');
        if($requestHeaderKey == $appApiKey){
            return $next($request);
        }
        return new JsonResponse(['status'=>404, 'message'=>'Unauthorized access']);
    }
}
