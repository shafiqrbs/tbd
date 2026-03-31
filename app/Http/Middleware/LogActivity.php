<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            // Use only safe, non-file parameters for logging
            $parameters = $request->except(['password', 'password_confirmation', 'file']);
            if ($request->hasFile('file')) {
                $parameters['file'] = '[uploaded file]';
            }

            // Log the request
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'parameters' => $parameters,
                    'response_status' => $response->getStatusCode(),
                ])
                ->log('Request: ' . $request->method() . ' ' . $request->path());

        } catch (\Exception $e) {
            Log::error('LogActivity middleware error: ' . $e->getMessage());
        }

        return $response;
    }
}
