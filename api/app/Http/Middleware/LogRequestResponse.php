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
        $enableLog = $request->hasHeader('X-Enable-LogRequestResponse') ? true : false;
        $enableRequestLogOnly = $request->hasHeader('X-LogRequestResponse-Request-Only') ? true : false;
        $enableResponseLogOnly = $request->hasHeader('X-LogRequestResponse-Response-Only') ? true : false;

        if (! $enableLog) return $next($request);

        $response = $next($request);

        if ($enableRequestLogOnly) {
            $this->logRequest($request);
        } else if ($enableResponseLogOnly) {
            $this->logResponse($response);    
        } else {
            $this->logRequest($request);
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
