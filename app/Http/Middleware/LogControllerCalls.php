<?php


namespace App\Http\Middleware;
// app/Http/Middleware/LogControllerCalls.php

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Core\App\Models\RequestLogModel;

class LogControllerCalls
{
    public function handle(Request $request, Closure $next)
    {
        // Get current route action
        $routeAction = Route::getCurrentRoute()->getAction();

        // Get the controller and method (action)
        $controllerAction = $routeAction['controller'] ?? null;

        if ($controllerAction) {
            [$controller, $action] = explode('@', class_basename($controllerAction));
        } else {
            $controller = 'Closure';
            $action = 'handle';
        }

        // Log the request details
        RequestLogModel::create([
            'controller' => $controller,
            'action'     => $action,
            'method'     => $request->method(),
            'url'        => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'requested_at' => now(),
        ]);

        // Continue with the request
        return $next($request);
    }
}



