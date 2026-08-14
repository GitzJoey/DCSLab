<?php

namespace App\Helpers;

use Vinkla\Hashids\Facades\Hashids;

class HashidsHelper
{
    public static function decodeId($encoded)
    {
        try {
            $result = Hashids::decode((string) $encoded);

            if (count($result) != 1) {
                return (int) 0;
            }

            return $result[0];
        } catch (\Exception $e) {
            return (int) 0;
        }
    }
}
