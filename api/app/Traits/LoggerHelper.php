<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

trait LoggerHelper
{
    public static function loggerDebug(string $method, Throwable $e): void
    {
        $sessionId = request()->hasSession() ? request()->session()->getId() : 'N/A';
        $userId = is_null(Auth::id()) ? 'NULL' : Auth::id();
        Log::debug('['.$sessionId.'-'.$userId.'] '.$method.$e);
    }

    public static function loggerPerformance(string $method, int|float $execution_time, int $recCount = 0): void
    {
        $sessionId = request()->hasSession() ? request()->session()->getId() : 'N/A';
        $userId = is_null(Auth::id()) ? 'NULL' : Auth::id();
        Log::channel('perfs')->info('['.$sessionId.'-'.$userId.'] '.$method.' ('.number_format($execution_time, 1).'s)'.' ('.$recCount.')');
    }
}
