<?php

namespace App\Helpers;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;
use Throwable;

class TimezoneHelper
{
    public static function convertToUTC(string $datetime): string
    {
        $timezone = self::getUserTimezone();

        return Carbon::parse($datetime, $timezone)
            ->setTimezone('UTC')
            ->format('Y-m-d H:i:s');
    }

    public static function convertFromUTC(string $datetime): string
    {
        $timezone = self::getUserTimezone();

        return Carbon::parse($datetime, 'UTC')
            ->setTimezone($timezone)
            ->format('Y-m-d H:i:s');
    }

    public static function convertFromUTCIfValid(mixed $datetime): ?string
    {
        if (! self::isValidDateTime($datetime)) {
            return null;
        }

        if ($datetime instanceof Carbon) {
            $datetime = $datetime->toDateTimeString();
        } elseif ($datetime instanceof DateTimeInterface) {
            $datetime = Carbon::instance($datetime)->toDateTimeString();
        }

        return self::convertFromUTC((string) $datetime);
    }

    public static function isValidDateTime(mixed $value): bool
    {
        if ($value instanceof Carbon || $value instanceof DateTimeInterface) {
            return true;
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                Carbon::parse($value);

                return true;
            } catch (Throwable) {
                return false;
            }
        }

        return false;
    }

    public static function getUserTimezone(): string
    {
        return Auth::user()?->timezone ?? config('app.timezone', 'UTC');
    }
}
