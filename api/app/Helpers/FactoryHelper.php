<?php

namespace App\Helpers;

use Vinkla\Hashids\Facades\Hashids;

class FactoryHelper
{
    public static function encodeIds(array $data): array
    {
        foreach ($data as $key => $value) {
            if (self::isValidId($key, $value)) {
                $data[$key] = Hashids::encode($value);
            }
        }

        return $data;
    }

    private static function isValidId($key, $value): bool
    {
        if (substr($key, -3) == '_id') {
            return is_int($value);
        }

        return false;
    }
}
