<?php

namespace Modules\Core\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Core\App\Models\RequestLog;
use Modules\Core\App\Models\UserModel;
use Symfony\Component\HttpFoundation\Response;

class LogRequestResponse
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $requestData = $this->getRequestData($request);
        $response = $next($request);
        $executionTime = (microtime(true) - $startTime) * 1000; // in milliseconds
        $responseData = $this->getResponseData($response);

        // Log to database (use try-catch to prevent logging errors from breaking the application)
        try {
            $isRequestLog = config('app.is_request_log', false);

            if ($isRequestLog) {

                $xApiUser = $request->header('X-Api-User');
                if ($xApiUser){
                    $headerData = UserModel::getUserData($xApiUser);
                }

                RequestLog::create([
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'headers' => $this->getFilteredHeaders($request),
                    'request_data' => $requestData,
                    'response_status' => $response->getStatusCode(),
                    'response_data' => $responseData,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'user_id' => $headerData['user_id'] ?? null,
                    'execution_time' => $executionTime,
                ]);

                /*LogRequestToDatabase::dispatch([
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'headers' => $this->getFilteredHeaders($request),
                    'request_data' => $requestData,
                    'response_status' => $response->getStatusCode(),
                    'response_data' => $responseData,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'user_id' => Auth::id() ?? null,
                    'execution_time' => $executionTime,
                ]);*/
            }
            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to log request: ' . $e->getMessage());
        }

        return $response;
    }

    private function getRequestData(Request $request)
    {
        $data = $request->all();

        // Remove sensitive data
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_token'];
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[HIDDEN]';
            }
        }

        // Limit size to prevent database issues with large payloads
        $jsonData = json_encode($data);
        if (strlen($jsonData) > 65535) { // MySQL TEXT limit
            return ['error' => 'Request data too large to log'];
        }

        return $data;
    }

    private function getResponseData($response)
    {
        // Only log JSON responses to avoid logging large HTML pages
        $contentType = $response->headers->get('Content-Type', '');

        if (strpos($contentType, 'application/json') !== false) {
            $content = $response->getContent();

            // Limit response data size
            if (strlen($content) > 65535) { // MySQL TEXT limit
//                return ['error' => 'Response data too large to log'];
                return [
                    'warning' => 'Response truncated due to size',
                    'partial_response' => substr($content, 0, 5000) . '...'
                ];
            }

            return json_decode($content, true);
        }

        return null;
    }

    private function getFilteredHeaders(Request $request)
    {
        $headers = $request->headers->all();

        // Remove sensitive headers
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key'];
        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['[HIDDEN]'];
            }
        }

        return $headers;
    }
}
