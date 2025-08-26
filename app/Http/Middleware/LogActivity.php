<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogActivity
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $response = $next($request);

            // Debug: Check if activity log is working
            Log::info('LogActivity middleware triggered for: ' . $request->path());

            // Log the request
            activity()
                ->causedBy(auth()->user()) // This might be null for unauthenticated users
                ->withProperties([
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'parameters' => $request->except(['password', 'password_confirmation']),
                    'response_status' => $response->getStatusCode(),
                ])
                ->log('Request: ' . $request->method() . ' ' . $request->path());

            Log::info('Activity logged successfully');

            return $response;

        } catch (\Exception $e) {
            Log::error('LogActivity middleware error: ' . $e->getMessage());
            return $next($request);
        }
    }
}
