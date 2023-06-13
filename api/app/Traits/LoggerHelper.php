<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

trait LoggerHelper
{
    public static function loggerDebug(string $method, Exception $e): void 
    {
        $sessionId = session()->getId();
        $userId = is_null(auth()->id()) ? 'NULL':auth()->id();
        Log::debug('['.$sessionId.'-'.$userId.'] '.$method.$e);
    }

    public static function loggerPerformance(string $method, int | float $execution_time): void
    {
        $sessionId = session()->getId();
        $userId = is_null(auth()->id()) ? 'NULL':auth()->id();
        Log::channel('perfs')->info('['.$sessionId.'-'.$userId.'] '.$method.' ('.number_format($execution_time, 1).'s)');
    }
}