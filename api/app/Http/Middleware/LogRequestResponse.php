<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $enableLog = false;
        $enableRequestLogOnly = false;
        $enableResponseLogOnly = false;

        if ($request->hasHeader('X-LogRequestResponse')) {
            switch (strtolower($request->header('X-LogRequestResponse'))) {
                case 'request':
                    $enableLog = true;
                    $enableRequestLogOnly = true;
                    $enableResponseLogOnly = false;
                    break;
                case 'response':
                    $enableLog = true;
                    $enableRequestLogOnly = false;
                    $enableResponseLogOnly = true;
                    break;
                case 'true':
                    $enableLog = true;
                    $enableRequestLogOnly = true;
                    $enableResponseLogOnly = true;
                    break;
                case 'false':
                default:
                    $enableLog = false;
                    $enableRequestLogOnly = false;
                    $enableResponseLogOnly = false;
            }
        }

        if (! $enableLog) {
            return $next($request);
        }

        $response = $next($request);

        if ($enableRequestLogOnly) {
            $this->logRequest($request);
        }
        if ($enableResponseLogOnly) {
            $this->logResponse($response);
        }

        return $response;
    }

    private function logRequest($request)
    {
        $logData = [
            'request' => $request->all(),
        ];

        $logMessage = json_encode($logData, JSON_UNESCAPED_SLASHES);
        Log::channel('traffic')->info($logMessage);
    }

    private function logResponse($response)
    {
        $logData = [
            'response' => $response->getContent(),
        ];

        $logMessage = str_replace('\"', '"', json_encode($logData));
        Log::channel('traffic')->info($logMessage);
    }
}
